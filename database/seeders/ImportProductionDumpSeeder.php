<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportProductionDumpSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = __DIR__ . '/production_dump.sql';
        if (!file_exists($sqlPath)) {
            $this->command->error("❌ SQL dump file not found at: {$sqlPath}");
            return;
        }

        $this->command->info("📦 Reading {$sqlPath}...");
        $content = file_get_contents($sqlPath);

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        $tables = [
            'lead_messages',
            'leads',
            'stage_triggers',
            'pipeline_stages',
            'brand_supervisors',
            'users',
            'wa_accounts',
            'smtp_settings',
            'ai_lead_comparisons'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        // Extract all INSERT INTO statements except migrations
        preg_match_all('/INSERT INTO\s+`([^`]+)`.*?;/s', $content, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
            foreach ($matches as $match) {
                $tableName = $match[1];
                $query = $match[0];

                if ($tableName === 'migrations' || $tableName === 'sessions' || $tableName === 'cache') {
                    continue;
                }

                try {
                    DB::unprepared($query);
                } catch (\Throwable $e) {
                    $this->command->warn("⚠️ Warning inserting into {$tableName}: " . substr($e->getMessage(), 0, 120));
                }
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $userCount = \App\Models\User::count();
        $brandCount = \App\Models\WaAccount::count();
        $leadCount = \App\Models\Lead::count();
        $msgCount = \App\Models\LeadMessage::count();

        $this->command->info("✅ Production Database Dump Imported Successfully!");
        $this->command->info("📊 Users: {$userCount} | Brands: {$brandCount} | Leads: {$leadCount} | Messages: {$msgCount}");
    }
}
