<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\ChatwootService;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\CrmClient;

class Inbox extends Component
{
    public $activeConversationId = null;
    public $conversations   = [];
    public $messages        = [];
    public $newMessage      = '';
    public $filterChannel   = '';
    public $filterStatus    = '';
    public $searchQuery     = '';
    public $activeConvData  = null;
    public $source          = 'local'; // local | chatwoot

    public function mount()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        // أولاً: جرّب Chatwoot
        try {
            $chatwoot = new ChatwootService();
            $raw = $chatwoot->getConversations();

            if (!empty($raw)) {
                $this->source = 'chatwoot';
                $this->conversations = collect($raw)->map(function ($c) {
                    return (object)[
                        'id'              => $c['id'],
                        'status'          => $c['status'],
                        'last_message_at' => isset($c['last_activity_at'])
                                             ? \Carbon\Carbon::createFromTimestamp($c['last_activity_at'])
                                             : null,
                        'channel'         => 'whatsapp',
                        'client_name'     => $c['meta']['sender']['name'] ?? 'مجهول',
                        'client_phone'    => $c['meta']['sender']['phone_number'] ?? '',
                        'unread'          => $c['unread_count'] ?? 0,
                        'conv_source'     => 'chatwoot',
                    ];
                })->values()->all();
                return;
            }
        } catch (\Exception $e) {}

        // Fallback: من قاعدة البيانات المحلية
        $this->source = 'local';
        $this->conversations = CrmConversation::with('client')
            ->when($this->filterChannel, fn($q) => $q->where('channel', $this->filterChannel))
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($c) {
                return (object)[
                    'id'              => $c->id,
                    'status'          => $c->status,
                    'last_message_at' => $c->last_message_at,
                    'channel'         => $c->channel,
                    'client_name'     => $c->client->name ?? '—',
                    'client_phone'    => $c->client->phone ?? '',
                    'unread'          => 0,
                    'conv_source'     => 'local',
                ];
            })->values()->all();
    }

    public function selectConversation($id)
    {
        $this->activeConversationId = $id;
        $this->activeConvData = null;

        foreach ($this->conversations as $c) {
            if ($c->id == $id) {
                $this->activeConvData = $c;
                break;
            }
        }

        $this->loadMessages();
    }

    public function loadMessages()
    {
        if (!$this->activeConversationId) return;

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();
                $raw = $chatwoot->getMessages($this->activeConversationId);

                $this->messages = collect($raw)
                    ->filter(fn($m) => in_array($m['message_type'], [0, 1]))
                    ->map(function ($m) {
                        return (object)[
                            'id'        => $m['id'],
                            'content'   => $m['content'] ?? '[رسالة فارغة]',
                            'direction' => $m['message_type'] === 0 ? 'in' : 'out',
                            'sent_at'   => isset($m['created_at'])
                                           ? \Carbon\Carbon::createFromTimestamp($m['created_at'])
                                           : null,
                        ];
                    })->values()->all();
                return;
            } catch (\Exception $e) {}
        }

        // Local DB
        $this->messages = CrmMessage::where('conversation_id', $this->activeConversationId)
            ->orderBy('sent_at', 'asc')
            ->get()
            ->map(function ($m) {
                return (object)[
                    'id'        => $m->id,
                    'content'   => $m->content,
                    'direction' => $m->direction,
                    'sent_at'   => $m->sent_at,
                ];
            })->values()->all();
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage)) || !$this->activeConversationId) return;

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();
                $chatwoot->sendMessage($this->activeConversationId, $this->newMessage);
            } catch (\Exception $e) {}
        } else {
            // إرسال عبر WhatsApp API مباشرة
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

        $this->newMessage = '';
        $this->loadMessages();
    }

    public function toggleStatus($id)
    {
        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();
                $currentStatus = $this->activeConvData?->status ?? 'open';
                $newStatus = $currentStatus === 'open' ? 'resolved' : 'open';
                $chatwoot->toggleStatus($id, $newStatus);
            } catch (\Exception $e) {}
        } else {
            $conv = CrmConversation::find($id);
            if ($conv) {
                $conv->status = $conv->status === 'open' ? 'resolved' : 'open';
                $conv->save();
            }
        }

        $this->loadConversations();
    }

    #[On('refresh-inbox')]
    public function refresh()
    {
        $this->loadConversations();
        $this->loadMessages();
    }

    public function render()
    {
        $filteredConversations = collect($this->conversations)
            ->when($this->filterStatus, fn($c) => $c->filter(fn($conv) => $conv->status === $this->filterStatus))
            ->when($this->searchQuery, function ($c) {
                $q = mb_strtolower($this->searchQuery);
                return $c->filter(fn($conv) =>
                    str_contains(mb_strtolower($conv->client_name), $q) ||
                    str_contains($conv->client_phone, $q)
                );
            })
            ->values()->all();

        return view('livewire.crm.inbox', compact('filteredConversations'))->layout('layouts.app');
    }
}
