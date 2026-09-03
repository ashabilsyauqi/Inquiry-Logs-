<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\User;
use App\Models\WaAccount;
use App\Models\PipelineStage;
use App\Models\StageTrigger;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\LeadComparisonController;
use Carbon\Carbon;

// Guest Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Webhook Disconnection Alert Route
Route::post('/api/wa-disconnect-alert', [WebhookController::class, 'handleDisconnectAlert']);
Route::post('/api/wa-status-update', function (Request $request) {
    $sessionId = $request->input('sessionId') ?? $request->input('session_id');
    $status = $request->input('status', 'CONNECTED');
    $phone = $request->input('phone');

    if ($sessionId) {
        // 1. Sync User CS Session
        $user = User::where('session_id', $sessionId)->first();
        if (!$user && str_starts_with($sessionId, 'session_user_')) {
            $uId = (int)str_replace('session_user_', '', $sessionId);
            $user = User::find($uId);
        }
        if ($user) {
            $user->wa_status = $status;
            if ($phone) {
                $user->wa_phone = preg_replace('/[^0-9]/', '', $phone);
            }
            $user->save();

            // Auto-assign any unassigned leads in user's brand to this CS user
            if ($user->wa_account_id) {
                Lead::where('wa_account_id', $user->wa_account_id)
                    ->whereNull('assigned_user_id')
                    ->update(['assigned_user_id' => $user->id]);
            }
        }

        // 2. Sync WaAccount Brand Session
        $account = WaAccount::where('session_id', $sessionId)->first();
        if (!$account && is_numeric($sessionId)) {
            $account = WaAccount::find($sessionId);
        }
        if ($account) {
            $account->status = $status;
            if ($phone) {
                $account->phone = preg_replace('/[^0-9]/', '', $phone);
            }
            $account->save();

            // Auto-assign unassigned leads to brand's CS Admin
            $csAdmin = User::where('wa_account_id', $account->id)->where('role', 'SALES_ADMIN')->first();
            if ($csAdmin) {
                Lead::where('wa_account_id', $account->id)
                    ->whereNull('assigned_user_id')
                    ->update(['assigned_user_id' => $csAdmin->id]);
            }
        }

        return response()->json(['status' => 'success']);
    }
    return response()->json(['status' => 'error', 'message' => 'Account or session not found'], 404);
});

