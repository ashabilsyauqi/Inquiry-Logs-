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

        $fullText = strtolower($messages->pluck('message')->implode(' '));

        // 1. Check for Deal / Closing Indications
        $dealPattern = '/\b(oke\s+deal|deal\s+ya|deal\s+mas|deal\s+gan|deal\s+pak|fix\s+ambil|jadi\s+ambil|jadi\s+pesan|siap\s+transfer|minta\s+no\s*rek|minta\s+rekening|udah\s+transfer|sudah\s+transfer|transfer\s+ke|kirim\s+bukti|bukti\s+transfer|acc\s+ya|gas\s+bungkus|terima\s+kasih\s+pak)\b/i';
        if (preg_match($dealPattern, $fullText)) {
            $matchedStage = $stages->first(fn($s) => stripos($s->name, 'deal') !== false || stripos($s->name, 'closing') !== false);
            if ($matchedStage) {
                $hasSuggestion = ($lead->stage !== $matchedStage->name);
                return [
                    'concluded_stage' => $matchedStage->name,
                    'has_suggestion' => $hasSuggestion,
                    'suggested_stage' => $matchedStage->name,
                    'suggested_keyword' => '#deal',
                    'reason' => 'Percakapan menunjukkan kesepakatan order/pembayaran (Deal). Customer/CS menyatakan persetujuan (' . $this->extractSnippet($fullText, $dealPattern) . ') namun stage di CRM saat ini masih "' . $lead->stage . '".'
                ];
            }
        }

        // 2. Check for Proposal / Penawaran Indications
        $offerPattern = '/\b(tawarannyah|kirim\s+tawaran|kirim\s+offer|kirimkan\s+offer|saya\s+kirimkan\s+offer|penawaran|proposal|pricelist|price\s*list|daftar\s+harga|harga\s+paket|biayanya|estimasi\s+biaya|invoice|quotation|quote)\b/i';
        if (preg_match($offerPattern, $fullText)) {
            $matchedStage = $stages->first(fn($s) => stripos($s->name, 'penawaran') !== false || stripos($s->name, 'offer') !== false || stripos($s->name, 'proposal') !== false);
            if ($matchedStage) {
                $hasSuggestion = ($lead->stage !== $matchedStage->name);
                return [
                    'concluded_stage' => $matchedStage->name,
                    'has_suggestion' => $hasSuggestion,
                    'suggested_stage' => $matchedStage->name,
                    'suggested_keyword' => '#kirim penawaran',
                    'reason' => 'Terdeteksi pengiriman penawaran harga/proposal paket (' . $this->extractSnippet($fullText, $offerPattern) . ') antara CS dan customer.'
                ];
            }
        }

        // 3. Check for Meeting / Gmeet / Zoom Indications
        $meetingPattern = '/\b(gmeet|gmeetz|labgsung\s+gmeet|langsung\s+gmeet|zoom|google\s+meet|jadwal\s+meet|jadwalin\s+meet|demo\s+produk|presentasi|video\s+call|ketemuan\s+online|call\s+wa|teleponan)\b/i';
        if (preg_match($meetingPattern, $fullText)) {
            $matchedStage = $stages->first(fn($s) => stripos($s->name, 'meeting') !== false || stripos($s->name, 'meet') !== false || stripos($s->name, 'call') !== false);
            if ($matchedStage) {
                $hasSuggestion = ($lead->stage !== $matchedStage->name);
                return [
                    'concluded_stage' => $matchedStage->name,
                    'has_suggestion' => $hasSuggestion,
                    'suggested_stage' => $matchedStage->name,
                    'suggested_keyword' => '/meeting',
                    'reason' => 'Terdeteksi ajakan atau kesepakatan sesi meeting online / Google Meet (' . $this->extractSnippet($fullText, $meetingPattern) . ') antara CS dan customer.'
                ];
            }
        }

        // 4. Check for Tanya Jawab / Konsultasi Indications
        $tanyaPattern = '/\b(mau\s+tanya|tanya\s+dong|konsultasi|diskusi\s+keperluan|info\s+lengkap|spesifikasi|speknya|fiturnya|apakah\s+bisa)\b/i';
        if (preg_match($tanyaPattern, $fullText)) {
            $matchedStage = $stages->first(fn($s) => stripos($s->name, 'tanya') !== false || stripos($s->name, 'konsultasi') !== false);
            if ($matchedStage) {
                $hasSuggestion = ($lead->stage !== $matchedStage->name);
                return [
                    'concluded_stage' => $matchedStage->name,
                    'has_suggestion' => $hasSuggestion,
                    'suggested_stage' => $matchedStage->name,
                    'suggested_keyword' => '#tanya jawab',
                    'reason' => 'Percakapan berada pada tahap diskusi konsultasi kebutuhan dan tanya jawab produk.'
                ];
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
