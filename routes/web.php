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

        // Sales Admin Isolation: Force account_id to assigned Brand WA account
        if (!$user->isCeo()) {
            $accountId = $user->wa_account_id;
        }

        // Instant 2-Way Status Sync: Auto-sync Brand & User sessions with wa-bridge server
        $allApprovedAccs = WaAccount::where('approval_status', 'APPROVED')->get();
        foreach ($allApprovedAccs as $acc) {
            if ($acc->session_id) {
                try {
                    $res = \Illuminate\Support\Facades\Http::timeout(1)->get('http://127.0.0.1:3001/api/qr?session=' . $acc->session_id);
                    if ($res->successful()) {
                        $data = $res->json();
                        $bridgeStatus = $data['sessionStatus'] ?? null;
                        $realStatus = ($bridgeStatus === 'CONNECTED') ? 'CONNECTED' : 'DISCONNECTED';

                        if ($acc->status !== $realStatus) {
                            $oldStatus = $acc->status;
                            $acc->status = $realStatus;
                            if ($realStatus === 'CONNECTED') {
                                if (!empty($data['phone'])) {
                                    $acc->phone = preg_replace('/[^0-9]/', '', $data['phone']);
                                }
                                $acc->last_disconnect_email_sent_at = null;
                            } else {
                                $acc->phone = null;
                            }
                            $acc->save();

                            if ($oldStatus === 'CONNECTED' && $realStatus === 'DISCONNECTED') {
                                $alertReq = new Request([
                                    'sessionId' => $acc->session_id ?: $acc->id,
                                    'reason' => 'Perangkat WA Brand terputus dari HP (Deteksi Otomatis Langsung)',
                                    'forceTest' => false
                                ]);
                                (new WebhookController())->handleDisconnectAlert($alertReq);
                            }
                        }
                    } else {
                        if ($acc->status !== 'DISCONNECTED') {
                            $acc->status = 'DISCONNECTED';
                            $acc->phone = null;
                            $acc->save();
                        }
                    }
                } catch (\Throwable $e) {
                    if ($acc->status !== 'DISCONNECTED') {
                        $acc->status = 'DISCONNECTED';
                        $acc->phone = null;
                        $acc->save();
                    }
                }
            }
        }

        // Auto-sync User CS sessions
        $allUsersWithSession = User::whereNotNull('session_id')->get();
        foreach ($allUsersWithSession as $u) {
            try {
                $res = \Illuminate\Support\Facades\Http::timeout(1)->get('http://127.0.0.1:3001/api/qr?session=' . $u->session_id);
                if ($res->successful()) {
                    $data = $res->json();
                    $bridgeStatus = $data['sessionStatus'] ?? null;
                    $realStatus = ($bridgeStatus === 'CONNECTED') ? 'CONNECTED' : 'DISCONNECTED';
                    if ($u->wa_status !== $realStatus) {
                        $u->wa_status = $realStatus;
                        if ($realStatus === 'CONNECTED' && !empty($data['phone'])) {
                            $u->wa_phone = preg_replace('/[^0-9]/', '', $data['phone']);
                        } elseif ($realStatus !== 'CONNECTED') {
                            $u->wa_phone = null;
                        }
                        $u->save();
                    }
                } else {
                    if ($u->wa_status !== 'DISCONNECTED') {
                        $u->wa_status = 'DISCONNECTED';
                        $u->wa_phone = null;
                        $u->save();
                    }
                }
            } catch (\Throwable $e) {
                if ($u->wa_status !== 'DISCONNECTED') {
                    $u->wa_status = 'DISCONNECTED';
                    $u->wa_phone = null;
                    $u->save();
                }
            }
        }

        // Auto-check for disconnected WA accounts & dispatch email alerts based on interval (10s / 30m)
        try {
            \Illuminate\Support\Facades\Artisan::call('wa:check-disconnects');
        } catch (\Throwable $e) {}

        $query = Lead::with(['waAccount', 'assignedUser']);

        if ($filter === 'daily') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'monthly') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        } elseif ($filter === 'yearly') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        $activeAccount = null;
        $csId = $request->query('cs_id', 'all');

        if ($accountId !== 'all') {
            $query->where('wa_account_id', $accountId);
            if ($user->role === 'SALES_ADMIN') {
                $query->where('assigned_user_id', $user->id);
            } elseif ($csId !== 'all' && is_numeric($csId)) {
                $query->where('assigned_user_id', $csId);
            }
            $activeAccount = WaAccount::with(['pipelineStages.triggers'])->find($accountId);
            if ($activeAccount) {
                $activeAccount->ensureDefaultStages();
            }
        } elseif ($user->role === 'SALES_ADMIN') {
            $query->where('assigned_user_id', $user->id);
        } elseif ($csId !== 'all' && is_numeric($csId)) {
            $query->where('assigned_user_id', $csId);
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
        if ($user->role === 'SALES_ADMIN') {
            $query->where('assigned_user_id', $user->id);
            $leads = $query->latest()->get();
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
            'totalLeads', 'csTeam', 'csId'
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
        if ($request->wantsJson() || $request->isJson()) {
            return response()->json(['status' => 'success', 'message' => 'Lead berhasil diperbarui.', 'lead' => $lead]);
        }
        return redirect()->back();
    });

    // Analytics Chart API
    Route::get('/api/analytics/chart-data', [AnalyticsController::class, 'getChartData']);

    // WA Accounts Routes
    Route::get('/wa-accounts', function () {
        $user = Auth::user();

        if (!$user->session_id) {
            $user->session_id = 'session_user_' . $user->id;
            $user->save();
        }

        if ($user->isCeo()) {
            $accounts = WaAccount::with(['pipelineStages.triggers', 'csTeam'])->where('approval_status', 'APPROVED')->get();
        } elseif ($user->role === 'SUPERVISOR') {
            $accounts = WaAccount::with(['pipelineStages.triggers', 'csTeam'])->where('id', $user->wa_account_id)->get();
        } else {
            $accounts = WaAccount::with(['pipelineStages.triggers', 'csTeam'])->where('id', $user->wa_account_id)->get();
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
            'isSupervisor' => ($user->role === 'SUPERVISOR'),
        ]);
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
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)->post('http://127.0.0.1:3001/api/connect', [
                'session' => $request->input('session', 'default')
            ]);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'WA Bridge offline di cPanel (Perlu di-start via Node.js App / Terminal)'], 503);
        }
    });

    Route::get('/admin/wa-proxy/qr', function (Request $request) {
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)->get('http://127.0.0.1:3001/api/qr?session=' . $request->query('session', 'default'));
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'WA Bridge offline di cPanel (Perlu di-start via Node.js App / Terminal)'], 503);
        }
    });

    // Supervisor CS Team Management Routes
    Route::get('/brand/cs-team', [UserController::class, 'getCsTeam']);
    Route::post('/brand/cs-team', [UserController::class, 'storeCsMember']);
    Route::delete('/brand/cs-team/{id}', [UserController::class, 'destroyCsMember']);

    // CEO Dynamic SMTP Settings Routes
    Route::get('/admin/smtp-settings', function () {
        if (!Auth::user() || !Auth::user()->isCeo()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $settings = \App\Models\SmtpSetting::firstOrCreate([], [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@difitech.id',
            'mail_from_name' => 'Difitech CRM Alert',
        ]);
        return response()->json($settings);
    });

    Route::post('/admin/smtp-settings', function (Request $request) {
        if (!Auth::user() || !Auth::user()->isCeo()) {
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
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✅ Pengaturan Server SMTP Email Berhasil Disimpan!',
            'settings' => $settings
        ]);
    });

    Route::post('/admin/smtp-settings/test', function (Request $request) {
        if (!Auth::user() || !Auth::user()->isCeo()) {
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
    Route::post('/ai-comparison/simulate', [LeadComparisonController::class, 'simulate'])->name('ai-comparison.simulate');
});