// Authenticated CRM Routes
Route::middleware(['auth'])->group(function () {

    Route::get('/', function (Request $request) {
        $user = Auth::user();
        $filter = $request->query('filter', 'all');
        $accountId = $request->query('account_id', 'all');

        // Ensure user has their personal session_id
        if (!$user->session_id) {
            $user->session_id = 'session_user_' . $user->id;
            $user->save();
        }

        // Sales Admin Auto WA Account Provisioning if not yet linked to any Brand
        if (!$user->isCeo() && !$user->wa_account_id) {
            $firstBrand = WaAccount::where('approval_status', 'APPROVED')->first();
            if ($firstBrand) {
                $user->wa_account_id = $firstBrand->id;
                $user->save();
            } else {
                $waAccount = WaAccount::create([
                    'name' => 'Brand ' . $user->name,
                    'session_id' => 'session_brand_' . time(),
                    'status' => 'DISCONNECTED',
                    'approval_status' => 'APPROVED'
                ]);
                $waAccount->ensureDefaultStages();
                $user->wa_account_id = $waAccount->id;
                $user->save();
            }
        }

        // Sales Admin Isolation: CS Admin is locked to their single assigned Brand WA account
        if ($user->isSalesAdmin()) {
            $accountId = $user->wa_account_id;
        }

        // Instant 2-Way Status Sync: Auto-sync Brand & User sessions with wa-bridge server
        $bridgeUrl = rtrim(env('WA_BRIDGE_URL', 'http://127.0.0.1:3001'), '/');
        $allApprovedAccs = WaAccount::where('approval_status', 'APPROVED')->get();
        foreach ($allApprovedAccs as $acc) {
            if ($acc->session_id) {
                try {
                    $res = \Illuminate\Support\Facades\Http::timeout(2)->get($bridgeUrl . '/api/qr?session=' . $acc->session_id);
                    if ($res->successful()) {
                        $data = $res->json();
                        $bridgeStatus = $data['sessionStatus'] ?? null;

                        if ($bridgeStatus === 'CONNECTED') {
                            $acc->status = 'CONNECTED';
                            if (!empty($data['phone'])) {
                                $acc->phone = preg_replace('/[^0-9]/', '', $data['phone']);
                            }
                            $acc->last_disconnect_email_sent_at = null;
                            $acc->save();
                        } elseif ($bridgeStatus === 'DISCONNECTED' || $bridgeStatus === 'QR_READY') {
                            if ($acc->status === 'CONNECTED') {
                                $acc->status = 'DISCONNECTED';
                                $acc->save();

                                $alertReq = new Request([
                                    'sessionId' => $acc->session_id ?: $acc->id,
                                    'reason' => 'Perangkat WA Brand terputus dari HP (Deteksi Otomatis Langsung)',
                                    'forceTest' => false
                                ]);
                                (new WebhookController())->handleDisconnectAlert($alertReq);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore transient bridge timeouts without forcibly wiping connected state
                }
            }
        }

        // Auto-sync User CS sessions
        $allUsersWithSession = User::whereNotNull('session_id')->get();
        foreach ($allUsersWithSession as $u) {
            try {
                $res = \Illuminate\Support\Facades\Http::timeout(2)->get($bridgeUrl . '/api/qr?session=' . $u->session_id);
                if ($res->successful()) {
                    $data = $res->json();
                    $bridgeStatus = $data['sessionStatus'] ?? null;

                    if ($bridgeStatus === 'CONNECTED') {
                        $u->wa_status = 'CONNECTED';
                        if (!empty($data['phone'])) {
                            $u->wa_phone = preg_replace('/[^0-9]/', '', $data['phone']);
                        }
                        $u->save();
                    } elseif ($bridgeStatus === 'DISCONNECTED' || $bridgeStatus === 'QR_READY') {
                        if ($u->wa_status === 'CONNECTED') {
                            $u->wa_status = 'DISCONNECTED';
                            $u->save();
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore transient bridge timeouts
            }
        }

        // Auto-check for disconnected WA accounts & dispatch email alerts based on interval (10s / 30m)
        try {
            \Illuminate\Support\Facades\Artisan::call('wa:check-disconnects');
        } catch (\Throwable $e) {}

        $query = Lead::with(['waAccount', 'assignedUser', 'messages']);

        if ($filter === 'daily') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'monthly') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        } elseif ($filter === 'yearly') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        $accessibleBrands = $user->getAccessibleBrands();
        $accessibleBrandIds = $accessibleBrands->pluck('id')->toArray();

        $activeAccount = null;
        $csId = $request->query('cs_id', 'all');
        $temperatureFilter = $request->query('temperature', 'all');
        $followUpFilter = $request->query('follow_up', 'all');

        if ($accountId !== 'all') {
            if (in_array((int)$accountId, $accessibleBrandIds) || $user->isCeo()) {
                $query->where('wa_account_id', $accountId);
                if ($user->isSalesAdmin()) {
                    $query->where('assigned_user_id', $user->id);
                } elseif ($csId !== 'all' && is_numeric($csId)) {
                    $query->where('assigned_user_id', $csId);
                }
                $activeAccount = WaAccount::with(['pipelineStages.triggers', 'supervisors'])->find($accountId);
                if ($activeAccount) {
                    $activeAccount->ensureDefaultStages();
                }
            } else {
                // Fallback to first accessible brand if unauthorized
                if ($accessibleBrands->isNotEmpty()) {
                    $firstBrand = $accessibleBrands->first();
                    $accountId = $firstBrand->id;
                    $query->where('wa_account_id', $accountId);
                    $activeAccount = $firstBrand;
                }
            }
        } elseif ($accountId === 'all') {
            if (!$user->isCeo()) {
                $query->whereIn('wa_account_id', $accessibleBrandIds);
            }
            if ($user->isSalesAdmin()) {
                $query->where('assigned_user_id', $user->id);
            } elseif ($csId !== 'all' && is_numeric($csId)) {
                $query->where('assigned_user_id', $csId);
            }
        }

        // WA Accounts available for current user's switcher
        if ($user->isCeo()) {
            $waAccounts = WaAccount::with(['leads', 'pipelineStages.triggers', 'supervisors'])->where('approval_status', 'APPROVED')->get();
            foreach ($waAccounts as $acc) {
                $acc->ensureDefaultStages();
            }
        } else {
            $waAccounts = $accessibleBrands;
            $waAccounts->load(['leads', 'pipelineStages.triggers', 'supervisors']);
        }

        // CS Team members for Supervisor / CEO selection cards
        $csTeam = collect();
        if ($activeAccount) {
            $csTeam = User::where('wa_account_id', $activeAccount->id)
                          ->where('role', 'SALES_ADMIN')
                          ->get();

            // Auto-heal: If brand has CS admin(s) and any leads are unassigned, bind them to the brand's CS
            if ($csTeam->isNotEmpty()) {
                $primaryCs = $csTeam->first();
                Lead::where('wa_account_id', $activeAccount->id)
                    ->whereNull('assigned_user_id')
                    ->update(['assigned_user_id' => $primaryCs->id]);
            }

            foreach ($csTeam as $cs) {
                $cs->leads_count = Lead::where('wa_account_id', $activeAccount->id)
                                       ->where('assigned_user_id', $cs->id)
                                       ->count();
            }
        }

        // Re-evaluate query if user is SALES_ADMIN to include newly bound leads
        if ($user->isSalesAdmin()) {
            $query->where('assigned_user_id', $user->id);
        }

        $allLeads = $query->latest()->get();

        // Calculate Temperature & Follow-Up Metrics
        $coldCount = 0;
        $coolCount = 0;
        $warmCount = 0;
        $veryWarmCount = 0;
        $hotCount = 0;
        $deadCount = 0;

        $fuDoneTodayCount = 0;
        $fuDueTodayCount = 0;
        $fuOverdueCount = 0;

        foreach ($allLeads as $l) {
            $t = $l->temperature['key'] ?? 'cold';
            if ($t === 'cold') $coldCount++;
            elseif ($t === 'cool') $coolCount++;
            elseif ($t === 'warm') $warmCount++;
            elseif ($t === 'very_warm') $veryWarmCount++;
            elseif ($t === 'hot') $hotCount++;
            elseif ($t === 'dead') $deadCount++;

            $fu = $l->follow_up_data;
            if ($fu['status'] === 'FOLLOWED_UP_TODAY') $fuDoneTodayCount++;
            elseif ($fu['status'] === 'DUE_TODAY') $fuDueTodayCount++;
            elseif ($fu['status'] === 'OVERDUE') $fuOverdueCount++;
        }

        // Apply Temperature Filter if selected
        $filteredLeads = $allLeads;
        if (in_array($temperatureFilter, ['cold', 'cool', 'warm', 'very_warm', 'hot', 'dead'])) {
            $filteredLeads = $filteredLeads->filter(fn($l) => ($l->temperature['key'] ?? '') === $temperatureFilter);
        }

        // Apply Follow-Up Filter if selected
        if ($followUpFilter === 'done_today') {
            $filteredLeads = $filteredLeads->filter(fn($l) => $l->follow_up_data['status'] === 'FOLLOWED_UP_TODAY');
        } elseif ($followUpFilter === 'due_today') {
            $filteredLeads = $filteredLeads->filter(fn($l) => $l->follow_up_data['status'] === 'DUE_TODAY');
        } elseif ($followUpFilter === 'overdue') {
            $filteredLeads = $filteredLeads->filter(fn($l) => $l->follow_up_data['status'] === 'OVERDUE');
        }

        $leads = $filteredLeads->values();
        $totalLeads = $allLeads->count();

        // Determine pipeline stages for current view
        if ($activeAccount) {
            $stages = $activeAccount->pipelineStages;
        } elseif (($user->isCeo() || $user->isSupervisor()) && $accountId === 'all') {
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
            'totalLeads', 'csTeam', 'csId', 'coldCount', 'coolCount', 'warmCount', 'veryWarmCount', 'hotCount', 'deadCount', 'temperatureFilter',
            'followUpFilter', 'fuDoneTodayCount', 'fuDueTodayCount', 'fuOverdueCount'
        ));
    });

    Route::post('/api/leads/{id}/stage', function (Request $request, $id) {
        $lead = Lead::findOrFail($id);
        $stage = $request->input('stage');
        if ($stage) {
            $lead->stage = $stage;
            $lead->save();
        }
        return response()->json([
            'status' => 'success',
            'message' => "Stage lead '{$lead->name}' berhasil diubah ke '{$lead->stage}'.",
            'lead' => $lead
        ]);
    });

    Route::get('/leads/{id}/detail', function ($id) {
        $lead = Lead::with(['messages' => function($q) {
            $q->orderBy('created_at', 'asc');
        }, 'waAccount', 'assignedUser'])->findOrFail($id);

        $csList = User::where('wa_account_id', $lead->wa_account_id)->where('role', 'SALES_ADMIN')->get(['id', 'name', 'email']);
        $brandStages = PipelineStage::where('wa_account_id', $lead->wa_account_id)->orderBy('order', 'asc')->get(['id', 'name']);

        return response()->json([
            'id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'stage' => $lead->stage,
            'priority' => $lead->priority,
            'notes' => $lead->notes,
            'wa_account' => $lead->waAccount,
            'assigned_user_id' => $lead->assigned_user_id,
            'assigned_user' => $lead->assignedUser,
            'cs_list' => $csList,
            'stages' => $brandStages,
            'messages' => $lead->messages,
            'follow_up' => $lead->follow_up_data,
        ]);
    });

    Route::post('/leads/{id}/record-fu', function (Request $request, $id) {
        $lead = Lead::findOrFail($id);
        $user = Auth::user();
        $customNote = $request->input('note', 'Follow-up manual dicatat oleh CS.');

        LeadMessage::create([
            'lead_id' => $lead->id,
            'sender' => $user->wa_phone ?: $user->name,
            'message' => '📌 [Catatan Follow-Up]: ' . $customNote,
            'is_from_me' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Follow-up berhasil dicatat!',
            'follow_up' => $lead->fresh()->follow_up_data,
        ]);
    });

    Route::post('/leads/{id}/update', function (Request $request, $id) {
        $lead = Lead::findOrFail($id);

        if ($request->has('name') && $request->filled('name')) {
            $lead->name = $request->input('name');
        }
        if ($request->has('phone') && $request->filled('phone')) {
            $lead->phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        }
        if ($request->has('notes')) {
            $lead->notes = $request->input('notes');
        }
        if ($request->has('priority')) {
            $lead->priority = (int) $request->input('priority');
        }
        if ($request->has('stage') && $request->filled('stage')) {
            $lead->stage = $request->input('stage');
        }
        if ($request->has('assigned_user_id')) {
            $lead->assigned_user_id = $request->input('assigned_user_id') ?: null;
        }

        $lead->save();
        if ($request->wantsJson() || $request->isJson()) {
            return response()->json(['status' => 'success', 'message' => 'Lead berhasil diperbarui.', 'lead' => $lead]);
        }
        return redirect()->back()->with('success', 'Data lead berhasil diperbarui!');
    });

    Route::post('/leads/{id}/delete', function ($id) {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        return response()->json(['status' => 'success', 'message' => 'Lead berhasil dihapus.']);
    });

    // Analytics Chart API
    Route::get('/api/analytics/chart-data', [AnalyticsController::class, 'getChartData']);

    // WA Accounts Routes (Accessible by CEO & Supervisor)
    Route::get('/wa-accounts', function () {
        $user = Auth::user();

        if (!$user->session_id) {
            $user->session_id = 'session_user_' . $user->id;
            $user->save();
        }

        if ($user->isCeo()) {
            $accounts = WaAccount::with(['pipelineStages.triggers', 'csTeam'])->where('approval_status', 'APPROVED')->get();
        } else {
            $accounts = $user->getAccessibleBrands();
            $accounts->load(['pipelineStages.triggers', 'csTeam']);
        }

        return response()->json([
            'accounts' => $accounts,
            'currentUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'session_id' => $user->session_id,
                'wa_status' => $user->wa_status ?? 'DISCONNECTED',
                'wa_phone' => $user->wa_phone,
                'wa_account_id' => $user->wa_account_id,
                'brand_name' => $user->waAccount->name ?? 'Default Brand',
            ],
            'isCeo' => $user->isCeo(),
            'isSupervisor' => $user->isSupervisor(),
        ]);
    });

    Route::post('/wa-accounts', function (Request $request) {
        $user = Auth::user();
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

        // If Supervisor creates brand, automatically bind supervisor to this brand!
        if ($user->isSupervisor()) {
            $user->supervisedBrands()->syncWithoutDetaching([$account->id]);
            if (!$user->wa_account_id) {
                $user->wa_account_id = $account->id;
                $user->save();
            }
        }

        return response()->json(['status' => 'success', 'account' => $account]);
    });

    Route::post('/wa-accounts/{id}/update', function (Request $request, $id) {
        $user = Auth::user();
        if (!$user->isCeo() && !$user->canAccessBrand($id)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

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
        if ($request->has('disconnect_alert_emails')) {
            $account->disconnect_alert_emails = $request->input('disconnect_alert_emails');
        }
        $account->save();

        return response()->json(['status' => 'success', 'message' => 'Data Brand Berhasil Diperbarui!', 'account' => $account]);
    });

    Route::post('/wa-accounts/{id}/delete', function ($id) {
        $user = Auth::user();
        if (!$user->isCeo() && !$user->canAccessBrand($id)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

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
        $waAccount = WaAccount::find($id) ?: WaAccount::first();
        if (!$waAccount) {
            return response()->json(['error' => 'No active brand found'], 404);
        }
        $waAccount->disconnect_email_enabled = (bool) $request->input('enabled');
        $waAccount->disconnect_email_interval = (int) $request->input('interval');
        $waAccount->save();

        $modeText = ($waAccount->disconnect_email_interval == 10) ? '10 Detik (Mode Testing)' : '30 Menit (Mode Production)';

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan Email Disconnect Berhasil Diperbarui ke ' . $modeText . '!',
            'account' => $waAccount
        ]);
    });

    Route::post('/wa-accounts/{id}/test-disconnect-email', function (Request $request, $id) {
        $waAccount = WaAccount::find($id) ?: WaAccount::first();
        if (!$waAccount) {
            return response()->json(['error' => 'No active brand found'], 404);
        }
        
        $webhookCtrl = new \App\Http\Controllers\WebhookController();
        $testRequest = new Request([
            'sessionId' => $waAccount->session_id ?: $waAccount->id,
            'reason' => 'Uji Coba Pengiriman Notifikasi Email Disconnect (Tombol Tes Admin)',
            'forceTest' => true
        ]);

        return $webhookCtrl->handleDisconnectAlert($testRequest);
    });

    // WA Bridge Proxy Routes (Bypasses HTTPS Mixed Content & Port 3001 Firewall blocks)
    Route::post('/admin/wa-proxy/connect', function (Request $request) {
        $bridgeUrl = rtrim(env('WA_BRIDGE_URL', 'http://127.0.0.1:3001'), '/');
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)->post($bridgeUrl . '/api/connect', [
                'session' => $request->input('session', 'default')
            ]);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'WA Bridge offline di server: ' . $e->getMessage()], 503);
        }
    });

    Route::get('/admin/wa-proxy/qr', function (Request $request) {
        $bridgeUrl = rtrim(env('WA_BRIDGE_URL', 'http://127.0.0.1:3001'), '/');
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)->get($bridgeUrl . '/api/qr?session=' . $request->query('session', 'default'));
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'WA Bridge offline di server: ' . $e->getMessage()], 503);
        }
    });

    // Supervisor CS Team Management Routes
    Route::get('/brand/cs-team', [UserController::class, 'getCsTeam']);
    Route::post('/brand/cs-team', [UserController::class, 'storeCsMember']);
    Route::post('/brand/cs-team/{id}/update', [UserController::class, 'updateCsMember']);
    Route::delete('/brand/cs-team/{id}', [UserController::class, 'destroyCsMember']);

    // User Profile & Password Update Route (Owner, Supervisor, CS)
    Route::post('/user/profile/update', [UserController::class, 'updateProfile']);

    // CEO & Supervisor SMTP & Alert Settings Routes
    Route::get('/admin/smtp-settings', function () {
        $user = Auth::user();
        if (!$user || (!$user->isCeo() && !$user->isSupervisor())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $settings = \App\Models\SmtpSetting::firstOrCreate([], [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@difitech.id',
            'mail_from_name' => 'Difitech CRM Alert',
            'disconnect_alert_emails' => 'ashabil@difitech.id, siswandi@difitech.co.id',
        ]);
        return response()->json($settings);
    });

    Route::post('/admin/smtp-settings', function (Request $request) {
        $user = Auth::user();
        if (!$user || (!$user->isCeo() && !$user->isSupervisor())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $settings = \App\Models\SmtpSetting::firstOrCreate([]);
        $settings->update([
            'mail_mailer' => $request->input('mail_mailer', 'smtp'),
            'mail_host' => $request->input('mail_host'),
            'mail_port' => (int) $request->input('mail_port', 587),
            'mail_username' => $request->input('mail_username'),
            'mail_password' => $request->input('mail_password'),
            'mail_encryption' => $request->input('mail_encryption'),
            'mail_from_address' => $request->input('mail_from_address', 'no-reply@difitech.id'),
            'mail_from_name' => $request->input('mail_from_name', 'Difitech CRM Alert'),
            'disconnect_alert_emails' => $request->input('disconnect_alert_emails'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✅ Pengaturan Notifikasi & Server SMTP Email Berhasil Disimpan!',
            'settings' => $settings
        ]);
    });

    Route::post('/admin/smtp-settings/test', function (Request $request) {
        $user = Auth::user();
        if (!$user || (!$user->isCeo() && !$user->isSupervisor())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $setting = \App\Models\SmtpSetting::applyConfig();
        $targetEmail = Auth::user()->email;

        try {
            \Illuminate\Support\Facades\Mail::html("
                <div style='font-family: sans-serif; padding: 20px; background: #f8fafc; border-radius: 12px;'>
                    <h2 style='color: #059669;'>✅ Uji Coba Server SMTP Berhasil!</h2>
                    <p>Halo <strong>" . Auth::user()->name . "</strong>,</p>
                    <p>Pesan ini mengonfirmasi bahwa konfigurasi server SMTP <strong>" . ($setting->mail_host ?? 'SMTP') . "</strong> pada CRM MVP Difitech berfungsi dengan lancar!</p>
                    <p style='font-size: 12px; color: #64748b;'>Waktu pengujian: " . now()->format('d M Y H:i:s') . " WIB</p>
                </div>
            ", function ($message) use ($targetEmail, $setting) {
                $message->to($targetEmail)
                    ->subject("🧪 Uji Coba Koneksi Server SMTP - Difitech CRM");
            });

            return response()->json([
                'status' => 'success',
                'message' => "✅ Uji coba email SMTP berhasil dikirimkan ke {$targetEmail}!"
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => "❌ Gagal mengirimkan email SMTP: " . $e->getMessage()
            ], 500);
        }
    });

    // AI vs Real Leads Comparison Routes
    Route::get('/ai-comparison', [LeadComparisonController::class, 'index'])->name('ai-comparison.index');
    Route::get('/api/ai-comparison', [LeadComparisonController::class, 'apiData'])->name('ai-comparison.api');
    Route::post('/ai-comparison/snapshot', [LeadComparisonController::class, 'storeSnapshot'])->name('ai-comparison.snapshot');
    Route::post('/ai-comparison/scan-all', [LeadComparisonController::class, 'scanAllLeads'])->name('ai-comparison.scan-all');

    // Multi-Brand Supervisor Management Routes (CEO Access)
    Route::get('/admin/supervisors', function () {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $supervisors = User::where('role', 'SUPERVISOR')->with('supervisedBrands')->get();
        $brands = WaAccount::where('approval_status', 'APPROVED')->get();
        return response()->json([
            'supervisors' => $supervisors,
            'brands' => $brands
        ]);
    });

    Route::post('/admin/supervisors/{id}/brands', function (Request $request, $id) {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $supervisor = User::where('role', 'SUPERVISOR')->findOrFail($id);
        $brandIds = $request->input('brand_ids', []);
        $supervisor->supervisedBrands()->sync($brandIds);

        if (!empty($brandIds) && (!$supervisor->wa_account_id || !in_array($supervisor->wa_account_id, $brandIds))) {
            $supervisor->wa_account_id = $brandIds[0];
            $supervisor->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => "Akses brand untuk supervisor {$supervisor->name} berhasil diperbarui!",
            'supervisor' => $supervisor->load('supervisedBrands')
        ]);
    });

    Route::post('/admin/brands/{id}/supervisors', function (Request $request, $id) {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $account = WaAccount::findOrFail($id);
        $supervisorIds = $request->input('supervisor_ids', []);
        $account->supervisors()->sync($supervisorIds);

        return response()->json([
            'status' => 'success',
            'message' => "Supervisor untuk brand {$account->name} berhasil diperbarui!",
            'account' => $account->load('supervisors')
        ]);
    });
    Route::post('/ai-comparison/simulate', [LeadComparisonController::class, 'simulate'])->name('ai-comparison.simulate');
});

