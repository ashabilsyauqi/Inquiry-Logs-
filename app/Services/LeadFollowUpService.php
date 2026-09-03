<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadMessage;
use Carbon\Carbon;

class LeadFollowUpService
{
    /**
     * Common Follow-Up keywords and phrases in Indonesian customer service conversations
     */
    protected static array $fuKeywords = [
        'izin follow up', 'ijin follow up', 'izin fu', 'ijin fu', '#fu',
        'follow up', 'follow-up', 'tindak lanjut', 'kelanjutan',
        'bagaimana kelanjutannya', 'bagaimana kelanjutan', 'bagaimana kak',
        'bagaimana kabarnya', 'apakah jadi', 'apakah berminat', 'mau tanya kak',
        'ada kendala', 'penawaran kami', 'promo spesial', 'diskon khusus',
        'info lebih lanjut', 'izin mengingatkan', 'ijin mengingatkan',
        'apakah sudah sempat', 'apakah ada yang bisa dibantu'
    ];

    /**
     * Analyze a lead's messages to determine follow-up count, daily status, and history.
     *
     * @param Lead $lead
     * @return array
     */
    public static function getFollowUpData(Lead $lead): array
    {
        $stage = $lead->stage ?? '';
        $isDeadOrDeal = LeadTemperatureService::isDeadStage($stage) || strcasecmp(trim($stage), 'deal') === 0;

        // Fetch messages sorted chronologically
        $messages = $lead->relationLoaded('messages')
            ? $lead->messages->sortBy('created_at')->values()
            : $lead->messages()->oldest()->get();

        $fuSessions = [];
        $lastClientMessageTime = null;
        $processedDates = []; // Track dates to enforce 1 FU session per calendar day

        foreach ($messages as $msg) {
            $msgTime = $msg->created_at ? Carbon::parse($msg->created_at) : now();
            $msgDateStr = $msgTime->format('Y-m-d');
            $text = strtolower(trim($msg->message ?? ''));

            if (!$msg->is_from_me) {
                // Incoming message from client
                $lastClientMessageTime = $msgTime;
            } else {
                // Outbound message from CS
                $isFuTrigger = false;
                $reason = '';

                // Check 1: Explicit Follow-Up Keyword Match
                foreach (self::$fuKeywords as $kw) {
                    if (str_contains($text, $kw)) {
                        $isFuTrigger = true;
                        $reason = "Keyword: '{$kw}'";
                        break;
                    }
                }

                // Check 2: Time-gap re-engagement (CS sends message after >6 hours of client inactivity)
                if (!$isFuTrigger && $lastClientMessageTime !== null) {
                    $diffHours = $lastClientMessageTime->diffInHours($msgTime);
                    if ($diffHours >= 6) {
                        $isFuTrigger = true;
                        $reason = "Jeda waktu > {$diffHours} jam";
                    }
                }

                // If FU trigger detected and not yet recorded for this calendar day
                if ($isFuTrigger && !in_array($msgDateStr, $processedDates)) {
                    $processedDates[] = $msgDateStr;
                    $fuSessions[] = [
                        'date' => $msgTime->format('d M Y'),
                        'time' => $msgTime->format('H:i'),
                        'datetime' => $msgTime,
                        'message' => $msg->message,
                        'reason' => $reason,
                    ];
                }
            }
        }

        $fuCount = count($fuSessions);
        $lastFu = !empty($fuSessions) ? end($fuSessions) : null;
        $lastFuDate = $lastFu ? $lastFu['datetime'] : null;

        $today = Carbon::today();
        $isFollowedUpToday = $lastFuDate && $lastFuDate->isSameDay($today);

        // Determine Status, Badge, and Tooltip
        if ($isDeadOrDeal) {
            $status = 'RESOLVED';
            $statusLabel = 'Selesai / Deal';
            $badgeClass = 'bg-slate-100 text-slate-600 border border-slate-200';
            $statusIcon = '⚪';
            $tooltip = 'Lead sudah Deal atau Masuk kategori Non-Aktif/Spam';
        } elseif ($isFollowedUpToday) {
            $status = 'FOLLOWED_UP_TODAY';
            $statusLabel = 'Di-FU Hari Ini (' . ($lastFu['time'] ?? '') . ')';
            $badgeClass = 'bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold';
            $statusIcon = '🟢';
            $tooltip = 'CS sudah melakukan follow-up hari ini pada jam ' . ($lastFu['time'] ?? '');
        } else {
            // Check hours since creation or last interaction
            $leadAgeDays = $lead->created_at ? Carbon::parse($lead->created_at)->diffInDays(now()) : 0;
            $lastActivity = $lastFuDate ?: ($lead->created_at ? Carbon::parse($lead->created_at) : now());
            $hoursInactive = $lastActivity->diffInHours(now());

            if ($hoursInactive >= 48) {
                $status = 'OVERDUE';
                $statusLabel = 'Overdue (>48 Jam)';
                $badgeClass = 'bg-rose-100 text-rose-800 border border-rose-300 font-bold animate-pulse';
                $statusIcon = '🔴';
                $tooltip = 'Peringatan: Belum ada follow up selama lebih dari 48 jam!';
            } else {
                $status = 'DUE_TODAY';
                $statusLabel = 'Perlu FU Hari Ini';
                $badgeClass = 'bg-amber-100 text-amber-800 border border-amber-300 font-bold';
                $statusIcon = '🟡';
                $tooltip = 'Lead aktif perlu di-follow up hari ini';
            }
        }

        return [
            'count' => $fuCount,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_icon' => $statusIcon,
            'badge_class' => $badgeClass,
            'tooltip' => $tooltip,
            'is_followed_up_today' => $isFollowedUpToday,
            'last_fu_at' => $lastFu ? $lastFu['datetime']->format('d M Y, H:i') : null,
            'last_fu_text' => $lastFu ? $lastFu['message'] : null,
            'fu_sessions' => array_reverse($fuSessions), // Newest first for UI logs
        ];
    }
}
