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

    public function mount()
    {
        $this->loadConversations();

        // افتح محادثة العميل تلقائياً لما يجي من ملف العميل
        if ($phone = request('phone')) {
            $foundId = $this->findConversationByPhone($phone);

            if ($foundId) {
                $this->selectConversation($foundId);
            } else {
                // ما في محادثة بعد → جهّز UI لبدء محادثة جديدة
                $this->pendingClientPhone = $phone;
                $this->pendingClientName  = request('name', '');
            }
        }
    }

    private function findConversationByPhone(string $phone): ?int
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // 1. البحث عبر Chatwoot contact search (أدق)
        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();

                // جرّب البحث بالرقم الكامل وبـ + أيضاً
                foreach ([$phone, '+' . ltrim($phone, '+')] as $searchTerm) {
                    $contact = $chatwoot->searchContact($searchTerm);
                    if ($contact && isset($contact['id'])) {
                        $convs = $chatwoot->getContactConversations((int) $contact['id']);
                        // خذ أحدث محادثة في نفس الـ inbox
                        $inboxId = config('chatwoot.inbox_id');
                        $found = collect($convs)
                            ->filter(fn($c) => ($c['inbox_id'] ?? 0) == $inboxId)
                            ->sortByDesc('last_activity_at')
                            ->first();

                        // إذا ما فلّينا بالـ inbox, خذ أي محادثة
                        if (!$found) {
                            $found = collect($convs)->sortByDesc('last_activity_at')->first();
                        }

                        if ($found) return (int) $found['id'];
                    }
                }
            } catch (\Exception $e) {}
        }

        // 2. مطابقة بالأرقام في المحادثات المحمّلة
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

    public function loadConversations()
    {
        // أولاً: جرّب Chatwoot
        try {
            $chatwoot = new ChatwootService();
            $raw = $chatwoot->getConversations();

            if (!empty($raw)) {
                $this->source = 'chatwoot';
                $this->conversations = collect($raw)->map(function ($c) {
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
                return [
                    'id'              => $c->id,
                    'status'          => $c->status,
                    'last_message_at' => $c->last_message_at?->toDateTimeString(),
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

        // دائماً أعد التحميل عشان نضمن البيانات موجودة
        $this->loadConversations();

        $this->activeConvData = collect($this->conversations)
            ->first(fn($c) => $c['id'] == $id);

        // إذا ما لقيناها في الـ source الحالي، ابنِ بيانات بسيطة
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
            ];
        }

        $this->loadMessages();

        // علّم المحادثة كمقروءة
        if ($this->source === 'chatwoot') {
            try {
                (new ChatwootService())->markAsRead((int) $id);
            } catch (\Exception $e) {}
        }

        // صفّر عداد الغير مقروء محلياً فوراً
        $this->conversations = collect($this->conversations)->map(function ($c) use ($id) {
            if ($c['id'] == $id) $c['unread'] = 0;
            return $c;
        })->values()->all();
    }

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

        // Local DB
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

    public function startNewConversation()
    {
        if (!$this->pendingClientPhone || empty(trim($this->newMessage))) return;

        if ($this->source === 'chatwoot') {
            try {
                $chatwoot = new ChatwootService();

                // ابحث عن contact أو أنشئ جديد
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
                        $this->newMessage        = '';
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

        // إذا في محادثة pending (عميل جديد)
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
                $currentConv = collect($this->conversations)->first(fn($c) => $c['id'] == $id);
                $currentStatus = $currentConv['status'] ?? 'open';
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

        // حدّث activeConvData بعد تغيير الحالة
        if ($this->activeConversationId) {
            $this->activeConvData = collect($this->conversations)
                ->first(fn($c) => $c['id'] == $this->activeConversationId);
        }
    }

    public function refreshAll()
    {
        $this->loadConversations();

        // بعد تحديث المحادثات، حدّث بيانات المحادثة النشطة
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

        return view('livewire.crm.inbox', compact('filteredConversations'))->layout('layouts.app');
    }
}
