<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\PipelineStage;

class LeadTemperatureService
{
    /**
     * Cache for brand stage lists to avoid repeated queries in loops
     * @var array
     */
    protected static array $stageCache = [];

    /**
     * Check if a stage name signifies a dead/lost/spam/canceled stage
     *
     * @param string|null $stageName
     * @return bool
     */
    public static function isDeadStage(?string $stageName): bool
    {
        if (empty($stageName)) {
            return false;
        }

        $lower = strtolower(trim($stageName));
        $deadKeywords = [
            'spam', 'dead', 'lost', 'batal', 'cancel', 'reject', 'ditolak',
            'junk', 'invalid', 'gagal', 'tidak valid', 'tidak minat',
            'unqualified', 'bukan prospek', 'hangus', 'no response', 'loss'
        ];

        foreach ($deadKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the lead temperature (Cold ❄️ -> Cool 💧 -> Warm 🌤️ -> Very Warm ⚡ -> Hot 🔥 or Dead 💀)
     * dynamically proportional to the brand's stage count.
     *
     * @param Lead $lead
     * @return array
     */
    public static function getTemperature(Lead $lead): array
    {
        $brandId = $lead->wa_account_id ?? 'global';

        // 1. Direct Dead / Spam Stage Check
        if (self::isDeadStage($lead->stage)) {
            return self::getDeadLeadData($lead->stage);
        }

        if (!isset(self::$stageCache[$brandId])) {
            if ($lead->wa_account_id) {
                $stages = PipelineStage::where('wa_account_id', $lead->wa_account_id)
                    ->orderBy('order', 'asc')
                    ->pluck('name')
                    ->toArray();
            } else {
                $stages = PipelineStage::orderBy('order', 'asc')
                    ->pluck('name')
                    ->unique()
                    ->values()
                    ->toArray();
            }

            if (empty($stages)) {
                $stages = ['Lead Masuk', 'Meeting Call', 'Kirim Penawaran', 'Deal'];
            }

            self::$stageCache[$brandId] = $stages;
        }

        $allStages = self::$stageCache[$brandId];

        // Filter out dead/spam stages so they don't skew the sales funnel scale
        $activeStages = array_values(array_filter($allStages, fn($st) => !self::isDeadStage($st)));
        if (empty($activeStages)) {
            $activeStages = $allStages;
        }

        $totalActive = count($activeStages);
        $currentIndex = array_search($lead->stage, $activeStages);

        if ($currentIndex === false) {
            foreach ($activeStages as $idx => $stName) {
                if (strcasecmp($stName, $lead->stage ?? '') === 0) {
                    $currentIndex = $idx;
                    break;
                }
            }
            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        }

        return self::calculateTemperatureData($currentIndex, $totalActive, $lead->stage);
    }

    /**
     * Determine temperature metadata for a specific stage
     *
     * @param int $stageIndex (0-indexed)
     * @param int $totalStages
     * @param string|null $stageName
     * @return array
     */
    public static function getStageTemperature(int $stageIndex, int $totalStages, ?string $stageName = null): array
    {
        if (self::isDeadStage($stageName)) {
            return self::getDeadLeadData($stageName);
        }

        return self::calculateTemperatureData($stageIndex, $totalStages, $stageName);
    }

    /**
     * Data object for Dead / Spam / Lost stages
     */
    public static function getDeadLeadData(?string $stageName = 'SPAM'): array
    {
        return [
            'key' => 'dead',
            'label' => 'Dead Lead',
            'icon' => '💀',
            'badge_class' => 'bg-slate-100 text-slate-700 border border-slate-300 shadow-xs',
            'pill_bg' => 'bg-slate-500',
            'text_color' => 'text-slate-600',
            'border_color' => 'border-slate-400',
            'hex' => '#64748b',
            'gradient' => 'from-slate-600 to-slate-400',
            'bar_color' => 'bg-slate-400',
            'accent_border' => 'border-l-4 border-l-slate-400',
            'description' => 'Prospek Gugur / Spam / Tidak Lolos Kualifikasi',
            'ratio' => 0.0,
            'progress' => 0,
            'step' => 'Dead',
            'is_dead' => true,
        ];
    }

    /**
     * Internal calculation logic mapping 0..M-1 active stages:
     * Cold (Blue) -> Cool (Teal) -> Warm (Amber) -> Very Warm (Orange) -> Hot (Red/Rose)
     */
    protected static function calculateTemperatureData(int $index, int $total, ?string $stageName = null): array
    {
        $total = max(1, $total);
        $index = max(0, min($index, $total - 1));

        if ($total === 1) {
            $ratio = 0.0;
        } elseif ($total === 2) {
            $ratio = ($index === 0) ? 0.0 : 1.0;
        } elseif ($total === 3) {
            // 3 Stages: 0=Cold, 1=Warm, 2=Hot
            $ratio = ($index === 0) ? 0.0 : (($index === 1) ? 0.5 : 1.0);
        } elseif ($total === 4) {
            // 4 Stages: 0=Cold, 1=Cool, 2=Warm/Very Warm, 3=Hot
            $ratio = $index / 3;
        } else {
            $ratio = $index / ($total - 1);
        }

        $progressPercent = (int)round((($index + 1) / $total) * 100);

        // Tier 1: Cold Lead (Biru Langit / Sky)
        if ($ratio <= 0.15 || ($total <= 3 && $index === 0)) {
            return [
                'key' => 'cold',
                'label' => 'Cold Lead',
                'icon' => '❄️',
                'badge_class' => 'bg-sky-50 text-sky-700 border border-sky-200 shadow-xs',
                'pill_bg' => 'bg-sky-500',
                'text_color' => 'text-sky-600',
                'border_color' => 'border-sky-300',
                'hex' => '#0284c7',
                'gradient' => 'from-blue-600 to-sky-400',
                'bar_color' => 'bg-sky-500',
                'accent_border' => 'border-l-4 border-l-sky-500',
                'description' => 'Tahap Awal / Inquiries Baru',
                'ratio' => $ratio,
                'progress' => $progressPercent,
                'step' => ($index + 1) . '/' . $total,
                'is_dead' => false,
            ];
        }

        // Tier 2: Cool Lead (Teal / Cyan)
        if ($ratio > 0.15 && $ratio <= 0.38) {
            return [
                'key' => 'cool',
                'label' => 'Cool Lead',
                'icon' => '💧',
                'badge_class' => 'bg-teal-50 text-teal-700 border border-teal-200 shadow-xs',
                'pill_bg' => 'bg-teal-500',
                'text_color' => 'text-teal-600',
                'border_color' => 'border-teal-300',
                'hex' => '#0d9488',
                'gradient' => 'from-sky-500 to-teal-400',
                'bar_color' => 'bg-teal-500',
                'accent_border' => 'border-l-4 border-l-teal-500',
                'description' => 'Minat Awal / Eksplorasi Produk',
                'ratio' => $ratio,
                'progress' => $progressPercent,
                'step' => ($index + 1) . '/' . $total,
                'is_dead' => false,
            ];
        }

        // Tier 3: Warm Lead (Amber / Gold)
        if ($ratio > 0.38 && $ratio <= 0.65) {
            return [
                'key' => 'warm',
                'label' => 'Warm Lead',
                'icon' => '🌤️',
                'badge_class' => 'bg-amber-50 text-amber-800 border border-amber-200 shadow-xs',
                'pill_bg' => 'bg-amber-500',
                'text_color' => 'text-amber-600',
                'border_color' => 'border-amber-300',
                'hex' => '#f59e0b',
                'gradient' => 'from-teal-500 to-amber-400',
                'bar_color' => 'bg-amber-500',
                'accent_border' => 'border-l-4 border-l-amber-500',
                'description' => 'Prospek Aktif / Tanya Harga & Spek',
                'ratio' => $ratio,
                'progress' => $progressPercent,
                'step' => ($index + 1) . '/' . $total,
                'is_dead' => false,
            ];
        }

        // Tier 4: Very Warm Lead (Vibrant Orange)
        if ($ratio > 0.65 && $ratio < 0.88) {
            return [
                'key' => 'very_warm',
                'label' => 'Very Warm',
                'icon' => '⚡',
                'badge_class' => 'bg-orange-50 text-orange-800 border border-orange-200 shadow-xs',
                'pill_bg' => 'bg-orange-500',
                'text_color' => 'text-orange-600',
                'border_color' => 'border-orange-300',
                'hex' => '#ea580c',
                'gradient' => 'from-amber-500 to-orange-500',
                'bar_color' => 'bg-orange-500',
                'accent_border' => 'border-l-4 border-l-orange-500',
                'description' => 'Prospek Hangat / Penawaran & Negosiasi',
                'ratio' => $ratio,
                'progress' => $progressPercent,
                'step' => ($index + 1) . '/' . $total,
                'is_dead' => false,
            ];
        }

        // Tier 5: Hot Lead (Rose / Crimson Red - Closing & Deal)
        return [
            'key' => 'hot',
            'label' => 'Hot Lead',
            'icon' => '🔥',
            'badge_class' => 'bg-rose-50 text-rose-800 border border-rose-200 shadow-xs',
            'pill_bg' => 'bg-rose-600',
            'text_color' => 'text-rose-600',
            'border_color' => 'border-rose-300',
            'hex' => '#e11d48',
            'gradient' => 'from-orange-500 to-rose-600',
            'bar_color' => 'bg-rose-600',
            'accent_border' => 'border-l-4 border-l-rose-500',
            'description' => 'Mendekati Closing / Meeting Call / Deal',
            'ratio' => $ratio,
            'progress' => $progressPercent,
            'step' => ($index + 1) . '/' . $total,
            'is_dead' => false,
        ];
    }
}
