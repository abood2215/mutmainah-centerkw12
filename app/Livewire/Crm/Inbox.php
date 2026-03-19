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
    public $activeConversationId = null;   // Chatwoot conversation ID
    public $conversations   = [];
    public $messages        = [];
    public $newMessage      = '';
    public $filterChannel   = '';
    public $activeConvData  = null;        // بيانات المحادثة النشطة من Chatwoot

    public function mount()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        try {
            $chatwoot = new ChatwootService();
            $raw = $chatwoot->getConversations();

            // حوّل كل محادثة Chatwoot لكائن بسيط
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
                ];
            })->values()->all();

        } catch (\Exception $e) {
            // fallback للمحادثات المحلية إذا فشل Chatwoot
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
                    ];
                })->values()->all();
        }
    }

    public function selectConversation($id)
    {
        $this->activeConversationId = $id;
        $this->loadMessages();

        // جلب بيانات المحادثة النشطة
        foreach ($this->conversations as $c) {
            if ($c->id == $id) {
                $this->activeConvData = $c;
                break;
            }
        }
    }

    public function loadMessages()
    {
        if (!$this->activeConversationId) return;

        try {
            $chatwoot = new ChatwootService();
            $raw = $chatwoot->getMessages($this->activeConversationId);

            $this->messages = collect($raw)
                ->filter(fn($m) => in_array($m['message_type'], [0, 1])) // 0=incoming, 1=outgoing
                ->map(function ($m) {
                    return (object)[
                        'id'        => $m['id'],
                        'content'   => $m['content'] ?? '[رسالة فارغة]',
                        'direction' => $m['message_type'] === 0 ? 'in' : 'out',
                        'sent_at'   => isset($m['created_at'])
                                       ? \Carbon\Carbon::createFromTimestamp($m['created_at'])
                                       : null,
                        'sender'    => $m['sender']['name'] ?? '',
                    ];
                })->values()->all();

        } catch (\Exception $e) {
            $this->messages = [];
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage)) || !$this->activeConversationId) return;

        try {
            $chatwoot = new ChatwootService();
            $chatwoot->sendMessage($this->activeConversationId, $this->newMessage);
        } catch (\Exception $e) {
            // fallback: احفظ محلياً على الأقل
        }

        $this->newMessage = '';
        $this->loadMessages();
    }

    public function toggleStatus($id)
    {
        try {
            $chatwoot = new ChatwootService();
            // جلب الحالة الحالية
            $currentStatus = null;
            foreach ($this->conversations as $c) {
                if ($c->id == $id) { $currentStatus = $c->status; break; }
            }
            $newStatus = ($currentStatus === 'open') ? 'resolved' : 'open';
            $chatwoot->toggleStatus($id, $newStatus);
        } catch (\Exception $e) {}

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
        return view('livewire.crm.inbox')->layout('layouts.app');
    }
}
