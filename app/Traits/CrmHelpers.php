<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait CrmHelpers
{
    /**
     * Clean phone number (remove +, spaces, dashes, etc.)
     */
    public static function cleanPhone($phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Scope for current user permissions (Manager vs Employee)
     */
    public function scopeForCurrentUser($query)
    {
        $user = auth()->user();
        if (!$user) return $query;

        // Admin sees all clients, agent sees only their own
        if ($user->isAdmin()) return $query;

        return $query->where('assigned_to', $user->id);
    }

    /**
     * Send WhatsApp Message via Cloud API
     */
    public function sendWhatsApp($phone, $message)
    {
        $cleanPhone = self::cleanPhone($phone);
        $config = config('whatsapp');

        if (empty($config['token']) || empty($config['phone_number_id'])) {
            return false;
        }

        return Http::withToken($config['token'])
            ->post($config['api_url'] . '/' . $config['phone_number_id'] . '/messages', [
                'messaging_product' => 'whatsapp',
                'to'   => $cleanPhone,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);
    }
}
