<?php
namespace App\Livewire\Crm;

use Carbon\Carbon;
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
    // Core
    public $activeConversationId = null;
    public $activeConvData = null;
    public $conversations = [];
    public $messages = [];
    public $newMessage = '';
    public bool $isPrivateNote = false;
    public string $filterStatus = 'open';
    public string $searchQuery = '';
    public string $source = 'local';

    // Pending new conversation
    public string $pendingClientPhone = '';
    public string $pendingClientName = '';

    // Agents & Teams
    public array $agents = [];
    public array $teams = [];
    public string $assignedAgentName = '';
    public int $assignedAgentId = 0;
    public string $assignedTeamName = '';
    public int $assignedTeamId = 0;

    // Labels & Priority
    public array $allLabels = [];
    public array $activeConvLabels = [];
    public string $activeConvPriority = '';

    // Canned responses
    public string $cannedSearch = '';
    public bool $showCanned = false;

    // UI
    public bool $showInfo = false;
    public bool $isMuted = false;
    public int $unreadTotal = 0;
    public bool $showLabelsPicker = false;
    public bool $showPriorityPicker = false;

    private ?Carbon $lastRefresh = null;

    public function mount()
    {
        $this->loadConversations();
        $this->getUnreadCount();

        if ($this->source === 'chatwoot') {
            try {
                $svc = new ChatwootService();
                $this->agents    = $svc->getAgents();
                $this->teams     = $svc->getTeams();
                $this->allLabels = $svc->getLabels();
            } catch (\Exception $e) {}
        }

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

    public function updatedFilterStatus()
    {
        $this->loadConversations();
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function findConversationByPhone(string $phone): ?int
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if ($this->source === 'chatwoot') {
            try {
                $svc = new ChatwootService();
                foreach ([$phone, '+' . ltrim($phone, '+')] as $term) {
                    $contact = $svc->searchContact($term);
                    if ($contact && isset($contact['id'])) {
                        $convs   = $svc->getContactConversations((int) $contact['id']);
                        $inboxId = config('chatwoot.inbox_id');
                        $found   = collect($convs)
                            ->filter(fn($c) => ($c['inbox_id'] ?? 0) == $inboxId)
                            ->sortByDesc('last_activity_at')->first()
                            ?? collect($convs)->sortByDesc('last_activity_at')->first();
                        if ($found) return (int) $found['id'];
                    }
                }
            } catch (\Exception $e) {}
        }

        foreach ($this->conversations as $conv) {
            $cp = preg_replace('/[^0-9]/', '', $conv['client_phone'] ?? '');
            if ($cp && (
                $clean === $cp ||
                str_ends_with($clean, substr($cp, -9)) ||
                str_ends_with($cp, substr($clean, -9))
            )) return (int) $conv['id'];
        }

        return null;
    }

    // ─── Conversations ─────────────────────────────────────────────────────────

    public function loadConversations()
    {
        try {
            $svc    = new ChatwootService();
            $status = $this->filterStatus ?: 'open';
            $raw    = $svc->getConversations($status);

            if (!empty($raw)) {
                $this->source = 'chatwoot';
                $this->conversations = collect($raw)->map(function ($c) {
                    $assignment = ConversationAssignment::where('chatwoot_conv_id', $c['id'])
                        ->latest('assigned_at')->first();
                    return [
                        'id'              => $c['id'],
                        'status'          => $c['status'],
                        'labels'          => $c['labels'] ?? [],
                        'priority'        => $c['priority'] ?? null,
                        'muted'           => $c['muted'] ?? false,
                        'last_message_at' => isset($c['last_activity_at'])
                            ? \Carbon\Carbon::createFromTimestamp($c['last_activity_at'])->toDateTimeString()
                            : null,
                        'client_name'     => $c['meta']['sender']['name'] ?? 'مجهول',
                        'client_phone'    => $c['meta']['sender']['phone_number'] ?? '',
                        'client_email'    => $c['meta']['sender']['email'] ?? '',
                        'unread'          => $c['unread_count'] ?? 0,
                        'conv_source'     => 'chatwoot',
                        'assigned_agent'  => $assignment?->agent_name ?? ($c['meta']['assignee']['name'] ?? ''),
                        'assigned_agent_id' => $c['meta']['assignee']['id'] ?? 0,
                        'assigned_team'   => $c['meta']['team']['name'] ?? '',
                        'assigned_team_id' => $c['meta']['team']['id'] ?? 0,
                        'last_message'    => $c['last_non_activity_message']['content'] ?? '',
                        'channel'         => 'whatsapp',
                    ];
                })->values()->all();
                return;
            }
        } catch (\Exception $e) {}

        $this->source = 'local';
        $status = $this->filterStatus;
        $this->conversations = CrmConversation::with('client')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(fn($c) => [
                'id'               => $c->id,
                'status'           => $c->status,
                'labels'           => [],
                'priority'         => null,
                'muted'            => false,
                'last_message_at'  => $c->last_message_at?->toDateTimeString(),
                'client_name'      => $c->client->name ?? '—',
                'client_phone'     => $c->client->phone ?? '',
                'client_email'     => $c->client->email ?? '',
                'unread'           => 0,
                'conv_source'      => 'local',
                'assigned_agent'   => '',
                'assigned_agent_id' => 0,
                'assigned_team'    => '',
                'assigned_team_id' => 0,
                'last_message'     => '',
                'channel'          => $c->channel,
            ])->values()->all();
    }

    public function selectConversation($id)
    {
        $this->activeConversationId = $id;
        $this->showInfo             = false;
        $this->showLabelsPicker     = false;
        $this->showPriorityPicker   = false;
        $this->lastRefresh          = null;

        $this->activeConvData = collect($this->conversations)->first(fn($c) => $c['id'] == $id)
            ?? [
                'id'               => $id,
                'status'           => 'open',
                'labels'           => [],
                'priority'         => null,
                'muted'            => false,
                'last_message_at'  => null,
                'client_name'      => 'محادثة #' . $id,
                'client_phone'     => '',
                'client_email'     => '',
                'unread'           => 0,
                'conv_source'      => $this->source,
                'assigned_agent'   => '',
                'assigned_agent_id' => 0,
                'assigned_team'    => '',
                'assigned_team_id' => 0,
                'last_message'     => '',
                'channel'          => 'whatsapp',
            ];

        $this->activeConvLabels   = $this->activeConvData['labels'] ?? [];
        $this->activeConvPriority = $this->activeConvData['priority'] ?? '';
        $this->isMuted            = $this->activeConvData['muted'] ?? false;
        $this->assignedAgentName  = $this->activeConvData['assigned_agent'] ?? '';
        $this->assignedAgentId    = $this->activeConvData['assigned_agent_id'] ?? 0;
        $this->assignedTeamName   = $this->activeConvData['assigned_team'] ?? '';
        $this->assignedTeamId     = $this->activeConvData['assigned_team_id'] ?? 0;

        // DB assignment may override
        $assignment = ConversationAssignment::where('chatwoot_conv_id', $id)->latest('assigned_at')->first();
        if ($assignment?->agent_name) $this->assignedAgentName = $assignment->agent_name;

        $this->loadMessages(false);

        if ($this->source === 'chatwoot') {
            try { (new ChatwootService())->markAsRead((int) $id); } catch (\Exception $e) {}
        }

        $this->conversations = collect($this->conversations)->map(function ($c) use ($id) {
            if ($c['id'] == $id) $c['unread'] = 0;
            return $c;
        })->values()->all();

        $this->getUnreadCount();
    }

    // ─── Messages ──────────────────────────────────────────────────────────────

    public function loadMessages(bool $isRefresh = false)
    {
        if (!$this->activeConversationId) return;

        if ($this->source === 'chatwoot') {
            try {
                $raw = (new ChatwootService())->getMessages($this->activeConversationId);
                $this->messages = collect($raw)
                    ->filter(fn($m) => in_array($m['message_type'], [0, 1, 2, 3]))
                    ->map(fn($m) => [
                        'id'           => $m['id'],
                        'content'      => $m['content'] ?? '',
                        'content_type' => $m['content_type'] ?? 'text',
                        'direction'    => match((int)($m['message_type'] ?? 0)) {
                            0       => 'in',
                            2       => 'activity',
                            default => 'out',
                        },
                        'status'       => $m['status'] ?? 'sent',
                        'private'      => (bool)($m['private'] ?? false),
                        'sent_at'      => isset($m['created_at'])
                            ? Carbon::createFromTimestamp($m['created_at'])->toDateTimeString()
                            : null,
                        '_ts'          => $m['created_at'] ?? 0,
                        'attachments'  => $m['attachments'] ?? [],
                        'sender_name'  => $m['sender']['name'] ?? '',
                        'sender_type'  => $m['sender']['type'] ?? 'contact',
                    ])->sortBy('_ts')->values()->all();

                $this->lastRefresh = now();
                $this->dispatch($isRefresh ? 'messages-refreshed' : 'messages-loaded');
                return;
            } catch (\Exception $e) {}
        }

        $this->messages = CrmMessage::where('conversation_id', $this->activeConversationId)
            ->orderBy('sent_at', 'asc')->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'content'      => $m->content,
                'content_type' => 'text',
                'direction'    => $m->direction,
                'status'       => 'sent',
                'private'      => false,
                'sent_at'      => $m->sent_at?->toDateTimeString(),
                '_ts'          => 0,
                'attachments'  => [],
                'sender_name'  => '',
                'sender_type'  => '',
            ])->values()->all();

        $this->lastRefresh = now();
        $this->dispatch($isRefresh ? 'messages-refreshed' : 'messages-loaded');
    }

    // ─── Send ───────────────────────────────────────────────────────────────────

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
                (new ChatwootService())->sendMessage(
                    $this->activeConversationId,
                    $this->newMessage,
                    $this->isPrivateNote
                );
            } catch (\Exception $e) {}
        } else {
            $conv = CrmConversation::with('client')->find($this->activeConversationId);
            if ($conv?->client?->phone) {
                $conv->client->sendWhatsApp($conv->client->phone, $this->newMessage);
            }
            CrmMessage::create([
                'conversation_id' => $this->activeConversationId,
                'direction'       => 'out',
                'content'         => $this->newMessage,
                'sent_at'         => now(),
            ]);
            CrmConversation::where('id', $this->activeConversationId)->update(['last_message_at' => now()]);
        }

        $this->newMessage = '';
        $this->showCanned = false;
        $this->loadMessages(false); // force scroll after sending
    }

    public function startNewConversation()
    {
        if (!$this->pendingClientPhone || empty(trim($this->newMessage))) return;

        if ($this->source === 'chatwoot') {
            try {
                $svc     = new ChatwootService();
                $contact = $svc->searchContact($this->pendingClientPhone)
                    ?? $svc->createContact($this->pendingClientName ?: 'عميل', $this->pendingClientPhone);

                if ($contact && isset($contact['id'])) {
                    $conv = $svc->createConversation((int) $contact['id']);
                    if ($conv && isset($conv['id'])) {
                        $convId = (int) $conv['id'];
                        $svc->sendMessage($convId, $this->newMessage);
                        $this->newMessage = $this->pendingClientPhone = $this->pendingClientName = '';
                        $this->loadConversations();
                        $this->selectConversation($convId);
                        return;
                    }
                }
            } catch (\Exception $e) {}
        }
        $this->newMessage = '';
    }

    // ─── Status ────────────────────────────────────────────────────────────────

    public function toggleStatus($id)
    {
        $conv          = collect($this->conversations)->first(fn($c) => $c['id'] == $id);
        $currentStatus = $conv['status'] ?? ($this->activeConvData['status'] ?? 'open');
        $newStatus     = $currentStatus === 'open' ? 'resolved' : 'open';

        if ($this->source === 'chatwoot') {
            try { (new ChatwootService())->toggleStatus((int) $id, $newStatus); } catch (\Exception $e) {}
        } else {
            $c = CrmConversation::find($id);
            if ($c) { $c->status = $newStatus; $c->save(); }
        }

        // أزل المحادثة من القائمة الحالية فوراً حتى لا تبقى مرئية في التاب الخاطئ
        $this->conversations = collect($this->conversations)
            ->reject(fn($c) => $c['id'] == $id)
            ->values()->all();

        // إذا كانت هي المحادثة المفتوحة — أغلق نافذة الشات وانتقل للتاب الجديد
        if ($this->activeConversationId == $id) {
            $this->activeConversationId = null;
            $this->activeConvData       = null;
            $this->messages             = [];
        }

        $this->filterStatus = $newStatus;
        $this->loadConversations();
        $this->getUnreadCount();
    }

    // ─── Labels ────────────────────────────────────────────────────────────────

    public function toggleLabel(string $label)
    {
        if (!$this->activeConversationId) return;

        if (in_array($label, $this->activeConvLabels)) {
            $this->activeConvLabels = array_values(array_filter($this->activeConvLabels, fn($l) => $l !== $label));
        } else {
            $this->activeConvLabels[] = $label;
        }

        if ($this->source === 'chatwoot') {
            try { (new ChatwootService())->setConversationLabels($this->activeConversationId, $this->activeConvLabels); }
            catch (\Exception $e) {}
        }

        $labels = $this->activeConvLabels;
        $this->conversations = collect($this->conversations)->map(function ($c) use ($labels) {
            if ($c['id'] == $this->activeConversationId) $c['labels'] = $labels;
            return $c;
        })->values()->all();

        if ($this->activeConvData) $this->activeConvData['labels'] = $labels;
    }

    // ─── Priority ──────────────────────────────────────────────────────────────

    public function setPriority(string $priority)
    {
        if (!$this->activeConversationId) return;
        $this->activeConvPriority  = $priority;
        $this->showPriorityPicker  = false;

        if ($this->source === 'chatwoot') {
            try { (new ChatwootService())->setPriority($this->activeConversationId, $priority); }
            catch (\Exception $e) {}
        }

        if ($this->activeConvData) $this->activeConvData['priority'] = $priority;
        $this->conversations = collect($this->conversations)->map(function ($c) use ($priority) {
            if ($c['id'] == $this->activeConversationId) $c['priority'] = $priority;
            return $c;
        })->values()->all();
    }

    // ─── Assignment ────────────────────────────────────────────────────────────

    public function assignConversation(int $agentId, string $agentName): void
    {
        if (!$this->activeConversationId) return;

        if ($this->source === 'chatwoot') {
            try { (new ChatwootService())->assignConversation((int) $this->activeConversationId, $agentId); }
            catch (\Exception $e) {}
        }

        if ($agentId === 0) {
            ConversationAssignment::where('chatwoot_conv_id', $this->activeConversationId)->delete();
        } else {
            ConversationAssignment::updateOrCreate(
                ['chatwoot_conv_id' => $this->activeConversationId],
                ['conversation_id' => $this->activeConversationId, 'agent_name' => $agentName, 'assigned_at' => now()]
            );
        }

        $this->assignedAgentId   = $agentId;
        $this->assignedAgentName = $agentName;
        if ($this->activeConvData) {
            $this->activeConvData['assigned_agent']    = $agentName;
            $this->activeConvData['assigned_agent_id'] = $agentId;
        }
        $this->conversations = collect($this->conversations)->map(function ($c) use ($agentName, $agentId) {
            if ($c['id'] == $this->activeConversationId) {
                $c['assigned_agent'] = $agentName;
                $c['assigned_agent_id'] = $agentId;
            }
            return $c;
        })->values()->all();
    }

    public function assignTeam(int $teamId, string $teamName): void
    {
        if (!$this->activeConversationId || $this->source !== 'chatwoot') return;
        try { (new ChatwootService())->assignTeam((int) $this->activeConversationId, $teamId); }
        catch (\Exception $e) {}

        $this->assignedTeamId   = $teamId;
        $this->assignedTeamName = $teamName;
        if ($this->activeConvData) {
            $this->activeConvData['assigned_team']    = $teamName;
            $this->activeConvData['assigned_team_id'] = $teamId;
        }
        $this->conversations = collect($this->conversations)->map(function ($c) use ($teamName, $teamId) {
            if ($c['id'] == $this->activeConversationId) {
                $c['assigned_team'] = $teamName;
                $c['assigned_team_id'] = $teamId;
            }
            return $c;
        })->values()->all();
    }

    // ─── Mute ──────────────────────────────────────────────────────────────────

    public function toggleMute(): void
    {
        if (!$this->activeConversationId || $this->source !== 'chatwoot') return;
        $this->isMuted = !$this->isMuted;
        try {
            $svc = new ChatwootService();
            $this->isMuted
                ? $svc->muteConversation($this->activeConversationId)
                : $svc->unmuteConversation($this->activeConversationId);
        } catch (\Exception $e) {}
        if ($this->activeConvData) $this->activeConvData['muted'] = $this->isMuted;
    }

    // ─── Canned Responses ──────────────────────────────────────────────────────

    public function loadCannedResponses(): array
    {
        return CannedResponse::when($this->cannedSearch, fn($q) =>
            $q->where('title', 'LIKE', "%{$this->cannedSearch}%")
              ->orWhere('content', 'LIKE', "%{$this->cannedSearch}%")
        )->orderBy('title')->get()->map(fn($r) => [
            'id'      => $r->id,
            'title'   => $r->title,
            'content' => $r->content,
        ])->values()->all();
    }

    public function selectCannedResponse(int $id): void
    {
        $r = CannedResponse::find($id);
        if ($r) $this->newMessage = $r->content;
        $this->showCanned   = false;
        $this->cannedSearch = '';
    }

    // ─── Unread ────────────────────────────────────────────────────────────────

    public function getUnreadCount(): void
    {
        $this->unreadTotal = collect($this->conversations)->filter(fn($c) => ($c['unread'] ?? 0) > 0)->count();
    }

    // ─── Refresh ───────────────────────────────────────────────────────────────

    public function refreshAll()
    {
        $this->loadConversations();
        $this->getUnreadCount();

        if ($this->activeConversationId) {
            $found = collect($this->conversations)->first(fn($c) => $c['id'] == $this->activeConversationId);
            if ($found) {
                $this->activeConvData     = $found;
                $this->activeConvLabels   = $found['labels'] ?? $this->activeConvLabels;
                $this->activeConvPriority = $found['priority'] ?? $this->activeConvPriority;
                $this->isMuted            = $found['muted'] ?? $this->isMuted;
            }

            $secondsSinceLast = is_null($this->lastRefresh)
                ? PHP_INT_MAX
                : $this->lastRefresh->diffInSeconds(now());

            if ($secondsSinceLast > 5) {
                $this->loadMessages(true);
            }
        }
    }

    #[On('refresh-inbox')]
    public function refresh() { $this->refreshAll(); }

    // ─── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $filteredConversations = collect($this->conversations)
            ->when($this->searchQuery, function ($c) {
                $q = mb_strtolower($this->searchQuery);
                return $c->filter(fn($conv) =>
                    str_contains(mb_strtolower($conv['client_name']), $q) ||
                    str_contains($conv['client_phone'] ?? '', $q)
                );
            })->values()->all();

        $cannedResponses = $this->showCanned ? $this->loadCannedResponses() : [];

        return view('livewire.crm.inbox', compact('filteredConversations', 'cannedResponses'))
            ->layout('layouts.app');
    }
}
