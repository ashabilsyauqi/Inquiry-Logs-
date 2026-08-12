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
        $receiver = $request->input('receiver') ?? $request->input('to');
        $message = trim($request->input('message') ?? $request->input('text'));
        $senderNameInput = $request->input('senderName');
        $sessionId = $request->input('sessionId') ?? 'default';
        $isAdminCommand = $request->input('isAdminCommand') || str_starts_with($message, '/') || str_starts_with($message, '#');
        $isSelfChat = (bool)$request->input('isSelfChat');

        if (!$sender || !$receiver || !$message) {
            return response()->json(['status' => 'error', 'message' => 'Missing sender, receiver, or message'], 400);
        }

        $senderPhone = $this->sanitizePhone($sender);
        $receiverPhone = $this->sanitizePhone($receiver);

        // Find or create target WaAccount
        $waAccount = WaAccount::where('session_id', $sessionId)->first();
        if (!$waAccount && $receiverPhone) {
            $waAccount = WaAccount::where('phone', $receiverPhone)->first();
        }

        if (!$waAccount) {
            $waAccount = WaAccount::create([
                'name' => 'WA Account ' . ($receiverPhone ?: $sessionId),
                'phone' => $receiverPhone ?: null,
                'session_id' => $sessionId,
                'status' => 'CONNECTED'
            ]);
            $waAccount->ensureDefaultStages();
        } else {
            $waAccount->ensureDefaultStages();
            if ($receiverPhone && !$waAccount->phone) {
                $waAccount->phone = $receiverPhone;
                $waAccount->status = 'CONNECTED';
                $waAccount->save();
            }
        }

        $myNumber = $waAccount->phone ?: $receiverPhone;
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
        $lead = Lead::where('phone', $leadPhone)->first();

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
        $matchedStageName = null;

        foreach ($activeTriggers as $trigger) {
            if ($trigger->pipelineStage && str_contains($lowerMessage, strtolower($trigger->keyword))) {
                $matchedStageName = $trigger->pipelineStage->name;
                break;
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
                    'wa_account_id' => $waAccount->id,
                    'name'  => $displayName,
                    'phone' => $leadPhone,
                    'stage' => $matchedStageName ?: $defaultStageName
                ]);
            } elseif ($matchedStageName) {
                $lead->stage = $matchedStageName;
                $lead->save();
            }
        } else {
            // Incoming lead from customer
            $displayName = $senderNameInput ?: $this->formatDisplayPhone($leadPhone);

            if (!$lead) {
                $lead = Lead::create([
                    'wa_account_id' => $waAccount->id,
                    'name'  => $displayName,
                    'phone' => $leadPhone,
                    'stage' => $matchedStageName ?: $defaultStageName
                ]);
            } else {
                if (!$lead->wa_account_id) {
                    $lead->wa_account_id = $waAccount->id;
                }
                if ($senderNameInput && (str_contains($lead->name, 'Lead') || preg_match('/^[0-9]+$/', $lead->name))) {
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
        $reason = $request->input('reason') ?? 'Lost connection / Unauthenticated';

        $waAccount = WaAccount::where('session_id', $sessionId)->first();
        if ($waAccount) {
            $waAccount->status = 'DISCONNECTED';
            $waAccount->save();

            // Send Email Notification Alert to CEO Users
            $ceos = User::where('role', 'CEO')->get();
            $accountName = $waAccount->name;
            $phone = $waAccount->phone ?: 'Belum Terhubung';

            foreach ($ceos as $ceo) {
                $to = $ceo->email;
                $subject = "⚠️ PERINGATAN DARURAT: WhatsApp CS {$accountName} Terputus!";
                
                $htmlBody = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; rounded-radius: 12px;'>
                        <h2 style='color: #dc2626;'>⚠️ PERINGATAN DARURAT WA TERPUTUS</h2>
                        <p>Halo CEO / Owner (<strong>{$ceo->name}</strong>),</p>
                        <p>Perangkat WhatsApp untuk brand <strong>{$accountName}</strong> (No: <code>{$phone}</code>) baru saja <strong>TERPUTUS</strong> dari server.</p>
                        <div style='background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px; margin: 15px 0;'>
                            <strong>Alasan Terputus:</strong> {$reason}
                        </div>
                        <p>Silakan segera menyambungkan kembali koneksi perangkat WhatsApp Anda:</p>
                        <p style='text-align: center; margin: 25px 0;'>
                            <a href='https://crm.difitech.id' style='background-color: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>
                                📲 Scan QR Code Ulang Sekarang
                            </a>
                        </p>
                        <hr style='border: none; border-top: 1px solid #e2e8f0; margin-top: 30px;' />
                        <p style='font-size: 11px; color: #94a3b8;'>Pesan ini dikirimkan secara otomatis oleh CRM MVP System Difitech.</p>
                    </div>
                </body>
                </html>
                ";

                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: CRM Alert <no-reply@difitech.id>\r\n";
                $headers .= "Reply-To: no-reply@difitech.id\r\n";

                @mail($to, $subject, $htmlBody, $headers);
            }

            Log::warning("⚠️ DISCONNECTION EMAIL ALERT DISPATCHED to CEOs for Account {$accountName} ({$sessionId})");
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
        return '+' . $phone;
    }
}
