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
        if (empty($this->apiKey)) {
            Log::info("GeminiAiService: GEMINI_API_KEY is not set. Skipping AI analysis.");
            return ['has_suggestion' => false, 'suggested_stage' => null, 'suggested_keyword' => null, 'reason' => null];
        }

        $waAccountId = $lead->wa_account_id;
        $stages = PipelineStage::where('wa_account_id', $waAccountId)->orderBy('order', 'asc')->get();

        if ($stages->isEmpty()) {
            return ['has_suggestion' => false, 'suggested_stage' => null, 'suggested_keyword' => null, 'reason' => null];
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

        // Retrieve last 10 chat messages
        $messages = $lead->messages()->latest()->take(10)->get()->reverse();

        if ($messages->isEmpty()) {
            return ['has_suggestion' => false, 'suggested_stage' => null, 'suggested_keyword' => null, 'reason' => null];
        }

        $transcriptStr = "";
        foreach ($messages as $msg) {
            $senderTag = $msg->is_from_me ? "[CS/Admin]" : "[Customer ({$lead->name})]";
            $transcriptStr .= "{$senderTag}: {$msg->message}\n";
        }

        $prompt = <<<PROMPT
Kamu adalah Asisten AI Pintar CRM Sales. Tugasmu adalah membaca percakapan WhatsApp antara CS/Admin dan Customer, lalu menentukan apakah percakapan ini menunjukkan indikasi kemajuan stage sales, tetapi Admin CS LUPA/BELUM mengetik keyword trigger.

STAGES DALAM SISTEM CRM:
{$stageListStr}

STAGE LEAD SAAT INI: "{$lead->stage}"

TRANSKRIP CHAT TERAKHIR:
{$transcriptStr}

INSTRUKSI PENTING:
1. Evaluasi apakah percakapan di atas sudah mencapai tahap/stage yang LEBIH MAJU dibanding stage saat ini ("{$lead->stage}").
2. Jika percakapan sudah menunjukkan indikasi kuat (misal: customer minta nomor rekening/transfer = Deal, kesepakatan janji temu = Meeting), dan Admin CS BELUM mengetik keyword trigger (seperti #deal, /meeting), maka berikan rekomendasi!
3. Format balasan WAJIB berupa JSON saja (tanpa markdown backtick / tanpa penjelasan tambahan):

{
  "has_suggestion": true atau false,
  "suggested_stage": "Nama Stage Yang Disarankan",
  "suggested_keyword": "#keyword_trigger_yang_harus_diketik",
  "reason": "Alasan singkat dalam bahasa Indonesia mengapa stage ini disarankan"
}

Pasti pastikan "has_suggestion" bernilai false jika stage saat ini sudah sesuai atau belum ada indikasi perubahan stage yang jelas.
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
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $rawBody = $response->json();
                $candidates = $rawBody['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                // Clean markdown codeblocks if any
                $jsonText = trim(str_replace(['```json', '```'], '', $candidates));
                $data = json_decode($jsonText, true);

                if (is_array($data) && !empty($data['has_suggestion']) && !empty($data['suggested_stage'])) {
                    return [
                        'has_suggestion' => true,
                        'suggested_stage' => $data['suggested_stage'],
                        'suggested_keyword' => $data['suggested_keyword'] ?? '#' . strtolower($data['suggested_stage']),
                        'reason' => $data['reason'] ?? 'Terdeteksi indikasi perubahan stage dari isi chat.'
                    ];
                }
            } else {
                Log::error("Gemini API Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception: " . $e->getMessage());
        }

        return ['has_suggestion' => false, 'suggested_stage' => null, 'suggested_keyword' => null, 'reason' => null];
    }
}
