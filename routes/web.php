<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\WaAccount;
use Carbon\Carbon;

Route::get('/', function (Request $request) {
    $filter = $request->query('filter', 'all');
    $accountId = $request->query('account_id', 'all');

    $query = Lead::with('waAccount');

    if ($filter === 'daily') {
        $query->whereDate('created_at', Carbon::today());
    } elseif ($filter === 'monthly') {
        $query->whereMonth('created_at', Carbon::now()->month)
              ->whereYear('created_at', Carbon::now()->year);
    } elseif ($filter === 'yearly') {
        $query->whereYear('created_at', Carbon::now()->year);
    }

    $activeAccount = null;
    if ($accountId !== 'all') {
        $query->where('wa_account_id', $accountId);
        $activeAccount = WaAccount::find($accountId);
    }

    $leads = $query->latest()->get();
    $waAccounts = WaAccount::all();

    $totalLeads = $leads->count();
    $totalLeadMasuk = $leads->where('stage', 'Lead Masuk')->count();
    $totalMeetingCall = $leads->where('stage', 'Meeting Call')->count();
    $totalKirimPenawaran = $leads->where('stage', 'Kirim Penawaran')->count();
    $totalDeal = $leads->where('stage', 'Deal')->count();

    return view('dashboard', compact(
        'leads', 'filter', 'accountId', 'activeAccount', 'waAccounts',
        'totalLeads', 'totalLeadMasuk', 'totalMeetingCall', 'totalKirimPenawaran', 'totalDeal'
    ));
});

Route::get('/leads/{id}/detail', function ($id) {
    $lead = Lead::with(['messages' => function($q) {
        $q->orderBy('created_at', 'asc');
    }, 'waAccount'])->findOrFail($id);

    return response()->json($lead);
});

Route::post('/leads/{id}/update', function (Request $request, $id) {
    $lead = Lead::findOrFail($id);

    if ($request->has('name')) {
        $lead->name = $request->input('name');
    }
    if ($request->has('notes')) {
        $lead->notes = $request->input('notes');
    }
    if ($request->has('priority')) {
        $lead->priority = (int) $request->input('priority');
    }
    if ($request->has('stage')) {
        $lead->stage = $request->input('stage');
    }

    $lead->save();
    return redirect()->back();
});

// WA Accounts Routes
Route::get('/wa-accounts', function () {
    return response()->json(WaAccount::all());
});

Route::post('/wa-accounts', function (Request $request) {
    $name = $request->input('name', 'New WA Account');
    $sessionId = 'session_' . time();

    $account = WaAccount::create([
        'name' => $name,
        'session_id' => $sessionId,
        'status' => 'DISCONNECTED'
    ]);

    return response()->json(['status' => 'success', 'account' => $account]);
});

Route::post('/wa-accounts/{id}/delete', function ($id) {
    $account = WaAccount::findOrFail($id);
    $account->delete();
    return response()->json(['status' => 'success']);
});
