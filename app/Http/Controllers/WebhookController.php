<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleIncomingWA(Request $request)
    {
        // Debug dump
        Log::info('Incoming WA Payload: ' . json_encode($request->all()));

        // Extract sender, receiver, and message
        // Note: Field names might vary depending on your WhatsApp Gateway provider
        $sender = $request->input('sender') ?? $request->input('from');
        $receiver = $request->input('receiver') ?? $request->input('to');
        $message = $request->input('message') ?? $request->input('text');

        if (!$sender || !$receiver || !$message) {
            return response()->json(['status' => 'error', 'message' => 'Missing sender, receiver, or message'], 400);
        }

        // Sanitize phone numbers
        $senderPhone = $this->sanitizePhone($sender);
        $receiverPhone = $this->sanitizePhone($receiver);

        // Your WhatsApp Number
        $myNumber = '6287871976694';

        // Keyword matching
        $lowerMessage = strtolower($message);
        
        // Trigger if SENDER is YOU
        if ($senderPhone === $myNumber) {
            $leadPhone = $receiverPhone;
            
            // Trigger 1: Upgrade to Follow Up
            if (str_contains($lowerMessage, 'hallo selamat datang')) {
                $lead = Lead::where('phone', $leadPhone)->first();
                if (!$lead) {
                    $lead = Lead::create([
                        'name'  => 'Lead ' . $leadPhone,
                        'phone' => $leadPhone,
                        'stage' => 'Follow Up'
                    ]);
                    Log::info("New lead created (ID: {$lead->id}, Phone: {$leadPhone}) with stage Follow Up.");
                } elseif ($lead->stage === 'Inquiries') {
                    $lead->stage = 'Follow Up';
                    $lead->save();
                    Log::info("Lead {$lead->id} (Phone: {$leadPhone}) stage updated from Inquiries to Follow Up by Owner.");
                }
            }

            // Trigger 2: Move to Payment (Only if already in Follow Up)
            if (str_contains($lowerMessage, 'silahkan melakukan pembayaran')) {
                $lead = Lead::where('phone', $leadPhone)->first();
                if ($lead && $lead->stage === 'Follow Up') {
                    $lead->stage = 'Payment';
                    $lead->save();
                    Log::info("Lead {$lead->id} (Phone: {$leadPhone}) stage updated to Payment by Owner.");
                }
            }

            // Trigger 3: Move to Closed (Only if already in Payment)
            // Menggunakan dua keyword untuk menghindari typo "Pembayran"
            if (str_contains($lowerMessage, 'terverifikasi') && str_contains($lowerMessage, 'terima kasih')) {
                $lead = Lead::where('phone', $leadPhone)->first();
                if ($lead && $lead->stage === 'Payment') {
                    $lead->stage = 'Closed';
                    $lead->save();
                    Log::info("Lead {$lead->id} (Phone: {$leadPhone}) stage updated to Closed by Owner.");
                }
            }
        } else {
            // Trigger 0: Incoming Message (SENDER is NOT YOU) -> Inquiries
            if ($receiverPhone === $myNumber) {
                $leadPhone = $senderPhone;
                $lead = Lead::where('phone', $leadPhone)->first();
                if (!$lead) {
                    $lead = Lead::create([
                        'name'  => 'Lead ' . $leadPhone,
                        'phone' => $leadPhone,
                        'stage' => 'Inquiries'
                    ]);
                    Log::info("New INCOMING lead created (ID: {$lead->id}, Phone: {$leadPhone}) with stage Inquiries.");
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed owner trigger dynamically'
        ]);
    }

    private function sanitizePhone(string $phone): string
    {
        // Remove spaces, dashes, plus signs
        $phone = preg_replace('/[\s\-\+]/', '', $phone);

        // Convert leading 08... to 628...
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}
