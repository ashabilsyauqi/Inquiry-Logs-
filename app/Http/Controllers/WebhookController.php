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
        $sessionId = $request->input('sessionId') ?? 'default';

        if (!$sender || !$receiver || !$message) {
            return response()->json(['status' => 'error', 'message' => 'Missing sender, receiver, or message'], 400);
        }

        $senderPhone = $this->sanitizePhone($sender);
        $receiverPhone = $this->sanitizePhone($receiver);

        // Find or create the target WaAccount based on session_id or receiver phone
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

        // Check if message is from owner (outgoing) or from customer (incoming)
        $isFromMe = $request->input('isFromMe') || ($senderPhone === $myNumber);

        if ($isFromMe) {
            $leadPhone = $receiverPhone;
            $lead = Lead::where('phone', $leadPhone)->first();

            // Trigger 1: Upgrade to Follow Up
            if (str_contains($lowerMessage, 'hallo selamat datang')) {
                if (!$lead) {
                    $lead = Lead::create([
                        'wa_account_id' => $waAccount->id,
                        'name'  => 'Lead ' . $leadPhone,
                        'phone' => $leadPhone,
                        'stage' => 'Follow Up'
                    ]);
                    Log::info("New lead created (ID: {$lead->id}) with stage Follow Up.");
                } elseif ($lead->stage === 'Inquiries') {
                    $lead->stage = 'Follow Up';
                    $lead->save();
                }
            }

            // Trigger 2: Move to Payment
            if (str_contains($lowerMessage, 'silahkan melakukan pembayaran')) {
                if ($lead && $lead->stage === 'Follow Up') {
                    $lead->stage = 'Payment';
                    $lead->save();
                }
            }

            // Trigger 3: Move to Closed
            if (str_contains($lowerMessage, 'terverifikasi') && str_contains($lowerMessage, 'terima kasih')) {
                if ($lead && $lead->stage === 'Payment') {
                    $lead->stage = 'Closed';
                    $lead->save();
                }
            }
        } else {
            // Incoming lead from customer
            $leadPhone = $senderPhone;
            $lead = Lead::where('phone', $leadPhone)->first();

            if (!$lead) {
                $lead = Lead::create([
                    'wa_account_id' => $waAccount->id,
                    'name'  => 'Lead ' . $leadPhone,
                    'phone' => $leadPhone,
                    'stage' => 'Inquiries'
                ]);
                Log::info("New INCOMING lead created (ID: {$lead->id}, Phone: {$leadPhone}) for Account: {$waAccount->name}");
            } else if (!$lead->wa_account_id) {
                $lead->wa_account_id = $waAccount->id;
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
}
