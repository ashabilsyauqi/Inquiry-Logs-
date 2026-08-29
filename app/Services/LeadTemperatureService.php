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
     * Determine the lead temperature (Cold ❄️, Warm 🌤️, Hot 🔥)
     * dynamically proportional to the brand's stage count.
     *
     * @param Lead $lead
     * @return array
     */
    public static function getTemperature(Lead $lead): array
    {
        $brandId = $lead->wa_account_id ?? 'global';

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

        $stages = self::$stageCache[$brandId];
        $totalStages = count($stages);
        $currentIndex = array_search($lead->stage, $stages);

        if ($currentIndex === false) {
            // Case-insensitive match fallback
            foreach ($stages as $idx => $stName) {
                if (strcasecmp($stName, $lead->stage ?? '') === 0) {
                    $currentIndex = $idx;
                    break;
                }
            }
            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        }

        return self::calculateTemperatureData($currentIndex, $totalStages);
    }

    /**
     * Determine temperature metadata for a specific stage
     *
     * @param int $stageIndex (0-indexed)
     * @param int $totalStages
     * @return array
     */
    public static function getStageTemperature(int $stageIndex, int $totalStages): array
    {
        return self::calculateTemperatureData($stageIndex, $totalStages);
    }

    /**
     * Internal calculation logic mapping 0..N-1 to Cold (Blue) -> Warm (Amber) -> Hot (Red)
     */
    protected static function calculateTemperatureData(int $index, int $total): array
    {
        $total = max(1, $total);
        $index = max(0, min($index, $total - 1));

        if ($total === 1) {
            $ratio = 0.0;
        } elseif ($total === 2) {
            $ratio = ($index === 0) ? 0.0 : 1.0;
        } else {
            $ratio = $index / ($total - 1);
        }

        $progressPercent = (int)round((($index + 1) / $total) * 100);

        if ($ratio < 0.34) {
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
                'description' => 'Tahap Prospek Awal / Minat Baru',
                'ratio' => $ratio,
                'progress' => $progressPercent,
                'step' => ($index + 1) . '/' . $total,
            ];
        } elseif ($ratio < 0.67) {
            return [
                'key' => 'warm',
                'label' => 'Warm Lead',
                'icon' => '🌤️',
                'badge_class' => 'bg-amber-50 text-amber-800 border border-amber-200 shadow-xs',
                'pill_bg' => 'bg-amber-500',
                'text_color' => 'text-amber-600',
                'border_color' => 'border-amber-300',
                'hex' => '#f59e0b',
                'gradient' => 'from-amber-500 to-orange-400',
                'bar_color' => 'bg-amber-500',
                'accent_border' => 'border-l-4 border-l-amber-500',
                'description' => 'Prospek Aktif / Diskusi & Penawaran',
                'ratio' => $ratio,
                'progress' => $progressPercent,
                'step' => ($index + 1) . '/' . $total,
            ];
        } else {
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
                'description' => 'Prospek Matang / Siap Closing & Deal',
                'ratio' => $ratio,
                'progress' => $progressPercent,
                'step' => ($index + 1) . '/' . $total,
            ];
        }
    }
}
