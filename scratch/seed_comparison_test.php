<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\WaAccount;
use App\Services\LeadComparisonService;

echo "--- SEEDING AI VS REAL LEADS TEST DATA ---\n";

$account = WaAccount::first();
if (!$account) {
    $account = WaAccount::create([
        'name' => 'Demo Difitech WA',
        'phone' => '628123456789',
        'session_id' => 'demo_session',
        'status' => 'CONNECTED',
        'approval_status' => 'APPROVED'
    ]);
    $account->ensureDefaultStages();
}

// Sample leads dataset
$sampleLeads = [
    [
        'name' => 'Budi Santoso (PT Maju)',
        'phone' => '628111222333',
        'stage' => 'Deal',
        'ai_concluded_stage' => 'Deal',
        'ai_suggested_stage' => null,
        'ai_suggestion_reason' => 'Pelanggan telah mengirim bukti transfer pembayaran paket enterprise.',
    ],
    [
        'name' => 'Siti Nurhaliza (CV Berkah)',
        'phone' => '628111222334',
        'stage' => 'Lead Masuk', // CS forgot to update
        'ai_concluded_stage' => 'Deal', // AI concluded Deal!
        'ai_suggested_stage' => 'Deal',
        'ai_suggested_keyword' => '#deal',
        'ai_suggestion_reason' => 'Chat menyepakati harga 15jt dan meminta nomor rekening BCA.',
    ],
    [
        'name' => 'Agus Pratama (Toko Abadi)',
        'phone' => '628111222335',
        'stage' => 'Meeting Call',
        'ai_concluded_stage' => 'Meeting Call',
        'ai_suggested_stage' => null,
        'ai_suggestion_reason' => 'Jadwal Google Meet disepakati hari Selasa jam 14:00.',
    ],
    [
        'name' => 'Rina Wijaya (StartUp Tech)',
        'phone' => '628111222336',
        'stage' => 'Lead Masuk', // CS forgot to update
        'ai_concluded_stage' => 'Meeting Call', // AI concluded Meeting!
        'ai_suggested_stage' => 'Meeting Call',
        'ai_suggested_keyword' => '/meeting',
        'ai_suggestion_reason' => 'CS dan klien mendiskusikan link zoom meeting besok pagi.',
    ],
    [
        'name' => 'Dedi Kurniawan',
        'phone' => '628111222337',
        'stage' => 'Kirim Penawaran',
        'ai_concluded_stage' => 'Kirim Penawaran',
        'ai_suggested_stage' => null,
        'ai_suggestion_reason' => 'CS mengirimkan PDF proposal penawaran harga.',
    ],
    [
        'name' => 'Hendro Susilo',
        'phone' => '628111222338',
        'stage' => 'Lead Masuk',
        'ai_concluded_stage' => 'Lead Masuk',
        'ai_suggested_stage' => null,
        'ai_suggestion_reason' => 'Chat baru tahap perkenalan salam awal.',
    ],
];

foreach ($sampleLeads as $data) {
    Lead::updateOrCreate(
        ['phone' => $data['phone']],
        array_merge($data, [
            'wa_account_id' => $account->id,
            'ai_suggested_at' => now(),
        ])
    );
}

echo "Created/Updated " . count($sampleLeads) . " test leads.\n";

$service = new LeadComparisonService();
$result = $service->generateComparisonData($account->id, 'all_time');

echo "\n--- COMPARISON SUMMARY ---\n";
echo "Total Leads: " . $result['total_leads'] . "\n";
echo "Match Rate: " . $result['match_rate'] . "%\n";
echo "Discrepancy Count: " . $result['discrepancy_count'] . " (" . $result['discrepancy_rate'] . "%)\n";
print_r($result['stage_comparison']);

echo "\n--- CREATING TEST SNAPSHOT ---\n";
$snapshot = $service->createSnapshot($account->id);
echo "Snapshot saved for date: " . $snapshot->report_date->format('Y-m-d') . " (ID: {$snapshot->id})\n";
echo "--- ALL DONE SUCCESSFULLY! ---\n";
