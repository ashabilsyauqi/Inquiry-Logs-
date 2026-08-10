<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\WaAccount;
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
        } else if ($receiverPhone && !$waAccount->phone) {
            $waAccount->phone = $receiverPhone;
            $waAccount->status = 'CONNECTED';
            $waAccount->save();
        }

        $myNumber = $waAccount->phone ?: $receiverPhone;
        $lowerMessage = strtolower($message);

        $isFromMe = $request->input('isFromMe') || ($senderPhone === $myNumber);

        if ($isFromMe) {
            $leadPhone = $receiverPhone;
            $lead = Lead::where('phone', $leadPhone)->first();

            // Stage 2: Meeting Call
            if (str_contains($lowerMessage, 'hallo selamat datang') || str_contains($lowerMessage, 'meeting') || str_contains($lowerMessage, 'call')) {
                if (!$lead) {
                    $displayName = $this->formatDisplayPhone($leadPhone);
                    $lead = Lead::create([
                        'wa_account_id' => $waAccount->id,
                        'name'  => $displayName,
                        'phone' => $leadPhone,
                        'stage' => 'Meeting Call'
                    ]);
                } elseif ($lead->stage === 'Lead Masuk') {
                    $lead->stage = 'Meeting Call';
                    $lead->save();
                }
            }

            // Stage 3: Kirim Penawaran
            if (str_contains($lowerMessage, 'penawaran') || str_contains($lowerMessage, 'silahkan melakukan pembayaran')) {
                if ($lead && ($lead->stage === 'Meeting Call' || $lead->stage === 'Lead Masuk')) {
                    $lead->stage = 'Kirim Penawaran';
                    $lead->save();
                }
            }

            // Stage 4: Deal
            if (str_contains($lowerMessage, 'deal') || (str_contains($lowerMessage, 'terverifikasi') && str_contains($lowerMessage, 'terima kasih'))) {
                if ($lead && ($lead->stage === 'Kirim Penawaran' || $lead->stage === 'Meeting Call')) {
                    $lead->stage = 'Deal';
                    $lead->save();
                }
            }
        } else {
            // Incoming lead from customer -> Default Stage: "Lead Masuk"
            $leadPhone = $senderPhone;
            $lead = Lead::where('phone', $leadPhone)->first();

            $displayName = $senderNameInput ?: $this->formatDisplayPhone($leadPhone);

            if (!$lead) {
                $lead = Lead::create([
                    'wa_account_id' => $waAccount->id,
                    'name'  => $displayName,
                    'phone' => $leadPhone,
                    'stage' => 'Lead Masuk'
                ]);
            } else {
                if (!$lead->wa_account_id) {
                    $lead->wa_account_id = $waAccount->id;
                }
                if ($senderNameInput && (str_contains($lead->name, 'Lead') || preg_match('/^[0-9]+$/', $lead->name))) {
                    $lead->name = $senderNameInput;
                }
                $lead->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully'
        ]);
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
