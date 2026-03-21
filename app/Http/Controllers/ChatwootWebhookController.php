<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessHour;
use App\Models\AutoReply;
use App\Services\ChatwootService;

class ChatwootWebhookController extends Controller
{
    /**
     * POST /webhooks/chatwoot
     *
     * Handles Chatwoot webhook events.
     * On message_created (incoming from customer):
     *   - Check if within business hours
     *   - If outside hours and auto-reply is active → send auto-reply
     *   - Avoid duplicate replies (once per conversation per day)
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Only handle message_created events
        if (($payload['event'] ?? '') !== 'message_created') {
            return response()->json(['ok' => true]);
        }

        // Only incoming messages from customers (message_type = 0 = incoming)
        $messageType = $payload['message_type'] ?? null;
        if ($messageType !== 'incoming') {
            return response()->json(['ok' => true]);
        }

        // Private notes don't trigger auto-reply
        if ($payload['private'] ?? false) {
            return response()->json(['ok' => true]);
        }

        $conversationId = $payload['conversation']['id'] ?? null;
        if (!$conversationId) {
            return response()->json(['ok' => true]);
        }

        // ── Outside Hours Auto-Reply ────────────────────────────────────────────

        $this->handleOutsideHoursReply((int) $conversationId);

        return response()->json(['ok' => true]);
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    private function handleOutsideHoursReply(int $conversationId): void
    {
        // If within business hours → no auto-reply needed
        if (BusinessHour::isWithinBusinessHours()) {
            return;
        }

        // Find an active outside_hours auto-reply
        $autoReply = AutoReply::where('trigger', 'outside_hours')
            ->where('is_active', true)
            ->first();

        if (!$autoReply || empty($autoReply->message)) {
            return;
        }

        // Avoid sending more than once today for this conversation.
        // We use a simple cache key keyed by conversation + date.
        $cacheKey = "chatwoot_auto_reply_{$conversationId}_" . now()->toDateString();

        if (cache()->has($cacheKey)) {
            return; // Already sent today
        }

        // Send the auto-reply
        try {
            $chatwoot = new ChatwootService();
            $sent = $chatwoot->sendMessage($conversationId, $autoReply->message, false);

            if ($sent) {
                // Mark as sent for today (expire at midnight)
                $secondsUntilMidnight = now()->secondsUntilEndOfDay();
                cache()->put($cacheKey, true, $secondsUntilMidnight);

                // Update last_sent_at
                $autoReply->update(['last_sent_at' => now()]);
            }
        } catch (\Exception $e) {
            // Silently fail — webhook must always return 200
        }
    }
}
