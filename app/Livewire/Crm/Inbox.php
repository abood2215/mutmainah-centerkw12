<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\ChatwootService;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\CrmClient;
use App\Models\CannedResponse;
use App\Models\ConversationAssignment;

class Inbox extends Component
{
    public $activeConversationId = null;
    public $activeConvData  = null; // array
    public $conversations   = [];
    public $messages        = [];
    public $newMessage      = '';
    public $isPrivateNote   = false;
    public $filterChannel   = '';
    public $filterStatus    = '';
    public $searchQuery     = '';
    public $source          = 'local'; // local | chatwoot

    // لبدء محادثة جديدة مع عميل ما عنده محادثة بعد
    public $pendingClientPhone = '';
    public $pendingClientName  = '';

    // ─── New Properties ────────────────────────────────────────────────────────

    /** Chatwoot agents list: [ [ id, name, availability_status ] ] */
    public array $agents = [];

    /** Name of the currently assigned agent for the active conversation */
    public string $assignedAgentName = '';

    /** Canned responses search query */
    public string $cannedSearch = '';

    /** Whether the canned responses dropdown is shown */
    public bool $showCanned = false;

    /** Total unread conversations count */
    public int $unreadTotal = 0;

    // ─── Mount ─────────────────────────────────────────────────────────────────

