<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WaBridgeCommand extends Command
{
    protected $signature = 'wa:bridge {--start : Start the WA Bridge server if not running} {--restart : Force restart WA Bridge} {--status : Check WA Bridge status} {--watchdog : Auto-start if offline (for Cron Jobs)}';
    protected $description = 'Manage and auto-heal the WhatsApp Bridge (Node.js/Baileys) process';

    public function handle()
    {
        $bridgeUrl = rtrim(env('WA_BRIDGE_URL', 'http://127.0.0.1:3001'), '/');
        $port = parse_url($bridgeUrl, PHP_URL_PORT) ?: 3001;

        if ($this->option('status')) {
            return $this->checkStatus($bridgeUrl, $port);
        }

        if ($this->isListening($port)) {
            $this->info("🟢 WA Bridge sudah ONLINE dan aktif di {$bridgeUrl} (Port: {$port})");
            return Command::SUCCESS;
        }

        if ($this->option('restart') || $this->option('start') || $this->option('watchdog')) {
            if (!function_exists('shell_exec') && !function_exists('exec')) {
                $this->warn("⚠️ Fungsi PHP shell_exec/exec dinonaktifkan di php.ini server cPanel.");
                $this->line("💡 Jalankan manual di Terminal cPanel:");
                $this->info("   cd ~/crm.difitech.id/wa-bridge && nohup node index.js > ../storage/logs/wa-bridge.log 2>&1 &");
                return Command::FAILURE;
            }

            $this->info("🔄 Memulai WA Bridge di Port {$port}...");
            $this->killExistingProcess($port);
            return $this->startBridge($bridgeUrl, $port);
        }

        return $this->checkStatus($bridgeUrl, $port);
    }

    protected function checkStatus($bridgeUrl, $port)
    {
        if ($this->isListening($port)) {
            try {
                $res = Http::timeout(3)->get("{$bridgeUrl}/api/qr?session=test_status");
                $this->info("🟢 WA Bridge ONLINE dan responsif di {$bridgeUrl} (Port {$port})");
                return Command::SUCCESS;
            } catch (\Throwable $e) {
                $this->warn("🟡 Port {$port} terbuka tetapi respons lambat: " . $e->getMessage());
                return Command::SUCCESS;
            }
        } else {
            $this->error("🔴 WA Bridge OFFLINE di {$bridgeUrl} (Port {$port} tidak merespons)");
            $this->line("💡 Jalankan di Terminal Server:");
            $this->info("   cd ~/crm.difitech.id/wa-bridge && nohup node index.js > ../storage/logs/wa-bridge.log 2>&1 &");
            return Command::FAILURE;
        }
    }

    protected function startBridge($bridgeUrl, $port)
    {
        $bridgeDir = base_path('wa-bridge');
        $logDir = storage_path('logs');
        if (!file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = storage_path('logs/wa-bridge.log');

        if (!file_exists($bridgeDir)) {
            $this->error("❌ Direktori {$bridgeDir} tidak ditemukan!");
            return Command::FAILURE;
        }

        $nodePath = 'node';
        if (function_exists('shell_exec')) {
            $detected = trim(@shell_exec('which node 2>/dev/null') ?: '');
            if ($detected) $nodePath = $detected;
        }

        $cmd = "cd " . escapeshellarg($bridgeDir) . " && ({$nodePath} index.js >> " . escapeshellarg($logFile) . " 2>&1 &)";
        if (function_exists('popen')) {
            @pclose(@popen($cmd, 'r'));
        } elseif (function_exists('exec')) {
            @exec($cmd);
        }

        $this->line("⏳ Memverifikasi konektivitas socket port {$port}...");
        $attempts = 0;
        $maxAttempts = 5;
        $isUp = false;

        while ($attempts < $maxAttempts) {
            sleep(1);
            if ($this->isListening($port)) {
                $isUp = true;
                break;
            }
            $attempts++;
        }

        if ($isUp) {
            $this->info("🎉 WA Bridge BERHASIL DIAKTIFKAN!");
            $this->info("   URL: {$bridgeUrl}");
            $this->info("   Log: {$logFile}");
            return Command::SUCCESS;
        } else {
            $this->error("⚠️ WA Bridge belum merespons dalam {$maxAttempts} detik.");
            $this->line("   Silakan jalankan manual: cd ~/crm.difitech.id/wa-bridge && nohup node index.js &");
            return Command::FAILURE;
        }
    }

    protected function isListening($port)
    {
        $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    protected function killExistingProcess($port)
    {
        if (function_exists('shell_exec')) {
            $pids = trim(@shell_exec("lsof -t -i:{$port} 2>/dev/null") ?: '');
            if ($pids) {
                foreach (explode("\n", $pids) as $pid) {
                    $pid = trim($pid);
                    if ($pid && is_numeric($pid)) {
                        @shell_exec("kill -9 {$pid} 2>/dev/null");
                    }
                }
            }
        }
    }
}
