<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\WaAccount;
use App\Models\PipelineStage;
use App\Models\AiLeadComparison;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeadComparisonService
{
    /**
     * Generate real-time or filtered comparison between Real CS Lead Stages vs AI Stage Conclusions.
     *
     * @param string|int $accountId 'all' or specific wa_account_id
     * @param string $period 'today', 'this_week', 'this_month', 'all_time', 'custom'
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function generateComparisonData($accountId = 'all', $period = 'all_time', $startDate = null, $endDate = null): array
    {
        $query = Lead::with('waAccount');

        // Filter by WA Account
        if ($accountId !== 'all' && is_numeric($accountId)) {
            $query->where('wa_account_id', $accountId);
        }

        // Filter by Period
        $now = Carbon::now();
        if ($period === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($period === 'this_week') {
            $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $leads = $query->latest()->get();

        // Get standard stages
        if ($accountId !== 'all' && is_numeric($accountId)) {
            $stageNames = PipelineStage::where('wa_account_id', $accountId)->orderBy('order')->pluck('name')->toArray();
        } else {
            $stageNames = PipelineStage::orderBy('order')->pluck('name')->unique()->toArray();
        }

        if (empty($stageNames)) {
            $stageNames = ['Lead Masuk', 'Meeting Call', 'Kirim Penawaran', 'Deal'];
        }

        $realCounts = [];
        $aiCounts = [];
        foreach ($stageNames as $st) {
            $realCounts[$st] = 0;
            $aiCounts[$st] = 0;
        }

        $matchCount = 0;
        $discrepancyCount = 0;
        $discrepancies = [];

        foreach ($leads as $lead) {
            $realStage = $lead->stage ?? 'Lead Masuk';
            $aiStage = $lead->ai_concluded_stage ?? ($lead->ai_suggested_stage ?? $realStage);

            if (!isset($realCounts[$realStage])) {
                $realCounts[$realStage] = 0;
            }
            $realCounts[$realStage]++;

            if (!isset($aiCounts[$aiStage])) {
                $aiCounts[$aiStage] = 0;
            }
            $aiCounts[$aiStage]++;

            if ($realStage === $aiStage) {
                $matchCount++;
            } else {
                $discrepancyCount++;
                $discrepancies[] = [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'account_name' => $lead->waAccount->name ?? 'Default WA',
                    'real_stage' => $realStage,
                    'ai_stage' => $aiStage,
                    'ai_keyword' => $lead->ai_suggested_keyword,
                    'ai_reason' => $lead->ai_suggestion_reason ?? 'Indikasi percakapan menunjukkan kemajuan stage ke ' . $aiStage,
                    'created_at' => $lead->created_at ? $lead->created_at->format('d M Y H:i') : '-',
                    'ai_suggested_at' => $lead->ai_suggested_at ? Carbon::parse($lead->ai_suggested_at)->format('d M Y H:i') : '-'
                ];
            }
        }

        $totalLeads = $leads->count();
        $matchRate = $totalLeads > 0 ? round(($matchCount / $totalLeads) * 100, 1) : 100;
        $discrepancyRate = $totalLeads > 0 ? round(($discrepancyCount / $totalLeads) * 100, 1) : 0;

        // Stage comparisons summary table
        $allUniqueStages = array_unique(array_merge(array_keys($realCounts), array_keys($aiCounts)));
        $stageComparison = [];
        foreach ($allUniqueStages as $stage) {
            $r = $realCounts[$stage] ?? 0;
            $a = $aiCounts[$stage] ?? 0;
            $stageComparison[$stage] = [
                'stage' => $stage,
                'real_count' => $r,
                'ai_count' => $a,
                'difference' => $a - $r, // positive means AI detected more than CS recorded
                'status' => $a > $r ? 'AI Lebih Tinggi' : ($a < $r ? 'Real Lebih Tinggi' : 'Selaras (Match)')
            ];
        }

        return [
            'total_leads' => $totalLeads,
            'match_count' => $matchCount,
            'match_rate' => $matchRate,
            'discrepancy_count' => $discrepancyCount,
            'discrepancy_rate' => $discrepancyRate,
            'real_counts' => $realCounts,
            'ai_counts' => $aiCounts,
            'stage_comparison' => $stageComparison,
            'discrepant_leads' => $discrepancies,
            'period' => $period,
            'account_id' => $accountId,
            'generated_at' => Carbon::now()->format('d M Y H:i:s')
        ];
    }

    /**
     * Save a snapshot to the database for historical reporting.
     *
     * @param string|int $accountId
     * @return AiLeadComparison
     */
    public function createSnapshot($accountId = 'all'): AiLeadComparison
    {
        $data = $this->generateComparisonData($accountId, 'this_week');

        return AiLeadComparison::create([
            'report_date' => Carbon::today()->toDateString(),
            'real_stage_counts' => $data['real_counts'],
            'ai_stage_counts' => $data['ai_counts'],
            'differences' => [
                'total_leads' => $data['total_leads'],
                'match_rate' => $data['match_rate'],
                'discrepancy_count' => $data['discrepancy_count'],
                'discrepant_leads_sample' => array_slice($data['discrepant_leads'], 0, 10),
                'stage_comparison' => $data['stage_comparison'],
            ],
        ]);
    }
}
