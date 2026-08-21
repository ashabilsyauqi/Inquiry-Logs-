<?php

namespace App\Jobs;

use App\Services\LeadComparisonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyLeadComparisonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(LeadComparisonService $service): void
    {
        try {
            $snapshot = $service->createSnapshot('all');
            Log::info("📊 AI vs Real Leads Weekly Snapshot generated successfully for {$snapshot->report_date->format('Y-m-d')}.");
        } catch (\Throwable $e) {
            Log::error("Failed generating weekly AI lead comparison snapshot: " . $e->getMessage());
        }
    }
}
