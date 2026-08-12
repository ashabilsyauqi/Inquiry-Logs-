<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaAccount;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckWaDisconnects extends Command
{
    protected $signature = 'wa:check-disconnects';
    protected $description = 'Check for disconnected WA accounts and send automated disconnect email alerts based on interval settings';

    public function handle()
    {
        $disconnectedAccounts = WaAccount::where('approval_status', 'APPROVED')
            ->where('disconnect_email_enabled', true)
            ->where('status', 'DISCONNECTED')
            ->get();

        if ($disconnectedAccounts->isEmpty()) {
            return Command::SUCCESS;
        }

        $webhookCtrl = new WebhookController();

        foreach ($disconnectedAccounts as $acc) {
            $intervalSeconds = $acc->disconnect_email_interval ?: 10;
            $lastSent = $acc->last_disconnect_email_sent_at ? \Carbon\Carbon::parse($acc->last_disconnect_email_sent_at) : null;

            if (!$lastSent || $lastSent->diffInSeconds(now()) >= $intervalSeconds) {
                Log::info("⏰ Auto Disconnect Checker: Sending alert for Account {$acc->name} (Interval: {$intervalSeconds}s)");
                
                $testRequest = new Request([
                    'sessionId' => $acc->session_id ?: $acc->id,
                    'reason' => 'Perangkat WA tidak terhubung (Sistem Deteksi Otomatis Interval ' . ($intervalSeconds < 60 ? "{$intervalSeconds} Detik" : ($intervalSeconds/60) . " Menit") . ')',
                    'forceTest' => true
                ]);

                $webhookCtrl->handleDisconnectAlert($testRequest);
                $this->info("✅ Email disconnect alert sent for Account {$acc->name}");
            }
        }

        return Command::SUCCESS;
    }
}
