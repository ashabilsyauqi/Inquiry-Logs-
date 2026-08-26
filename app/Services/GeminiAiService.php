<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\StageTrigger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
    }

    /**
     * Analyze recent messages for a lead and determine if an AI suggestion should be issued.
     *
     * @param Lead $lead
     * @return array{has_suggestion: bool, suggested_stage: string|null, suggested_keyword: string|null, reason: string|null}
     */
    public function analyzeLeadStage(Lead $lead): array
    {
        $waAccountId = $lead->wa_account_id;
        $stages = PipelineStage::where('wa_account_id', $waAccountId)->orderBy('order', 'asc')->get();

        if ($stages->isEmpty()) {
            return ['has_suggestion' => false, 'concluded_stage' => $lead->stage, 'suggested_stage' => null, 'suggested_keyword' => null, 'reason' => null];
        }

        // Retrieve last 10 chat messages
        $messages = $lead->messages()->latest()->take(10)->get()->reverse();

        if ($messages->isEmpty()) {
            return ['has_suggestion' => false, 'concluded_stage' => $lead->stage, 'suggested_stage' => null, 'suggested_keyword' => null, 'reason' => null];
        }

        if (empty($this->apiKey)) {
            Log::info("GeminiAiService: GEMINI_API_KEY not set. Using Intelligent Indonesian Slang & NLP Heuristic Engine.");
            return $this->analyzeWithHeuristics($lead, $stages, $messages);
        }

        // Get active triggers (keywords mapped to stages)
        $triggers = StageTrigger::where('wa_account_id', $waAccountId)->with('pipelineStage')->get();

        // Build list of stages and keywords for the prompt
        $stageListStr = "";
        foreach ($stages as $index => $stage) {
            $stageOrder = $stage->order ?: ($index + 1);
            $stageTriggers = $triggers->filter(fn($t) => $t->pipeline_stage_id === $stage->id)->pluck('keyword')->implode(', ');
            $keywordHint = $stageTriggers ? " (Keyword Trigger: #{$stageTriggers} atau /{$stageTriggers})" : " (Keyword Trigger: #{$stage->name} atau /{$stage->name})";
            $stageListStr .= "- {$stageOrder}. {$stage->name}{$keywordHint}\n";
        }

        $transcriptStr = "";
        foreach ($messages as $msg) {
            $senderTag = $msg->is_from_me ? "[CS/Admin]" : "[Customer ({$lead->name})]";
            $transcriptStr .= "{$senderTag}: {$msg->message}\n";
        }

        $prompt = <<<PROMPT
Kamu adalah Analis AI Ahli Sales Pipeline CRM. Tugasmu adalah menganalisis transkrip percakapan WhatsApp antara CS/Admin dan Customer/Lead secara cerdas dan mendalam, termasuk memahami bahasa gaul, singkatan, typo, dan istilah slang Indonesia.

DAFTAR STAGES DALAM SISTEM CRM:
{$stageListStr}

STAGE LEAD SAAT INI DI CRM: "{$lead->stage}"

TRANSKRIP CHAT TERAKHIR:
{$transcriptStr}

PANDUAN PEMAHAMAN BAHASA SLANG / INFORMAL INDONESIA:
- Meeting / Janji Temu: "gmeet", "labgsung gmeet", "gmeetz", "zoom", "call", "meet", "jadwal meet", "diskusi online" -> Stage: Meeting Call
- Penawaran / Proposal / Pricing: "tawarannyah", "offer", "kirim offer", "proposal", "pricelist", "biaya", "harga paket", "invoice" -> Stage: Kirim Penawaran
- Tanya Jawab / Konsultasi: "mau tanya", "diskusi keperluan", "info layanan", "tanya-tanya", "speknya gimana" -> Stage: Tanya Jawab / Konsultasi
- Deal / Kesepakatan / Closing: "oke deal", "deal ya", "acc", "fix ambil", "minta rekening", "siap transfer", "terima kasih pak (setelah deal)", "gas bungkus" -> Stage: Deal atau Closing

TUGAS UTAMA:
1. Tentukan "concluded_stage": Stage mana dari daftar CRM yang PALING TEPAT menggambarkan posisi akhir percakapan ini saat ini?
2. Apakah stage hasil kesimpulan AI berbeda dengan stage saat ini ("{$lead->stage}")? Jika berbeda (artinya Admin CS belum/lupa mengupdate stage via keyword trigger), maka set "has_suggestion": true. Jika sudah sama persis, set "has_suggestion": false.

KEMBALIKAN HANYA FORMAT JSON MURNI (TANPA MARKDOWN, TANPA PENJELASAN DI LUAR JSON):
{
  "concluded_stage": "Nama Stage Yang Paling Tepat dari Daftar CRM",
  "has_suggestion": true atau false,
  "suggested_stage": "Nama Stage Yang Disarankan",
  "suggested_keyword": "#keyword_trigger",
  "reason": "Alasan singkat dan jelas dalam bahasa Indonesia mengapa percakapan ini berada di stage tersebut"
}
PROMPT;

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $rawBody = $response->json();
                $candidates = $rawBody['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                // Clean markdown codeblocks if any
                $jsonText = trim(str_replace(['```json', '```'], '', $candidates));
                $data = json_decode($jsonText, true);

                if (is_array($data) && !empty($data['concluded_stage'])) {
                    $concludedStage = $data['concluded_stage'];
                    $hasSuggestion = (bool)($data['has_suggestion'] ?? ($concludedStage !== $lead->stage));
                    
                    return [
                        'concluded_stage' => $concludedStage,
                        'has_suggestion' => $hasSuggestion,
                        'suggested_stage' => $data['suggested_stage'] ?? $concludedStage,
                        'suggested_keyword' => $data['suggested_keyword'] ?? '#' . strtolower($concludedStage),
                        'reason' => $data['reason'] ?? "Indikasi percakapan menunjukkan kesepakatan stage {$concludedStage}."
                    ];
                }
            } else {
                Log::error("Gemini API Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception: " . $e->getMessage());
        }

        return $this->analyzeWithHeuristics($lead, $stages, $messages);
    }

    /**
     * Fallback NLP Heuristic Analysis for Indonesian Slang & Dialogue Context
     */
    public function analyzeWithHeuristics(Lead $lead, $stages, $messages): array
    {
        if ($messages->isEmpty()) {
            return ['has_suggestion' => false, 'concluded_stage' => $lead->stage, 'suggested_stage' => null, 'suggested_keyword' => null, 'reason' => null];
        }

        // Evaluate intent from the most recent messages (chronological precedence)
        $recentMessages = $messages->reverse(); // newest first
        
        // Comprehensive Indonesian Sales Slang & NLP Regex Dictionaries
        $dealPattern = '/\b(oke\s+deal|deal\s+ya|deal\s+mas|deal\s+gan|deal\s+pak|deal\s+kak|deal\s+bos|deal|fix\s+ambil|fix\s+jadi|jadi\s+ambil|jadi\s+pesan|jadi\s+order|langsung\s+order|siap\s+transfer|siap\s+tf|mau\s+transfer|mau\s+tf|minta\s+no\s*rek|minta\s+norek|minta\s+rek|minta\s+rekening|kirim\s+rekening|kirim\s+norek|rek\s+bca|rek\s+mandiri|rek\s+bri|rek\s+bni|udah\s+transfer|sudah\s+transfer|udah\s+tf|sudah\s+tf|saya\s+tf|tf\s+sekarang|transfer\s+ke|transfer\s+sekarang|bayar\s+sekarang|siap\s+bayar|kirim\s+bukti|bukti\s+tf|bukti\s+transfer|struk\s+transfer|acc\s+ya|acc\s+aja|acc|gas\s+bungkus|bungkus\s+dah|bungkus|gass|gaskan|gas\s+kan|gaspol|gaskeun|sepakat|langsung\s+sepakat|setuju|saya\s+setuju|cocok\s+harganya|ambil\s+ini|ambil\s+paket|minat\s+ambil|mau\s+ambil|saya\s+ambil|fix\s+beli|beli\s+sekarang|lunas|pelunasan|dp\s+sekarang|tanda\s+jadi)\b/i';
        $meetingPattern = '/\b(gmeet|gmeetz|labgsung\s+gmeet|langsung\s+gmeet|link\s+gmeet|gmeetnya|zoom|link\s+zoom|zoom\s+meeting|zoomnya|google\s+meet|jadwal\s+meet|jadwalin\s+meet|atur\s+jadwal|jam\s+berapa\s+meet|demo\s+produk|demo\s+aplikasi|demo\s+web|presentasi|presentasiin|video\s+call|vc|call\s+wa|teleponan|telponan|voice\s+call|ngobrol\s+di\s+call|meeting\s+lagi|meet\s+lagi|gmeet\s+lagi|zoom\s+lagi|reschedule)\b/i';
        $offerPattern = '/\b(tawarannyah|tawaran|kirim\s+tawaran|kirim\s+offer|kirimkan\s+offer|saya\s+kirimkan\s+offer|penawaran|proposal|kirim\s+proposal|pricelist|price\s*list|daftar\s+harga|list\s+harga|harga\s+paket|biayanya|biaya\s+berapa|estimasi\s+biaya|harganya\s+berapa|berapaan|diskon|ada\s+diskon|potongan\s+harga|harga\s+nett|best\s+price|invoice|quotation|quote|surat\s+penawaran)\b/i';
        $tanyaPattern = '/\b(mau\s+tanya|tanya\s+dong|tanya-tanya|mau\s+nanya|nanya\s+dong|boleh\s+nanya|konsultasi|konsul|diskusi\s+keperluan|diskusi\s+kebutuhan|butuh\s+solusi|info\s+lengkap|info\s+dong|info\s+layanan|informasi|detailnya|spesifikasi|speknya|spek|fiturnya|fitur\s+apa\s+aja|bisa\s+apa\s+aja|apakah\s+bisa|bisa\s+gak|bisa\s+ga|support\s+gak|cara\s+kerjanya|kelebihannya)\b/i';

        foreach ($recentMessages as $msg) {
            $text = strtolower($msg->message);

            // 1. Check Meeting Intent in recent chat (e.g. client asks for meeting after deal)
            if (preg_match($meetingPattern, $text)) {
                $matchedStage = $stages->first(fn($s) => stripos($s->name, 'meeting') !== false || stripos($s->name, 'meet') !== false || stripos($s->name, 'call') !== false);
                if ($matchedStage) {
                    $hasSuggestion = ($lead->stage !== $matchedStage->name);
                    return [
                        'concluded_stage' => $matchedStage->name,
                        'has_suggestion' => $hasSuggestion,
                        'suggested_stage' => $matchedStage->name,
                        'suggested_keyword' => '/meeting',
                        'reason' => 'Percakapan terbaru menunjukkan ajakan/jadwal meeting (' . $this->extractSnippet($text, $meetingPattern) . ').'
                    ];
                }
            }

            // 2. Check Deal / Closing Intent in recent chat
            if (preg_match($dealPattern, $text)) {
                $matchedStage = $stages->first(fn($s) => stripos($s->name, 'deal') !== false || stripos($s->name, 'closing') !== false);
                if ($matchedStage) {
                    $hasSuggestion = ($lead->stage !== $matchedStage->name);
                    return [
                        'concluded_stage' => $matchedStage->name,
                        'has_suggestion' => $hasSuggestion,
                        'suggested_stage' => $matchedStage->name,
                        'suggested_keyword' => '#deal',
                        'reason' => 'Percakapan terbaru menunjukkan kesepakatan order/pembayaran (' . $this->extractSnippet($text, $dealPattern) . ').'
                    ];
                }
            }

            // 3. Check Proposal / Offer Intent in recent chat
            if (preg_match($offerPattern, $text)) {
                $matchedStage = $stages->first(fn($s) => stripos($s->name, 'penawaran') !== false || stripos($s->name, 'offer') !== false || stripos($s->name, 'proposal') !== false);
                if ($matchedStage) {
                    $hasSuggestion = ($lead->stage !== $matchedStage->name);
                    return [
                        'concluded_stage' => $matchedStage->name,
                        'has_suggestion' => $hasSuggestion,
                        'suggested_stage' => $matchedStage->name,
                        'suggested_keyword' => '#kirim penawaran',
                        'reason' => 'Percakapan terbaru membahas pengiriman penawaran/proposal (' . $this->extractSnippet($text, $offerPattern) . ').'
                    ];
                }
            }

            // 4. Check Tanya Jawab Intent in recent chat
            if (preg_match($tanyaPattern, $text)) {
                $matchedStage = $stages->first(fn($s) => stripos($s->name, 'tanya') !== false || stripos($s->name, 'konsultasi') !== false);
                if ($matchedStage) {
                    $hasSuggestion = ($lead->stage !== $matchedStage->name);
                    return [
                        'concluded_stage' => $matchedStage->name,
                        'has_suggestion' => $hasSuggestion,
                        'suggested_stage' => $matchedStage->name,
                        'suggested_keyword' => '#tanya jawab',
                        'reason' => 'Percakapan terbaru berada pada tahap diskusi konsultasi dan tanya jawab produk.'
                    ];
                }
            }
        }

        return [
            'concluded_stage' => $lead->stage,
            'has_suggestion' => false,
            'suggested_stage' => null,
            'suggested_keyword' => null,
            'reason' => null
        ];
    }

    private function extractSnippet(string $text, string $pattern): string
    {
        if (preg_match($pattern, $text, $matches)) {
            return $matches[0];
        }
        return 'kata kunci';
    }
}
