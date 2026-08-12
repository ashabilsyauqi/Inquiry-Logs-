<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\WaAccount;
use App\Models\PipelineStage;
use App\Models\StageTrigger;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\WebhookController;
use Carbon\Carbon;

// Guest Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Webhook Disconnection Alert Route
Route::post('/api/wa-disconnect-alert', [WebhookController::class, 'handleDisconnectAlert']);

// Authenticated CRM Routes
Route::middleware(['auth'])->group(function () {

    Route::get('/', function (Request $request) {
        $user = Auth::user();
        $filter = $request->query('filter', 'all');
        $accountId = $request->query('account_id', 'all');

        // Sales Admin Auto WA Account Provisioning
        if (!$user->isCeo() && !$user->wa_account_id) {
            $waAccount = WaAccount::create([
                'name' => 'WA ' . $user->name,
                'session_id' => 'session_user_' . $user->id,
                'status' => 'DISCONNECTED'
            ]);
            $waAccount->ensureDefaultStages();
            $user->wa_account_id = $waAccount->id;
            $user->save();
        }

        // Sales Admin Isolation: Force account_id to assigned WA account
        if (!$user->isCeo()) {
            $accountId = $user->wa_account_id;
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
            $query->where('wa_account_id', $accountId);
            $activeAccount = WaAccount::with(['pipelineStages.triggers'])->find($accountId);
            if ($activeAccount) {
                $activeAccount->ensureDefaultStages();
            }
        }

        $leads = $query->latest()->get();

        // CEO sees all APPROVED WA Accounts with lead counts & metrics
        if ($user->isCeo()) {
            $waAccounts = WaAccount::with(['leads', 'pipelineStages.triggers'])->where('approval_status', 'APPROVED')->get();
            foreach ($waAccounts as $acc) {
                $acc->ensureDefaultStages();
            }
        } else {
            $waAccounts = $user->wa_account_id ? WaAccount::with(['leads', 'pipelineStages.triggers'])->where('id', $user->wa_account_id)->where('approval_status', 'APPROVED')->get() : collect();
        }

        $totalLeads = $leads->count();

        // Determine pipeline stages for current view
        if ($activeAccount) {
            $stages = $activeAccount->pipelineStages;
        } elseif ($user->isCeo() && $accountId === 'all') {
            $stages = PipelineStage::whereNull('wa_account_id')->orWhereIn('wa_account_id', $waAccounts->pluck('id'))->get()->unique('name');
            if ($stages->isEmpty()) {
                $stages = collect([
                    (object)['id' => 1, 'name' => 'Lead Masuk', 'color' => 'purple', 'is_default' => true],
                    (object)['id' => 2, 'name' => 'Meeting Call', 'color' => 'blue', 'is_default' => false],
                    (object)['id' => 3, 'name' => 'Kirim Penawaran', 'color' => 'yellow', 'is_default' => false],
                    (object)['id' => 4, 'name' => 'Deal', 'color' => 'green', 'is_default' => false],
                ]);
            }
        } else {
            $stages = collect([
                (object)['id' => 1, 'name' => 'Lead Masuk', 'color' => 'purple', 'is_default' => true],
                (object)['id' => 2, 'name' => 'Meeting Call', 'color' => 'blue', 'is_default' => false],
                (object)['id' => 3, 'name' => 'Kirim Penawaran', 'color' => 'yellow', 'is_default' => false],
                (object)['id' => 4, 'name' => 'Deal', 'color' => 'green', 'is_default' => false],
            ]);
        }

        return view('dashboard', compact(
            'leads', 'filter', 'accountId', 'activeAccount', 'waAccounts', 'user', 'stages',
            'totalLeads'
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
            return response()->json(WaAccount::with(['pipelineStages.triggers'])->where('approval_status', 'APPROVED')->get());
        }

        if (!$user->wa_account_id) {
            $waAccount = WaAccount::create([
                'name' => 'WA ' . $user->name,
                'session_id' => 'session_user_' . $user->id,
                'status' => 'DISCONNECTED'
            ]);
            $waAccount->ensureDefaultStages();
            $user->wa_account_id = $waAccount->id;
            $user->save();
        }

        return response()->json(WaAccount::with(['pipelineStages.triggers'])->where('id', $user->wa_account_id)->get());
    });

    Route::post('/wa-accounts', function (Request $request) {
        $name = $request->input('name', 'New Brand Account');
        $category = $request->input('category', 'General Business');
        $phoneInput = $request->input('phone');
        $phone = $phoneInput ? preg_replace('/[^0-9]/', '', $phoneInput) : null;
        $sessionId = 'session_' . time();

        $account = WaAccount::create([
            'name' => $name,
            'category' => $category,
            'phone' => $phone,
            'session_id' => $sessionId,
            'status' => 'DISCONNECTED',
            'approval_status' => 'APPROVED'
        ]);
        $account->ensureDefaultStages();

        return response()->json(['status' => 'success', 'account' => $account]);
    });

    Route::post('/wa-accounts/{id}/update', function (Request $request, $id) {
        $account = WaAccount::findOrFail($id);
        if ($request->has('name')) {
            $account->name = $request->input('name');
        }
        if ($request->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
            $account->phone = $phone ?: null;
        }
        if ($request->has('session_id')) {
            $account->session_id = $request->input('session_id');
        }
        if ($request->has('status')) {
            $account->status = $request->input('status');
        }
        $account->save();

        return response()->json(['status' => 'success', 'message' => 'Data Brand Berhasil Diperbarui!', 'account' => $account]);
    });

    Route::post('/wa-accounts/{id}/delete', function ($id) {
        $account = WaAccount::findOrFail($id);
        $account->delete();
        return response()->json(['status' => 'success']);
    });

    // Custom Pipeline Stages CRUD
    Route::post('/pipeline-stages', function (Request $request) {
        $waAccountId = $request->input('wa_account_id');
        $name = $request->input('name');
        $color = $request->input('color', 'purple');

        if (!$name || !$waAccountId) {
            return response()->json(['status' => 'error', 'message' => 'Missing name or account'], 400);
        }

        $isFirst = PipelineStage::where('wa_account_id', $waAccountId)->count() === 0;
        $maxOrder = PipelineStage::where('wa_account_id', $waAccountId)->max('order') ?? 0;

        $stage = PipelineStage::create([
            'wa_account_id' => $waAccountId,
            'name' => $name,
            'order' => $maxOrder + 1,
            'color' => $color,
            'is_default' => $isFirst,
        ]);

        return response()->json(['status' => 'success', 'stage' => $stage]);
    });

    Route::post('/pipeline-stages/{id}/rename', function (Request $request, $id) {
        $stage = PipelineStage::findOrFail($id);
        $newName = trim($request->input('name'));
        if ($newName) {
            $oldName = $stage->name;
            $stage->name = $newName;
            $stage->save();

            // Update existing leads with old stage name to new stage name
            Lead::where('wa_account_id', $stage->wa_account_id)
                ->where('stage', $oldName)
                ->update(['stage' => $newName]);
        }
        return response()->json(['status' => 'success', 'stage' => $stage]);
    });

    Route::post('/pipeline-stages/{id}/set-default', function ($id) {
        $stage = PipelineStage::findOrFail($id);
        PipelineStage::where('wa_account_id', $stage->wa_account_id)->update(['is_default' => false]);
        $stage->is_default = true;
        $stage->save();

        return response()->json(['status' => 'success', 'message' => "Stage '{$stage->name}' diset sebagai Entry Stage Inquiry Masuk!"]);
    });

    Route::post('/pipeline-stages/{id}/delete', function ($id) {
        $stage = PipelineStage::findOrFail($id);
        $waAccountId = $stage->wa_account_id;
        $wasDefault = $stage->is_default;
        $stage->delete();

        if ($wasDefault) {
            $nextStage = PipelineStage::where('wa_account_id', $waAccountId)->first();
            if ($nextStage) {
                $nextStage->is_default = true;
                $nextStage->save();
            }
        }

        return response()->json(['status' => 'success']);
    });

    // Custom Keyword Triggers CRUD
    Route::post('/stage-triggers', function (Request $request) {
        $waAccountId = $request->input('wa_account_id');
        $stageId = $request->input('pipeline_stage_id');
        $keyword = strtolower(trim($request->input('keyword')));

        if (!$keyword || !$waAccountId || !$stageId) {
            return response()->json(['status' => 'error', 'message' => 'Missing parameters'], 400);
        }

        $trigger = StageTrigger::create([
            'wa_account_id' => $waAccountId,
            'pipeline_stage_id' => $stageId,
            'keyword' => $keyword,
        ]);

        return response()->json(['status' => 'success', 'trigger' => $trigger]);
    });

    Route::post('/stage-triggers/{id}/delete', function ($id) {
        $trigger = StageTrigger::findOrFail($id);
        $trigger->delete();
        return response()->json(['status' => 'success']);
    });

    // CEO Brand & Supervisor Approval Routes
    Route::get('/brand-approvals', [UserController::class, 'index']);
    Route::post('/brand-approvals/{id}/approve', [UserController::class, 'approveBrand']);
    Route::post('/brand-approvals/{id}/reject', [UserController::class, 'rejectBrand']);

    // WA Disconnect Email Alert Settings & Testing Routes
    Route::post('/wa-accounts/{id}/update-disconnect-settings', function (Request $request, $id) {
        $waAccount = WaAccount::findOrFail($id);
        $waAccount->disconnect_email_enabled = (bool) $request->input('enabled');
        $waAccount->disconnect_email_interval = (int) $request->input('interval');
        $waAccount->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan Notifikasi Email Disconnect Berhasil Diperbarui!',
            'account' => $waAccount
        ]);
    });

    Route::post('/wa-accounts/{id}/test-disconnect-email', function (Request $request, $id) {
        $waAccount = WaAccount::findOrFail($id);
        
        $webhookCtrl = new \App\Http\Controllers\WebhookController();
        $testRequest = new Request([
            'sessionId' => $waAccount->session_id,
            'reason' => 'Uji Coba Pengiriman Notifikasi Email Disconnect (Tombol Tes Admin)',
            'forceTest' => true
        ]);

        return $webhookCtrl->handleDisconnectAlert($testRequest);
    });
});

