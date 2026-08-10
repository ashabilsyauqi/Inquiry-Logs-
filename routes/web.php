<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\WaAccount;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnalyticsController;
use Carbon\Carbon;

// Guest Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated CRM Routes
Route::middleware(['auth'])->group(function () {

    Route::get('/', function (Request $request) {
        $user = Auth::user();
        $filter = $request->query('filter', 'all');
        $accountId = $request->query('account_id', 'all');

        // Sales Admin Isolation: Force account_id to assigned WA account
        if (!$user->isCeo()) {
            $accountId = $user->wa_account_id ?: 'none';
        }

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
            if ($accountId === 'none') {
                $query->whereRaw('1 = 0'); // Return empty if admin has no assigned WA account
            } else {
                $query->where('wa_account_id', $accountId);
                $activeAccount = WaAccount::find($accountId);
            }
        }

        $leads = $query->latest()->get();

        // CEO sees all WA Accounts, Admin only sees their own assigned WA Account
        if ($user->isCeo()) {
            $waAccounts = WaAccount::all();
        } else {
            $waAccounts = $user->wa_account_id ? WaAccount::where('id', $user->wa_account_id)->get() : collect();
        }

        $totalLeads = $leads->count();
        $totalLeadMasuk = $leads->where('stage', 'Lead Masuk')->count();
        $totalMeetingCall = $leads->where('stage', 'Meeting Call')->count();
        $totalKirimPenawaran = $leads->where('stage', 'Kirim Penawaran')->count();
        $totalDeal = $leads->where('stage', 'Deal')->count();

        return view('dashboard', compact(
            'leads', 'filter', 'accountId', 'activeAccount', 'waAccounts', 'user',
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

    // Analytics Chart API
    Route::get('/api/analytics/chart-data', [AnalyticsController::class, 'getChartData']);

    // WA Accounts Routes
    Route::get('/wa-accounts', function () {
        $user = Auth::user();
        if ($user->isCeo()) {
            return response()->json(WaAccount::all());
        }
        return response()->json(WaAccount::where('id', $user->wa_account_id)->get());
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

    // CEO User Approval Routes
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/{id}/approve', [UserController::class, 'approve']);
    Route::post('/users/{id}/reject', [UserController::class, 'reject']);
});
