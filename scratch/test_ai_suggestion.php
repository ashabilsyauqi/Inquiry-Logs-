<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\WaAccount;
use App\Models\PipelineStage;
use App\Services\GeminiAiService;
use App\Jobs\AnalyzeLeadStageWithAiJob;

echo "--- TESTING AI SUGGESTION FEATURE ---\n";

$waAccount = WaAccount::firstOrCreate([
    'session_id' => 'test_ai_session'
], [
    'name' => 'Test Account AI',
    'phone' => '628123456789',
    'status' => 'CONNECTED'
]);
$waAccount->ensureDefaultStages();

$lead = Lead::firstOrCreate([
    'phone' => '628999888777'
], [
    'name' => 'Test Customer AI',
    'wa_account_id' => $waAccount->id,
    'stage' => 'Lead Masuk'
]);

// Add chat messages indicating deal intent
LeadMessage::create([
    'lead_id' => $lead->id,
    'sender' => '628999888777',
    'message' => 'Halo kak, aku mau ambil Paket Pro harganya berapa?',
    'is_from_me' => false
]);

LeadMessage::create([
    'lead_id' => $lead->id,
    'sender' => '628123456789',
    'message' => 'Halo kak! Paket Pro harganya 500rb per bulan ya.',
    'is_from_me' => true
]);

LeadMessage::create([
    'lead_id' => $lead->id,
    'sender' => '628999888777',
    'message' => 'Oke kak saya fix ambil Paket Pro ya, minta no rek BCA nya.',
    'is_from_me' => false
]);

LeadMessage::create([
    'lead_id' => $lead->id,
    'sender' => '628123456789',
    'message' => 'Siap kak, rekening BCA 1234567890 a.n Difitech ya.',
    'is_from_me' => true
]);

echo "Lead Initial Stage: " . $lead->stage . "\n";
echo "Dispatching AnalyzeLeadStageWithAiJob...\n";

AnalyzeLeadStageWithAiJob::dispatchSync($lead->id);

$lead->refresh();
echo "AI Suggested Stage: " . ($lead->ai_suggested_stage ?: 'None') . "\n";
echo "AI Suggested Keyword: " . ($lead->ai_suggested_keyword ?: 'None') . "\n";
echo "AI Suggestion Reason: " . ($lead->ai_suggestion_reason ?: 'None') . "\n";

echo "--- TEST COMPLETE ---\n";
