<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\GeminiAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeLeadStageWithAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $leadId;

    public function __construct(int $leadId)
    {
        $this->leadId = $leadId;
    }

    public function handle(GeminiAiService $aiService): void
    {
        $lead = Lead::find($this->leadId);
        if (!$lead) return;

        $result = $aiService->analyzeLeadStage($lead);

        if ($result['has_suggestion'] && $result['suggested_stage'] !== $lead->stage) {
            $lead->ai_suggested_stage = $result['suggested_stage'];
            $lead->ai_suggested_keyword = $result['suggested_keyword'];
            $lead->ai_suggestion_reason = $result['reason'];
            $lead->ai_suggested_at = now();
            $lead->save();

            Log::info("🤖 AI Notification Issued for Lead {$lead->id} ({$lead->name}): Suggested '{$result['suggested_stage']}' (Keyword: {$result['suggested_keyword']})");
        }
    }
}
