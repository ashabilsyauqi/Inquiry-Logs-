<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Services\GeminiAiService;

class AiScanLeadsCommand extends Command
{
    protected $signature = 'ai:scan-leads {--account= : Specific WA Account ID}';
    protected $description = 'Scan and analyze all lead conversations with AI to update concluded stages and detect discrepancies';

    public function handle()
    {
        $accountId = $this->option('account');
        $query = Lead::with(['messages', 'waAccount']);

        if ($accountId && is_numeric($accountId)) {
            $query->where('wa_account_id', $accountId);
        }

        $leads = $query->get();
        $total = $leads->count();

        if ($total === 0) {
            $this->warn("Tidak ada lead yang ditemukan untuk dipindai.");
            return Command::SUCCESS;
        }

        $this->info("⚡ Memulai Pemindaian AI untuk {$total} leads...");
        $aiService = new GeminiAiService();
        $scanned = 0;
        $discrepancies = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($leads as $lead) {
            if ($lead->messages->count() > 0) {
                $result = $aiService->analyzeLeadStage($lead);
                
                if (!empty($result['concluded_stage'])) {
                    $lead->ai_concluded_stage = $result['concluded_stage'];
                }
                
                if (!empty($result['has_suggestion'])) {
                    $lead->ai_suggested_stage = $result['suggested_stage'];
                    $lead->ai_suggested_keyword = $result['suggested_keyword'];
                    $lead->ai_suggestion_reason = $result['reason'];
                    $lead->ai_suggested_at = now();
                    $discrepancies++;
                } else if ($lead->stage === ($result['concluded_stage'] ?? $lead->stage)) {
                    $lead->ai_suggested_stage = null;
                    $lead->ai_suggested_keyword = null;
                    $lead->ai_suggestion_reason = null;
                }
                
                $lead->save();
                $scanned++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Pemindaian selesai!");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Leads', $total],
                ['Leads dengan Percakapan Dianalisis', $scanned],
                ['Selisih Ditemukan (Discrepancies)', $discrepancies],
            ]
        );

        return Command::SUCCESS;
    }
}
