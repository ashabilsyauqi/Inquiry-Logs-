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
        if (!$user->isCeo()) {
            $accountId = $user->wa_account_id ?? 'all';
        }

        $comparison = $this->comparisonService->generateComparisonData($accountId, $period, $startDate, $endDate);

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
}
