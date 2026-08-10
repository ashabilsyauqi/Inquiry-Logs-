<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\WaAccount;
use App\Models\LeadMessage;
use App\Models\StageTrigger;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleIncomingWA(Request $request)
    {
        Log::info('Incoming WA Payload: ' . json_encode($request->all()));

        $sender = $request->input('sender') ?? $request->input('from');
        $receiver = $request->input('receiver') ?? $request->input('to');
        $message = $request->input('message') ?? $request->input('text');
        $senderNameInput = $request->input('senderName');
        $sessionId = $request->input('sessionId') ?? 'default';

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
        $lowerMessage = strtolower($message);

        $isFromMe = $request->input('isFromMe') || ($senderPhone === $myNumber);
        $lead = null;

        $leadPhone = $isFromMe ? $receiverPhone : $senderPhone;
        $lead = Lead::where('phone', $leadPhone)->first();

        // Dynamic Keyword Stage Automation Triggers
        $activeTriggers = StageTrigger::where('wa_account_id', $waAccount->id)->with('pipelineStage')->get();
        $matchedStageName = null;

        foreach ($activeTriggers as $trigger) {
            if ($trigger->pipelineStage && str_contains($lowerMessage, strtolower($trigger->keyword))) {
                $matchedStageName = $trigger->pipelineStage->name;
                break;
            }
        }

        $firstStage = $waAccount->pipelineStages()->first();
        $defaultStageName = $firstStage ? $firstStage->name : 'Lead Masuk';

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

        // Store chat log in LeadMessage
        if ($lead) {
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
                $message = "Halo CEO / Owner,\n\n"
                         . "Peringatan! Perangkat WhatsApp untuk brand '{$accountName}' (No: {$phone}) telah TERPUTUS dari server.\n"
                         . "Alasan: {$reason}\n\n"
                         . "Silakan segera masuk ke CRM Admin Panel (https://crm.difitech.id) dan lakukan Scan Barcode QR Code ulang untuk menyambungkan kembali koneksi.\n\n"
                         . "Pesan ini dikirimkan secara otomatis oleh CRM MVP System.";
                
                @mail($to, $subject, $message, "From: CRM Alert <no-reply@difitech.id>");
            }

            Log::warning("⚠️ DISCONNECTION EMAIL ALERT DISPATCHED: Account {$accountName} ({$sessionId})");
        }

        return response()->json(['status' => 'success', 'message' => 'Disconnection alert processed']);
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
