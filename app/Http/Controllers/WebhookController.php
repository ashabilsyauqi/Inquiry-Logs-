<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\WaAccount;
use App\Models\LeadMessage;
use App\Models\StageTrigger;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleIncomingWA(Request $request)
    {
        Log::info('Incoming WA Payload: ' . json_encode($request->all()));

        $sender = $request->input('sender') ?? $request->input('from');
        $receiver = $request->input('receiver') ?? $request->input('to') ?? $request->input('sessionId') ?? 'system';
        $message = trim($request->input('message') ?? $request->input('text') ?? '');
        $senderNameInput = $request->input('senderName');
        $sessionId = $request->input('sessionId') ?? 'default';
        $isAdminCommand = $request->input('isAdminCommand') || str_starts_with($message, '/') || str_starts_with($message, '#');
        $isSelfChat = (bool)$request->input('isSelfChat');

        if (!$sender || $message === '') {
            return response()->json(['status' => 'error', 'message' => 'Missing sender or message'], 400);
        }

        $senderPhone = $this->sanitizePhone($sender);
        $receiverPhone = $this->sanitizePhone($receiver);

        // Find CS User if this session belongs to an individual CS Admin
        $csUser = User::where('session_id', $sessionId)->first();
        if (!$csUser && str_starts_with($sessionId, 'session_user_')) {
            $uId = (int)str_replace('session_user_', '', $sessionId);
            $csUser = User::find($uId);
        }

        if ($csUser) {
            $csUser->wa_status = 'CONNECTED';
            if ($receiverPhone && !$csUser->wa_phone) {
                $csUser->wa_phone = $receiverPhone;
            }
            $csUser->save();

            $assignedUserId = $csUser->id;
            $waAccount = $csUser->waAccount;
        } else {
            $waAccount = WaAccount::where('session_id', $sessionId)->first();
            if (!$waAccount && $receiverPhone) {
                $waAccount = WaAccount::where('phone', $receiverPhone)->first();
            }
            if (!$waAccount) {
                $waAccount = WaAccount::where('approval_status', 'APPROVED')->first();
            }

            if ($waAccount) {
                $brandCs = User::where('wa_account_id', $waAccount->id)->where('role', 'SALES_ADMIN')->first();
                $assignedUserId = $brandCs ? $brandCs->id : null;
            } else {
                $assignedUserId = null;
            }
        }

        if (!$waAccount) {
            $waAccount = WaAccount::create([
                'name' => 'WA Brand ' . ($receiverPhone ?: $sessionId),
                'phone' => $receiverPhone ?: null,
                'session_id' => $sessionId,
                'status' => 'CONNECTED'
            ]);
            $waAccount->ensureDefaultStages();
        } else {
            $waAccount->ensureDefaultStages();
            if ($receiverPhone && !$waAccount->phone && !$csUser) {
                $waAccount->phone = $receiverPhone;
                $waAccount->status = 'CONNECTED';
                $waAccount->save();
            }
        }

        $myNumber = ($csUser && $csUser->wa_phone) ? $csUser->wa_phone : ($waAccount->phone ?: $receiverPhone);
        $isFromMe = $request->input('isFromMe') || ($senderPhone === $myNumber);

        // 1. DEDICATED SELF-CHAT ADMIN CONTROL PANEL HASHTAG COMMANDS (#deal 08123456789)
        if (($isSelfChat || str_starts_with($message, '#')) && $isFromMe) {
            // Extract phone number inside message if provided e.g. "#deal 08123456789"
            preg_match('/(?:08|628|\+628)[0-9]{8,12}/', $message, $matches);
            $targetPhoneRaw = $matches[0] ?? null;

            if ($targetPhoneRaw) {
                $targetLeadPhone = $this->sanitizePhone($targetPhoneRaw);
                $commandPart = trim(str_replace($targetPhoneRaw, '', $message));
            } else {
                $targetLeadPhone = null;
                $commandPart = $message;
            }

            $cleanCmd = strtolower(ltrim(trim($commandPart), '#')); // e.g. "deal", "meeting", "stage 1"
            $matchedStage = null;

            if (str_starts_with($cleanCmd, 'stage ')) {
                $stageArg = trim(substr($cleanCmd, 6));
                if (is_numeric($stageArg)) {
                    $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->where('order', (int)$stageArg)->first();
                } else {
                    $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($stageArg) . '%'])->first();
                }
            } else {
                $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->whereRaw('LOWER(name) LIKE ?', ['%' . $cleanCmd . '%'])->first();
                if (!$matchedStage) {
                    $trigger = StageTrigger::where('wa_account_id', $waAccount->id)->whereRaw('LOWER(keyword) = ?', [$cleanCmd])->first();
                    if ($trigger) {
                        $matchedStage = $trigger->pipelineStage;
                    }
                }
            }

            if ($matchedStage && $targetLeadPhone) {
                $targetLead = Lead::where('phone', $targetLeadPhone)->first();
                if (!$targetLead) {
                    $displayName = $this->formatDisplayPhone($targetLeadPhone);
                    $targetLead = Lead::create([
                        'wa_account_id' => $waAccount->id,
                        'name' => $displayName,
                        'phone' => $targetLeadPhone,
                        'stage' => $matchedStage->name
                    ]);
                } else {
                    $targetLead->stage = $matchedStage->name;
                    $targetLead->save();
                }

                $replyText = "✅ *STATUS CRM TERHUBUNG & TERUPDATE!*\n\n" .
                             "👤 *Lead*: {$targetLead->name} ({$targetLead->phone})\n" .
                             "📌 *Stage Baru*: *{$matchedStage->name}*\n" .
                             "🛡️ *Pesan Ke Customer*: 0% (Tidak ada pesan terkirim ke customer).";

                Log::info("⚡ SELF CHAT CONTROL COMMAND EXECUTED: Lead {$targetLead->phone} moved to {$matchedStage->name}");

                return response()->json([
                    'status' => 'success',
                    'message' => 'Self chat control command executed',
                    'replyMessage' => $replyText
                ]);
            }
        }

        $leadPhone = $isFromMe ? $receiverPhone : $senderPhone;
        $lead = $waAccount 
            ? Lead::where('wa_account_id', $waAccount->id)->where('phone', $leadPhone)->first()
            : Lead::where('phone', $leadPhone)->first();

        // 2. HANDLE INTERNAL ADMIN WA SLASH & OPERATOR COMMANDS (e.g. /1, /2, .1, .2, /deal, /meeting)
        if ($isFromMe && $isAdminCommand) {
            $commandStr = strtolower(ltrim(ltrim($message, '/'), '.')); // e.g. "1", "2", "deal", "meeting"
            $matchedStage = null;

            if (is_numeric($commandStr)) {
                $orderNum = (int)$commandStr;
                $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->where('order', $orderNum)->first();
                if (!$matchedStage) {
                    $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->orderBy('order', 'asc')->skip($orderNum - 1)->first();
                }
            } elseif (str_starts_with($commandStr, 'stage ')) {
                $stageArg = trim(substr($commandStr, 6)); // e.g. "2" or "pitching"
                if (is_numeric($stageArg)) {
                    $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->where('order', (int)$stageArg)->first();
                } else {
                    $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($stageArg) . '%'])->first();
                }
            } else {
                // Search direct stage name match
                $matchedStage = PipelineStage::where('wa_account_id', $waAccount->id)->whereRaw('LOWER(name) LIKE ?', ['%' . $commandStr . '%'])->first();

                // Fallback: Search keyword trigger match
                if (!$matchedStage) {
                    $trigger = StageTrigger::where('wa_account_id', $waAccount->id)->whereRaw('LOWER(keyword) = ?', [$commandStr])->first();
                    if ($trigger) {
                        $matchedStage = $trigger->pipelineStage;
                    }
                }
            }

            if ($matchedStage) {
                if (!$lead) {
                    $displayName = $this->formatDisplayPhone($leadPhone);
                    $lead = Lead::create([
                        'wa_account_id' => $waAccount->id,
                        'name'  => $displayName,
                        'phone' => $leadPhone,
                        'stage' => $matchedStage->name
                    ]);
                } else {
                    $lead->stage = $matchedStage->name;
                    $lead->save();
                }
                Log::info("⚡ ADMIN WA SLASH COMMAND EXECUTED: Command '{$message}' moved Lead '{$lead->name}' to stage '{$matchedStage->name}'");
                return response()->json(['status' => 'success', 'message' => "Admin command executed: Lead moved to {$matchedStage->name}"]);
            }
        }

        // 3. BI-DIRECTIONAL & NON-LINEAR KEYWORD STAGE TRIGGERS (Skip or Jump Back Stage)
        $lowerMessage = strtolower($message);
        $activeTriggers = StageTrigger::where('wa_account_id', $waAccount->id)->with('pipelineStage')->get();
        if ($activeTriggers->isEmpty()) {
            $activeTriggers = StageTrigger::whereNull('wa_account_id')->with('pipelineStage')->get();
        }
        $matchedStageName = null;

        foreach ($activeTriggers as $trigger) {
            if ($trigger->pipelineStage && !empty($trigger->keyword) && str_contains($lowerMessage, strtolower($trigger->keyword))) {
                $matchedStageName = $trigger->pipelineStage->name;
                break;
            }
        }

        // Fallback: Direct stage name keyword matching (e.g. CS types "#deal", "deal", "/meeting", etc.)
        if (!$matchedStageName && $waAccount) {
            $stages = $waAccount->pipelineStages;
            foreach ($stages as $st) {
                $cleanStName = strtolower(trim($st->name));
                if (strlen($cleanStName) >= 3 && (str_contains($lowerMessage, '#' . $cleanStName) || str_contains($lowerMessage, '/' . $cleanStName) || str_contains($lowerMessage, $cleanStName))) {
                    $matchedStageName = $st->name;
                    break;
                }
            }
        }

        // Entry Stage Defaulting
        $entryStage = $waAccount->pipelineStages()->where('is_default', true)->first();
        if (!$entryStage) {
            $entryStage = $waAccount->pipelineStages()->first();
        }
        $defaultStageName = $entryStage ? $entryStage->name : 'Lead Masuk';

        if ($isFromMe) {
            if (!$lead) {
                $displayName = $this->formatDisplayPhone($leadPhone);
                $lead = Lead::create([
                    'wa_account_id' => $waAccount ? $waAccount->id : null,
                    'assigned_user_id' => $assignedUserId,
                    'name'  => $displayName,
                    'phone' => $leadPhone,
                    'stage' => $matchedStageName ?: $defaultStageName
                ]);
            } else {
                if ($assignedUserId && !$lead->assigned_user_id) {
                    $lead->assigned_user_id = $assignedUserId;
                }
                if ($matchedStageName) {
                    $lead->stage = $matchedStageName;
                }
                $lead->save();
            }
        } else {
            // Incoming lead from customer
            $displayName = $senderNameInput ?: $this->formatDisplayPhone($leadPhone);

            if (!$lead) {
                $lead = Lead::create([
                    'wa_account_id' => $waAccount ? $waAccount->id : null,
                    'assigned_user_id' => $assignedUserId,
                    'name'  => $displayName,
                    'phone' => $leadPhone,
                    'stage' => $matchedStageName ?: $defaultStageName
                ]);
            } else {
                if (!$lead->wa_account_id && $waAccount) {
                    $lead->wa_account_id = $waAccount->id;
                }
                if ($assignedUserId && !$lead->assigned_user_id) {
                    $lead->assigned_user_id = $assignedUserId;
                }
                if ($senderNameInput && (str_contains($lead->name, 'Pelanggan') || str_contains($lead->name, 'Lead') || preg_match('/^[0-9]+$/', $lead->name))) {
                    $lead->name = $senderNameInput;
                }
                if ($matchedStageName) {
                    $lead->stage = $matchedStageName;
                }
                $lead->save();
            }
        }

        // Store chat log in LeadMessage if not internal admin command
        if ($lead && !$isAdminCommand && !$isSelfChat) {
            LeadMessage::create([
                'lead_id' => $lead->id,
                'sender' => $senderPhone,
                'message' => $message,
                'is_from_me' => $isFromMe,
            ]);

            // Analyze conversation context with AI immediately
            try {
                if ($matchedStageName) {
                    $lead->ai_concluded_stage = $matchedStageName;
                    $lead->ai_suggested_stage = null;
                    $lead->ai_suggested_keyword = null;
                    $lead->ai_suggestion_reason = null;
                    $lead->ai_suggested_at = null;
                    $lead->save();
                } else {
                    \App\Jobs\AnalyzeLeadStageWithAiJob::dispatchSync($lead->id);
                }
            } catch (\Exception $e) {
                Log::error("Failed running AnalyzeLeadStageWithAiJob: " . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully'
        ]);
    }

    // Webhook Endpoint for Disconnection Alerts from wa-bridge
    public function handleDisconnectAlert(Request $request)
    {
        $sessionId = $request->input('sessionId') ?? 'default';
        $reason = $request->input('reason') ?? 'Perangkat terputus atau koneksi WhatsApp hilang';
        $forceTest = (bool)$request->input('forceTest');

        $waAccount = WaAccount::where('session_id', $sessionId)->first();
        if (!$waAccount && is_numeric($sessionId)) {
            $waAccount = WaAccount::find($sessionId);
        }

        if ($waAccount) {
            $isFirstDetection = ($waAccount->status !== 'DISCONNECTED' || !$waAccount->last_disconnect_email_sent_at);

            $waAccount->status = 'DISCONNECTED';
            $waAccount->save();

            // Check if email alert is enabled for this WA Account
            if (!$waAccount->disconnect_email_enabled && !$forceTest) {
                Log::info("ℹ️ Disconnect email alert skipped for Account {$waAccount->name} (Feature Disabled in Settings)");
                return response()->json(['status' => 'success', 'message' => 'Account marked DISCONNECTED (Email alert disabled)']);
            }

            // Interval throttling (e.g. 10s for testing, 1800s for 30m prod)
            // FIRST DETECTION ALWAYS BYPASSES THROTTLING (Sends Email Instantly!)
            $intervalSeconds = $waAccount->disconnect_email_interval ?: 10;
            $lastSent = $waAccount->last_disconnect_email_sent_at ? \Carbon\Carbon::parse($waAccount->last_disconnect_email_sent_at) : null;

            if (!$isFirstDetection && $lastSent && !$forceTest && $lastSent->diffInSeconds(now()) < $intervalSeconds) {
                $secondsLeft = $intervalSeconds - $lastSent->diffInSeconds(now());
                Log::info("⏳ Disconnect email alert throttled for Account {$waAccount->name}. Next email allowed in {$secondsLeft}s");
                return response()->json([
                    'status' => 'success',
                    'message' => "Account marked DISCONNECTED (Email throttled, next alert in {$secondsLeft}s)"
                ]);
            }

            // Send Email Notification Alert to Admin CS Team & Brand Supervisor (To:) and CC to CEO Users
            $supervisor = $waAccount->supervisor;
            $supervisorEmail = $supervisor ? $supervisor->email : null;
            $supervisorName = $supervisor ? $supervisor->name : 'Supervisor Brand';

            // Get Admin CS Emails assigned to this brand
            $csEmails = $waAccount->csTeam ? $waAccount->csTeam->pluck('email')->filter()->toArray() : [];

            // Get CEO Emails for CC
            $ceoEmails = User::where('role', 'CEO')->pluck('email')->toArray();
            if (empty($ceoEmails)) {
                $ceoEmails = ['ashabil@difitech.id'];
            }

            // Get configured Disconnect Alert Emails from SmtpSetting (Global) & WaAccount (Brand specific)
            $customAlertEmails = [];
            $smtpSetting = \App\Models\SmtpSetting::first();
            if ($smtpSetting && !empty($smtpSetting->disconnect_alert_emails)) {
                $rawList = preg_split('/[\r\n,;]+/', $smtpSetting->disconnect_alert_emails);
                foreach ($rawList as $rawEmail) {
                    $clean = trim($rawEmail);
                    if (filter_var($clean, FILTER_VALIDATE_EMAIL)) {
                        $customAlertEmails[] = $clean;
                    }
                }
            }
            if (!empty($waAccount->disconnect_alert_emails)) {
                $rawList = preg_split('/[\r\n,;]+/', $waAccount->disconnect_alert_emails);
                foreach ($rawList as $rawEmail) {
                    $clean = trim($rawEmail);
                    if (filter_var($clean, FILTER_VALIDATE_EMAIL)) {
                        $customAlertEmails[] = $clean;
                    }
                }
            }

            // Combine primary recipients: Admin CS Team + Supervisor + Custom Notification Emails
            $toRecipients = array_values(array_unique(array_filter(array_merge($csEmails, [$supervisorEmail], $customAlertEmails))));
            if (empty($toRecipients)) {
                $toRecipients = $ceoEmails;
            }

            $primaryToText = implode(', ', $toRecipients);
            $accountName = $waAccount->name;
            $phone = $waAccount->phone ?: 'Belum Terhubung';
            $intervalText = ($intervalSeconds < 60) ? "{$intervalSeconds} Detik (Testing Mode)" : ($intervalSeconds / 60) . " Menit";

            $subject = "⚠️ PERINGATAN DARURAT: WhatsApp CS Brand {$accountName} Terputus!";
            
            $htmlBody = "
            <html>
            <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f8fafc; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #dc2626; margin: 0;'>⚠️ PERINGATAN WA BRAND TERPUTUS</h2>
                        <p style='color: #64748b; font-size: 13px;'>Sistem Deteksi Otomatis CRM MVP</p>
                    </div>
                    <p>Halo <strong>Tim CS & Supervisor Brand {$accountName}</strong>,</p>
                    <p>Perangkat WhatsApp untuk brand <strong>{$accountName}</strong> (No: <code>{$phone}</code>) saat ini dalam status <span style='color: #dc2626; font-weight: bold;'>TERPUTUS (DISCONNECTED)</span>.</p>
                    
                    <div style='background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 14px; margin: 15px 0; border-radius: 6px;'>
                        <strong>Detail Peringatan:</strong><br/>
                        • <strong>Brand / Device:</strong> {$accountName}<br/>
                        • <strong>Penerima Alert:</strong> {$primaryToText}<br/>
                        • <strong>CC CEO:</strong> " . implode(', ', $ceoEmails) . "<br/>
                        • <strong>Alasan:</strong> {$reason}<br/>
                        • <strong>Waktu Kejadian:</strong> " . now()->format('d M Y - H:i:s') . " WIB<br/>
                        • <strong>Mode Interval:</strong> {$intervalText}
                    </div>

                    <p>Silakan segera lakukan scan ulang QR code di dashboard CRM Anda agar pesan dari customer tetap dapat terlayani:</p>

                    <p style='text-align: center; margin: 25px 0;'>
                        <a href='http://127.0.0.1:8000/dashboard' style='background-color: #059669; color: white; padding: 14px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);'>
                            📲 Scan QR Code Ulang Sekarang
                        </a>
                    </p>
                    <hr style='border: none; border-top: 1px solid #e2e8f0; margin-top: 30px;' />
                    <p style='font-size: 11px; color: #94a3b8; text-align: center;'>Pesan notifikasi darurat ini dikirimkan otomatis ke Admin CS, Supervisor Brand & CC ke CEO ({$ceoEmails[0]}).</p>
                </div>
            </body>
            </html>
            ";

            // Apply dynamic SMTP settings if configured
            \App\Models\SmtpSetting::applyConfig();

            try {
                \Illuminate\Support\Facades\Mail::html($htmlBody, function ($message) use ($toRecipients, $ceoEmails, $subject) {
                    $message->to($toRecipients)
                        ->subject($subject);
                    if (!empty($ceoEmails)) {
                        $message->cc($ceoEmails);
                    }
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("❌ SMTP Mail Exception: " . $e->getMessage());
                // Fallback to PHP mail
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: CRM Alert <no-reply@difitech.id>\r\n";
                $headers .= "Reply-To: no-reply@difitech.id\r\n";
                if (!empty($ceoEmails)) {
                    $headers .= "Cc: " . implode(', ', $ceoEmails) . "\r\n";
                }
                @mail($primaryToText, $subject, $htmlBody, $headers);
            }

            $waAccount->last_disconnect_email_sent_at = now();
            $waAccount->save();

            Log::warning("⚠️ DISCONNECTION EMAIL ALERT DISPATCHED to Admin CS & Supervisor ({$primaryToText}) & CC CEO for Account {$accountName} ({$sessionId})");
        }

        return response()->json(['status' => 'success', 'message' => 'Disconnection alert processed & emails dispatched']);
    }

    private function sanitizePhone(string $phone): string
    {
        $phone = explode('@', $phone)[0];
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    private function formatDisplayPhone(string $phone): string
    {
        if (str_starts_with($phone, '62')) {
            return '+62 ' . substr($phone, 2, 3) . '-' . substr($phone, 5, 4) . '-' . substr($phone, 9);
        }
        if (strlen($phone) >= 14 && !str_starts_with($phone, '62')) {
            return 'Pelanggan WA (' . substr($phone, -4) . ')';
        }
        return '+' . $phone;
    }
}
