<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleIncomingWA(Request $request)
    {
        // Log the raw request payload
        Log::info('Incoming WhatsApp Webhook: ', $request->all());

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
        
        // Trigger if SENDER is YOU and message contains the specific phrase
        if ($senderPhone === $myNumber && str_contains($lowerMessage, 'silahkan melakukan pembayaran')) {
            // The target lead is the RECEIVER
            $leadPhone = $receiverPhone;
            
            // Find Lead
            $lead = Lead::where('phone', $leadPhone)->first();

            if ($lead) {
                // If Lead exists, update stage
                $lead->stage = 'Payment';
                $lead->save();
                Log::info("Lead {$lead->id} (Phone: {$leadPhone}) stage updated to Payment by Owner.");
            } else {
                // If Lead DOES NOT exist, create automatically
                $lead = Lead::create([
                    'name'  => 'Lead ' . $leadPhone,
                    'phone' => $leadPhone,
                    'stage' => 'Payment'
                ]);
                Log::info("New dynamic lead created (ID: {$lead->id}, Phone: {$leadPhone}) with stage Payment by Owner.");
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