    public function mount()
    {
        $this->loadConversations();
        $this->getUnreadCount();

        // Load Chatwoot agents
        try {
            $chatwoot = new ChatwootService();
            $this->agents = $chatwoot->getAgents();
        } catch (\Exception $e) {
            $this->agents = [];
        }

        // افتح محادثة العميل تلقائياً لما يجي من ملف العميل
        if ($phone = request('phone')) {
            $foundId = $this->findConversationByPhone($phone);

            if ($foundId) {
                $this->selectConversation($foundId);
            } else {
                $this->pendingClientPhone = $phone;
                $this->pendingClientName  = request('name', '');
            }
        }
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function findConversationByPhone(string $phone): ?int
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();

                foreach ([$phone, '+' . ltrim($phone, '+')] as $searchTerm) {
                    $contact = $chatwoot->searchContact($searchTerm);
                    if ($contact && isset($contact['id'])) {
                        $convs = $chatwoot->getContactConversations((int) $contact['id']);
                        $inboxId = config('chatwoot.inbox_id');
                        $found = collect($convs)
                            ->filter(fn($c) => ($c['inbox_id'] ?? 0) == $inboxId)
                            ->sortByDesc('last_activity_at')
                            ->first();

                        if (!$found) {
                            $found = collect($convs)->sortByDesc('last_activity_at')->first();
                        }

                        if ($found) return (int) $found['id'];
                    }
                }
            } catch (\Exception $e) {}
        }

        foreach ($this->conversations as $conv) {
            $convPhone = preg_replace('/[^0-9]/', '', $conv['client_phone'] ?? '');
            if (!$convPhone) continue;
            if ($cleanPhone === $convPhone ||
                str_ends_with($cleanPhone, substr($convPhone, -9)) ||
                str_ends_with($convPhone, substr($cleanPhone, -9))) {
                return (int) $conv['id'];
            }
        }

        return null;
    }

    // ─── Conversations ─────────────────────────────────────────────────────────

    public function loadConversations()
    {
        try {
            $chatwoot = new ChatwootService();
            $raw = $chatwoot->getConversations();

            if (!empty($raw)) {
                $this->source = 'chatwoot';
                $this->conversations = collect($raw)->map(function ($c) {
                    // Pull saved assignment for this conversation
                    $assignment = ConversationAssignment::where('chatwoot_conv_id', $c['id'])
                        ->latest('assigned_at')
                        ->first();

                    return [
                        'id'              => $c['id'],
                        'status'          => $c['status'],
                        'last_message_at' => isset($c['last_activity_at'])
                                             ? \Carbon\Carbon::createFromTimestamp($c['last_activity_at'])->toDateTimeString()
                                             : null,
                        'channel'         => 'whatsapp',
                        'client_name'     => $c['meta']['sender']['name'] ?? 'مجهول',
                        'client_phone'    => $c['meta']['sender']['phone_number'] ?? '',
                        'unread'          => $c['unread_count'] ?? 0,
                        'conv_source'     => 'chatwoot',
                        'assigned_agent'  => $assignment?->agent_name ?? '',
                    ];
                })->values()->all();
                return;
            }
        } catch (\Exception $e) {}

        $this->source = 'local';
        $this->conversations = CrmConversation::with('client')
            ->when($this->filterChannel, fn($q) => $q->where('channel', $this->filterChannel))
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($c) {
                return [
                    'id'              => $c->id,
                    'status'          => $c->status,
                    'last_message_at' => $c->last_message_at?->toDateTimeString(),
                    'channel'         => $c->channel,
                    'client_name'     => $c->client->name ?? '—',
                    'client_phone'    => $c->client->phone ?? '',
                    'unread'          => 0,
                    'conv_source'     => 'local',
                    'assigned_agent'  => '',
                ];
            })->values()->all();
    }

    public function selectConversation($id)
    {
        $this->activeConversationId = $id;

        $this->activeConvData = collect($this->conversations)
            ->first(fn($c) => $c['id'] == $id);

        if (!$this->activeConvData) {
            $this->activeConvData = [
                'id'              => $id,
                'status'          => 'open',
                'last_message_at' => null,
                'channel'         => 'whatsapp',
                'client_name'     => 'محادثة #' . $id,
                'client_phone'    => '',
                'unread'          => 0,
                'conv_source'     => $this->source,
                'assigned_agent'  => '',
            ];
        }

        // Load assignment info for this conversation
        $assignment = ConversationAssignment::where('chatwoot_conv_id', $id)
            ->latest('assigned_at')
            ->first();
        $this->assignedAgentName = $assignment?->agent_name ?? '';

        $this->loadMessages();

        if ($this->source === 'chatwoot') {
            try {
                (new ChatwootService())->markAsRead((int) $id);
            } catch (\Exception $e) {}
        }

        $this->conversations = collect($this->conversations)->map(function ($c) use ($id) {
            if ($c['id'] == $id) $c['unread'] = 0;
            return $c;
        })->values()->all();

        $this->getUnreadCount();
    }

    // ─── Messages ──────────────────────────────────────────────────────────────

    public function loadMessages()
    {
        if (!$this->activeConversationId) return;

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();
                $raw = $chatwoot->getMessages($this->activeConversationId);

                $this->messages = collect($raw)
                    ->filter(fn($m) => in_array($m['message_type'], [0, 1, 2]))
                    ->map(fn($m) => [
                        'id'           => $m['id'],
                        'content'      => $m['content'] ?? '',
                        'content_type' => $m['content_type'] ?? 'text',
                        'direction'    => match((int)($m['message_type'] ?? 0)) {
                            0 => 'in',
                            2 => 'activity',
                            default => 'out',
                        },
                        'status'      => $m['status'] ?? 'sent',
                        'private'     => (bool)($m['private'] ?? false),
                        'sent_at'     => isset($m['created_at']) ? \Carbon\Carbon::createFromTimestamp($m['created_at'])->toDateTimeString() : null,
                        '_ts'         => $m['created_at'] ?? 0,
                        'attachments' => $m['attachments'] ?? [],
                        'sender_name' => $m['sender']['name'] ?? '',
                    ])->sortBy('_ts')->values()->all();
                return;
            } catch (\Exception $e) {}
        }

        $this->messages = CrmMessage::where('conversation_id', $this->activeConversationId)
            ->orderBy('sent_at', 'asc')
            ->get()
            ->map(function ($m) {
                return [
                    'id'        => $m->id,
                    'content'   => $m->content,
                    'direction' => $m->direction,
                    'sent_at'   => $m->sent_at?->toDateTimeString(),
                ];
            })->values()->all();
    }

    // ─── Send ───────────────────────────────────────────────────────────────────

    public function startNewConversation()
    {
        if (!$this->pendingClientPhone || empty(trim($this->newMessage))) return;

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();

                $contact = $chatwoot->searchContact($this->pendingClientPhone);
                if (!$contact) {
                    $contact = $chatwoot->createContact(
                        $this->pendingClientName ?: 'عميل',
                        $this->pendingClientPhone
                    );
                }

                if ($contact && isset($contact['id'])) {
                    $conv = $chatwoot->createConversation((int) $contact['id']);
                    if ($conv && isset($conv['id'])) {
                        $convId = (int) $conv['id'];
                        $chatwoot->sendMessage($convId, $this->newMessage);
                        $this->newMessage         = '';
                        $this->pendingClientPhone = '';
                        $this->pendingClientName  = '';
                        $this->loadConversations();
                        $this->selectConversation($convId);
                        return;
                    }
                }
            } catch (\Exception $e) {}
        }

        $this->newMessage = '';
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) return;

        if ($this->pendingClientPhone && !$this->activeConversationId) {
            $this->startNewConversation();
            return;
        }

        if (!$this->activeConversationId) return;

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();
                $chatwoot->sendMessage($this->activeConversationId, $this->newMessage, $this->isPrivateNote);
            } catch (\Exception $e) {}
        } else {
            $conv = CrmConversation::with('client')->find($this->activeConversationId);
            if ($conv && $conv->client && $conv->client->phone) {
                $client = $conv->client;
                $client->sendWhatsApp($client->phone, $this->newMessage);
            }

            CrmMessage::create([
                'conversation_id' => $this->activeConversationId,
                'direction'       => 'out',
                'content'         => $this->newMessage,
                'sent_at'         => now(),
            ]);

            CrmConversation::where('id', $this->activeConversationId)
                ->update(['last_message_at' => now()]);
        }

        $this->newMessage  = '';
        $this->showCanned  = false;
        $this->loadMessages();
    }

    public function toggleStatus($id)
    {
        $currentConv = collect($this->conversations)->first(fn($c) => $c['id'] == $id);
        $currentStatus = $currentConv['status'] ?? 'open';
        $newStatus = $currentStatus === 'open' ? 'resolved' : 'open';

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();
                $chatwoot->toggleStatus((int) $id, $newStatus);
            } catch (\Exception $e) {}

            // نحدث الحالة محلياً بدون إعادة جلب من Chatwoot
            // لأن Chatwoot ترجع فقط المحادثات المفتوحة فتختفي المغلقة من القائمة
            $this->conversations = collect($this->conversations)->map(function ($c) use ($id, $newStatus) {
                if ($c['id'] == $id) {
                    $c['status'] = $newStatus;
                }
                return $c;
            })->values()->all();
        } else {
            $conv = CrmConversation::find($id);
            if ($conv) {
                $conv->status = $newStatus;
                $conv->save();
            }
            $this->loadConversations();
        }

        if ($this->activeConversationId) {
            $this->activeConvData = collect($this->conversations)
                ->first(fn($c) => $c['id'] == $this->activeConversationId);
        }
    }

    // ─── Assignment ────────────────────────────────────────────────────────────

    /**
     * Assign a Chatwoot agent to the active conversation.
     */
    public function assignConversation(int $agentId, string $agentName): void
    {
        if (!$this->activeConversationId) return;

        try {
            $chatwoot = new ChatwootService();
            $chatwoot->assignConversation((int) $this->activeConversationId, $agentId);
        } catch (\Exception $e) {}

        ConversationAssignment::updateOrCreate(
            ['chatwoot_conv_id' => $this->activeConversationId],
            [
                'conversation_id' => $this->activeConversationId,
                'agent_name'      => $agentName,
                'assigned_at'     => now(),
            ]
        );

        $this->assignedAgentName = $agentName;
        $this->loadConversations();
    }

    // ─── Canned Responses ──────────────────────────────────────────────────────

    /**
     * Returns filtered canned responses based on $cannedSearch.
     */
    public function loadCannedResponses(): array
    {
        return CannedResponse::when($this->cannedSearch, function ($q) {
            $search = $this->cannedSearch;
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('content', 'LIKE', "%{$search}%");
        })->orderBy('title')->get()->map(fn($r) => [
            'id'      => $r->id,
            'title'   => $r->title,
            'content' => $r->content,
        ])->values()->all();
    }

    /**
     * Fill newMessage with selected canned response content (by ID — safe against special chars).
     */
    public function selectCannedResponse(int $id): void
    {
        $response = CannedResponse::find($id);
        if ($response) {
            $this->newMessage = $response->content;
        }
        $this->showCanned   = false;
        $this->cannedSearch = '';
    }

    // ─── Unread Count ──────────────────────────────────────────────────────────

    public function getUnreadCount(): void
    {
        $this->unreadTotal = collect($this->conversations)
            ->filter(fn($c) => ($c['unread'] ?? 0) > 0)
            ->count();
    }

    // ─── Refresh ───────────────────────────────────────────────────────────────

    public function refreshAll()
    {
        $this->loadConversations();
        $this->getUnreadCount();

        if ($this->activeConversationId) {
            $found = collect($this->conversations)
                ->first(fn($c) => $c['id'] == $this->activeConversationId);
            if ($found) {
                $this->activeConvData = $found;
            }
            $this->loadMessages();
        }
    }

    #[On('refresh-inbox')]
    public function refresh()
    {
        $this->refreshAll();
    }

    // ─── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $filteredConversations = collect($this->conversations)
            ->when($this->filterStatus, fn($c) => $c->filter(fn($conv) => $conv['status'] === $this->filterStatus))
            ->when($this->searchQuery, function ($c) {
                $q = mb_strtolower($this->searchQuery);
                return $c->filter(fn($conv) =>
                    str_contains(mb_strtolower($conv['client_name']), $q) ||
                    str_contains($conv['client_phone'], $q)
                );
            })
            ->values()->all();

        $cannedResponses = $this->showCanned ? $this->loadCannedResponses() : [];

        return view('livewire.crm.inbox', compact('filteredConversations', 'cannedResponses'))
            ->layout('layouts.app');
    }
}
