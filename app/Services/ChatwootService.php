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
    public function getConversations(string $status = 'open', int $page = 1): array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/conversations", [
            'inbox_id'      => $this->inboxId,
            'page'          => $page,
            'assignee_type' => 'all',
            'status'        => $status,
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

        return $res->json('payload', []);
    }

    /** إرسال رسالة عبر Chatwoot → WhatsApp */
    public function sendMessage(int $conversationId, string $content, bool $private = false): bool
    {
        $res = $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages",
            [
                'content'      => $content,
                'message_type' => 'outgoing',
                'private'      => $private,
            ]
        );

        return $res->successful();
    }

    /** تعليم المحادثة كمقروءة */
    public function markAsRead(int $conversationId): void
    {
        $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/update_last_seen"
        );
    }

    /** تغيير حالة المحادثة (open / resolved / pending) */
    public function toggleStatus(int $conversationId, string $status): bool
    {
        $res = $this->http()->patch(
            "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}",
            ['status' => $status]
        );

        return $res->successful();
    }

    // ────────────────────────────────────────────────
    // Labels
    // ────────────────────────────────────────────────

    /**
     * جلب كل الوسوم (Labels) المتاحة في الحساب
     * يرجع: [ [ id, title, color ] ]
     */
    public function getLabels(): array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/labels");

        if (!$res->successful()) return [];

        return collect($res->json('payload', []))->map(fn($l) => [
            'id'    => $l['id'] ?? 0,
            'title' => $l['title'] ?? '',
            'color' => $l['color'] ?? '#6366f1',
        ])->values()->all();
    }

    /**
     * تعيين وسوم لمحادثة
     */
    public function setConversationLabels(int $convId, array $labels): bool
    {
        $res = $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$convId}/labels",
            ['labels' => $labels]
        );

        return $res->successful();
    }

    // ────────────────────────────────────────────────
    // Teams
    // ────────────────────────────────────────────────

    /**
     * جلب قائمة الفرق (Teams)
     * يرجع: [ [ id, name ] ]
     */
    public function getTeams(): array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/teams");

        if (!$res->successful()) return [];

        return collect($res->json() ?? [])->map(fn($t) => [
            'id'   => $t['id'] ?? 0,
            'name' => $t['name'] ?? '',
        ])->values()->all();
    }

    /**
     * تعيين فريق لمحادثة — إذا $teamId = 0 يُلغي التعيين
     */
    public function assignTeam(int $convId, int $teamId): bool
    {
        $res = $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$convId}/assignments",
            ['team_id' => $teamId > 0 ? $teamId : null]
        );

        return $res->successful();
    }

    // ────────────────────────────────────────────────
    // Priority
    // ────────────────────────────────────────────────

    /**
     * تعيين أولوية للمحادثة (none / low / medium / high / urgent)
     */
    public function setPriority(int $convId, string $priority): bool
    {
        $res = $this->http()->patch(
            "/api/v1/accounts/{$this->accountId}/conversations/{$convId}",
            ['priority' => $priority ?: null]
        );

        return $res->successful();
    }

    // ────────────────────────────────────────────────
    // Mute / Unmute
    // ────────────────────────────────────────────────

    /** كتم إشعارات محادثة */
    public function muteConversation(int $convId): void
    {
        $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$convId}/mute"
        );
    }

    /** إلغاء كتم إشعارات محادثة */
    public function unmuteConversation(int $convId): void
    {
        $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$convId}/unmute"
        );
    }

    // ────────────────────────────────────────────────
    // Agents
    // ────────────────────────────────────────────────

    /**
     * جلب قائمة الوكلاء (Agents) من Chatwoot
     * يرجع مصفوفة من: [ id, name, availability_status ]
     */
    public function getAgents(): array
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}/agents");

        if (!$res->successful()) return [];

        return collect($res->json() ?? [])->map(fn($agent) => [
            'id'                  => $agent['id'] ?? 0,
            'name'                => $agent['name'] ?? '',
            'availability_status' => $agent['availability_status'] ?? 'offline',
        ])->values()->all();
    }

    /**
     * تعيين وكيل لمحادثة — إذا $agentId = 0 يُلغي التعيين
     */
    public function assignConversation(int $convId, int $agentId): bool
    {
        $res = $this->http()->post(
            "/api/v1/accounts/{$this->accountId}/conversations/{$convId}/assignments",
            ['assignee_id' => $agentId === 0 ? null : $agentId]
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
            'q'                => $phone,
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
        $data = $res->json('payload', []);
        return is_array($data) && isset($data['payload']) ? $data['payload'] : (array) $data;
    }

    /** إنشاء contact جديد */
    public function createContact(string $name, string $phone): ?array
    {
        $res = $this->http()->post("/api/v1/accounts/{$this->accountId}/contacts", [
            'name'         => $name,
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

    /** إرسال رسالة لرقم هاتف — يستخدم المحادثة الموجودة إن وجدت */
    public function sendToPhone(string $name, string $phone, string $message): bool
    {
        $contact = $this->searchContact($phone);

        if (!$contact) {
            $contact = $this->createContact($name, $phone);
            if (!$contact) return false;
        }

        $contactId = $contact['id'] ?? null;
        if (!$contactId) return false;

        $convId = null;
        $existingConvs = $this->getContactConversations($contactId);
        if (!empty($existingConvs)) {
            $open = collect($existingConvs)
                ->filter(fn($c) => in_array($c['status'] ?? '', ['open', 'pending']))
                ->sortByDesc('last_activity_at')
                ->first();
            if (!$open) {
                $open = collect($existingConvs)->sortByDesc('last_activity_at')->first();
            }
            $convId = $open['id'] ?? null;
        }

        if (!$convId) {
            $conv = $this->createConversation($contactId);
            if (!$conv) return false;
            $convId = $conv['id'] ?? null;
        }

        if (!$convId) return false;

        return $this->sendMessage((int) $convId, $message);
    }

    /** تحقق من الاتصال */
    public function testConnection(): bool
    {
        $res = $this->http()->get("/api/v1/accounts/{$this->accountId}");
        return $res->successful();
    }
}
