<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaAccount;
use App\Models\AiLeadComparison;
use App\Services\LeadComparisonService;

class LeadComparisonController extends Controller
{
    protected LeadComparisonService $comparisonService;

    public function __construct(LeadComparisonService $comparisonService)
    {
        $this->comparisonService = $comparisonService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $accountId = $request->query('account_id', 'all');
        $period = $request->query('period', 'all_time');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Sales Admin Isolation: Force account_id to assigned WA account if not CEO
        $assignedUserId = null;
        if (!$user->isCeo()) {
            $accountId = $user->wa_account_id ?? 'all';
            if ($user->role === 'SALES_ADMIN') {
                $assignedUserId = $user->id;
            }
        }

        $comparison = $this->comparisonService->generateComparisonData($accountId, $period, $startDate, $endDate, $assignedUserId);

        // Fetch WA Accounts for Filter Dropdown
        if ($user->isCeo()) {
            $waAccounts = WaAccount::where('approval_status', 'APPROVED')->get();
        } else {
            $waAccounts = $user->wa_account_id ? WaAccount::where('id', $user->wa_account_id)->where('approval_status', 'APPROVED')->get() : collect();
        }

        // Fetch Historical Snapshots
        $snapshots = AiLeadComparison::latest('report_date')->take(10)->get();

        return view('lead_comparison', compact('comparison', 'waAccounts', 'accountId', 'period', 'startDate', 'endDate', 'snapshots', 'user'));
    }

    public function apiData(Request $request)
    {
        $user = Auth::user();
        $accountId = $request->query('account_id', 'all');
        $period = $request->query('period', 'all_time');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$user->isCeo()) {
            $accountId = $user->wa_account_id ?? 'all';
        }

        $data = $this->comparisonService->generateComparisonData($accountId, $period, $startDate, $endDate);

        return response()->json($data);
    }

    public function storeSnapshot(Request $request)
    {
        $user = Auth::user();
        $accountId = $request->input('account_id', 'all');

        if (!$user->isCeo()) {
            $accountId = $user->wa_account_id ?? 'all';
        }

        $snapshot = $this->comparisonService->createSnapshot($accountId);

        return redirect()->back()->with('success', 'Snapshot mingguan berhasil disimpan pada tanggal ' . $snapshot->report_date->format('d M Y') . '!');
    }

    public function scanAllLeads(Request $request)
    {
        $user = Auth::user();
        $query = \App\Models\Lead::with('messages');

        if (!$user->isCeo()) {
            if ($user->role === 'SALES_ADMIN') {
                $query->where('assigned_user_id', $user->id);
            } elseif ($user->wa_account_id) {
                $query->where('wa_account_id', $user->wa_account_id);
            }
        }

        $leads = $query->get();
        $aiService = new \App\Services\GeminiAiService();
        $scannedCount = 0;
        $discrepancyCount = 0;

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
                    $discrepancyCount++;
                } else if ($lead->stage === ($result['concluded_stage'] ?? $lead->stage)) {
                    $lead->ai_suggested_stage = null;
                    $lead->ai_suggested_keyword = null;
                    $lead->ai_suggestion_reason = null;
                }
                $lead->save();
                $scannedCount++;
            }
        }

        $msg = "⚡ Pemindaian AI selesai! {$scannedCount} percakapan lead berhasil dianalisis dengan kecerdasan Gemini. Ditemukan {$discrepancyCount} lead dengan selisih stage / indikasi lupa trigger.";
        return redirect()->back()->with('success', $msg);
    }

    public function simulate(Request $request)
    {
        $type = $request->input('type', 'deal_missed'); // deal_missed, meeting_missed, deal_resolved
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

        $randomPhone = '62812' . rand(1000000, 9999999);

        if ($type === 'deal_missed') {
            $lead = \App\Models\Lead::create([
                'name' => 'Simulasi Deal Missed (' . rand(100, 999) . ')',
                'phone' => $randomPhone,
                'stage' => 'Lead Masuk', // Real stage remains Lead Masuk
                'wa_account_id' => $account->id,
                'ai_concluded_stage' => 'Deal',
                'ai_suggested_stage' => 'Deal',
                'ai_suggested_keyword' => '#deal',
                'ai_suggestion_reason' => 'Simulasi: Pembeli menyatakan setuju harga paket enterprise dan meminta rekening transfer, CS memberikan rekening tapi lupa mengetik #deal.',
                'ai_suggested_at' => now(),
            ]);

            \App\Models\LeadMessage::create([
                'lead_id' => $lead->id,
                'sender' => $lead->phone,
                'message' => 'Halo kak, saya fix ambil paketnya ya, minta rekening BCA nya dong.',
                'is_from_me' => false,
            ]);

            \App\Models\LeadMessage::create([
                'lead_id' => $lead->id,
                'sender' => $account->phone,
                'message' => 'Baik kak, rekening BCA 1234567890 an Difitech ya kak.',
                'is_from_me' => true,
            ]);

            $msg = "✅ Berhasil mensimulasikan 'CS Lupa Ketik #deal'! Lead baru '{$lead->name}' masuk dengan Stage Real 'Lead Masuk' dan Kesimpulan AI 'Deal'. Cek perbedaannya di bawah!";
        } elseif ($type === 'meeting_missed') {
            $lead = \App\Models\Lead::create([
                'name' => 'Simulasi Meeting Missed (' . rand(100, 999) . ')',
                'phone' => $randomPhone,
                'stage' => 'Lead Masuk',
                'wa_account_id' => $account->id,
                'ai_concluded_stage' => 'Meeting Call',
                'ai_suggested_stage' => 'Meeting Call',
                'ai_suggested_keyword' => '/meeting',
                'ai_suggestion_reason' => 'Simulasi: Pembeli dan CS menyepakati sesi demo Google Meet besok jam 10 pagi, namun CS lupa mengetik /meeting.',
                'ai_suggested_at' => now(),
            ]);

            \App\Models\LeadMessage::create([
                'lead_id' => $lead->id,
                'sender' => $lead->phone,
                'message' => 'Bisa minta demo presentasi produk via Zoom besok jam 10 pagi kak?',
                'is_from_me' => false,
            ]);

            \App\Models\LeadMessage::create([
                'lead_id' => $lead->id,
                'sender' => $account->phone,
                'message' => 'Bisa banget kak! Link Zoom sudah kami kirimkan ke email ya.',
                'is_from_me' => true,
            ]);

            $msg = "✅ Berhasil mensimulasikan 'CS Lupa Ketik /meeting'! Lead '{$lead->name}' memiliki perbedaan stage di sistem!";
        } else {
            // Skenario CS membalas dengan keyword trigger
            $lead = \App\Models\Lead::whereNotNull('ai_suggested_stage')->latest()->first();
            if ($lead) {
                $prevAiStage = $lead->ai_suggested_stage;
                $lead->stage = $prevAiStage;
                $lead->ai_suggested_stage = null;
                $lead->ai_suggested_keyword = null;
                $lead->ai_suggestion_reason = null;
                $lead->ai_suggested_at = null;
                $lead->save();

                $msg = "⚡ Berhasil mensimulasikan CS mengetik keyword trigger! Lead '{$lead->name}' sekarang resmi berubah stage menjadi '{$prevAiStage}' dan kembali SELARAS (Match)!";
            } else {
                $msg = "ℹ️ Semua lead saat ini sudah selaras 100%. Coba klik simulasi 'CS Lupa Ketik #deal' terlebih dahulu!";
            }
        }

        return redirect()->route('ai-comparison.index')->with('success', $msg);
    }
}
