<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatwootService
{
    private string $baseUrl;
    private string $token;
    private int    $accountId;
    private int    $inboxId;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('chatwoot.url'), '/');
        $this->token     = config('chatwoot.api_token');
        $this->accountId = (int) config('chatwoot.account_id');
        $this->inboxId   = (int) config('chatwoot.inbox_id');
    }

    private function http()
    {
        return Http::withHeaders([
            'api_access_token' => $this->token,
            'Content-Type'     => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    // ────────────────────────────────────────────────
    // Conversations
    // ────────────────────────────────────────────────

    /** جلب كل المحادثات من inbox الواتساب */
    public function getConversations(int $page = 1): array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/conversations", [
            'inbox_id'    => $this->inboxId,
            'page'        => $page,
            'assignee_type' => 'all',
        ]);

        if (!$res->successful()) return [];

        return $res->json('data.payload', []);
    }

    /** جلب محادثة واحدة بالـ id */
    public function getConversation(int $conversationId): ?array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}");
        return $res->successful() ? $res->json() : null;
    }

    /** جلب رسائل محادثة */
    public function getMessages(int $conversationId): array
    {
        $res = $this->http()->get(
            "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages"
        );

        if (!$res->successful()) return [];

        return array_reverse($res->json('payload', []));
    }

    /** إرسال رسالة عبر Chatwoot → WhatsApp */
    public function sendMessage(int $conversationId, string $content): bool
    {
        $res = $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages",
            [
                'content'      => $content,
                'message_type' => 'outgoing',
                'private'      => false,
            ]
        );

        return $res->successful();
    }

    /** تغيير حالة المحادثة (open / resolved) */
    public function toggleStatus(int $conversationId, string $status): bool
    {
        $res = $this->http()->patch(
            "/api/v1/accounts/{$this->accountId}/conversations",
            [
                'ids'    => [$conversationId],
                'status' => $status,
            ]
        );

        return $res->successful();
    }

    // ────────────────────────────────────────────────
    // Contacts
    // ────────────────────────────────────────────────

    /** البحث عن contact بالهاتف */
    public function searchContact(string $phone): ?array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/contacts/search", [
            'q'             => $phone,
            'include_contacts' => true,
        ]);

        if (!$res->successful()) return null;

        $payload = $res->json('payload', []);
        return $payload[0] ?? null;
    }

    /** جلب محادثات contact معين */
    public function getContactConversations(int $contactId): array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/conversations");
        if (!$res->successful()) return [];
        // Chatwoot يرجع { payload: { meta: {}, payload: [...] } }
        $data = $res->json('payload', []);
        return is_array($data) && isset($data['payload']) ? $data['payload'] : (array) $data;
    }

    /** إنشاء contact جديد */
    public function createContact(string $name, string $phone): ?array
    {
        $res = $this->http()->post("/api/v1/accounts/{$this->accountId}/contacts", [
            'name'  => $name,
            'phone_number' => '+' . ltrim($phone, '+'),
        ]);

        return $res->successful() ? $res->json() : null;
    }

    /** إنشاء محادثة جديدة لـ contact */
    public function createConversation(int $contactId): ?array
    {
        $res = $this->http()->post("/api/v1/accounts/{$this->accountId}/conversations", [
            'inbox_id'   => $this->inboxId,
            'contact_id' => $contactId,
        ]);

        return $res->successful() ? $res->json() : null;
    }

    // ────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────

    /** إرسال رسالة لرقم هاتف (ينشئ contact + conversation تلقائياً) */
    public function sendToPhone(string $name, string $phone, string $message): bool
    {
        // 1. ابحث عن contact
        $contact = $this->searchContact($phone);

        // 2. أنشئ إذا ما وجد
        if (!$contact) {
            $contact = $this->createContact($name, $phone);
            if (!$contact) return false;
        }

        $contactId = $contact['id'] ?? null;
        if (!$contactId) return false;

        // 3. أنشئ محادثة
        $conv = $this->createConversation($contactId);
        if (!$conv) return false;

        $convId = $conv['id'] ?? null;
        if (!$convId) return false;

        // 4. أرسل الرسالة
        return $this->sendMessage($convId, $message);
    }

    /** تحقق من الاتصال */
    public function testConnection(): bool
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}");
        return $res->successful();
    }
}
