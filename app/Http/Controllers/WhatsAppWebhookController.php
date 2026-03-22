<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CrmClient;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\CrmActivityLog;
use App\Models\CrmCampaignRecipient;
use App\Services\ChatwootService;

class WhatsAppWebhookController
{
    /**
     * GET /webhooks/whatsapp/verify
     * Meta يتصل بهذا الرابط للتحقق من الـ Webhook
     */
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * POST /webhooks/whatsapp/incoming
     * يستقبل الرسائل الواردة من WhatsApp Cloud API
     */
    public function incoming(Request $request)
    {
        $payload = $request->all();

        // تأكد إن الحدث من WhatsApp
        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return response('ok', 200);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {

                $value = $change['value'] ?? [];

                // ===== معالجة الرسائل الواردة =====
                foreach ($value['messages'] ?? [] as $msg) {
                    $this->handleIncomingMessage($value['metadata'] ?? [], $msg, $value['contacts'] ?? []);
                }

                // ===== معالجة تحديثات حالة الإرسال (delivered, read, failed) =====
                foreach ($value['statuses'] ?? [] as $status) {
                    $this->handleStatusUpdate($status);
                }
            }
        }

        return response('ok', 200);
    }

    // -----------------------------------------------------------------------

    private function handleIncomingMessage(array $metadata, array $msg, array $contacts): void
    {
        $from    = $msg['from'] ?? null;   // رقم هاتف المرسل (مثال: 96279911xxxx)
        $msgId   = $msg['id']   ?? null;
        $text    = $msg['text']['body'] ?? ($msg['caption'] ?? '[محتوى غير نصي]');
        $sentAt  = isset($msg['timestamp']) ? \Carbon\Carbon::createFromTimestamp($msg['timestamp']) : now();

        if (!$from) return;

        // ابحث عن العميل بالهاتف
        $client = CrmClient::where('phone', 'LIKE', "%{$from}%")->first();
        $isNewClient = false;

        // إذا لم يوجد العميل — أنشئه تلقائياً
        if (!$client) {
            $isNewClient = true;
            $contactName = $contacts[0]['profile']['name'] ?? "واتساب {$from}";
            $client = CrmClient::create([
                'name'   => $contactName,
                'phone'  => $from,
                'source' => 'whatsapp',
                'stage'  => 'new',
            ]);

            CrmActivityLog::create([
                'client_id'    => $client->id,
                'performed_by' => 1,
                'action'       => 'client_created',
                'metadata'     => ['msg' => 'أنشئ تلقائياً من واتساب', 'phone' => $from],
            ]);
        }

        // ابحث عن محادثة مفتوحة أو أنشئ واحدة
        $conversation = CrmConversation::where('client_id', $client->id)
            ->where('channel', 'whatsapp')
            ->where('status', 'open')
            ->first();

        if (!$conversation) {
            $conversation = CrmConversation::create([
                'client_id'       => $client->id,
                'channel'         => 'whatsapp',
                'status'          => 'open',
                'last_message_at' => $sentAt,
            ]);
        }

        // تجنب الرسائل المكررة
        $exists = CrmMessage::where('chatwoot_id', $msgId)->exists();
        if ($exists) return;

        // احفظ الرسالة
        CrmMessage::create([
            'conversation_id' => $conversation->id,
            'chatwoot_id'     => $msgId,
            'direction'       => 'in',
            'message_type'    => $msg['type'] ?? 'text',
            'content'         => $text,
            'sent_at'         => $sentAt,
        ]);

        // حدّث وقت آخر رسالة
        $conversation->update(['last_message_at' => $sentAt]);

        // إذا عميل جديد — أرسل له رسالة ترحيبية تلقائية
        if ($isNewClient) {
            $this->sendWelcomeMessage($client->name, $from);
        }

        // إذا كانت الرسالة رداً على حملة — سجّل الرد
        $this->markCampaignReply($client->id, $sentAt);
    }

    private function sendWelcomeMessage(string $name, string $phone): void
    {
        $message = "مرحباً {$name} 👋\nشكراً لتواصلك مع بيوت محبة!\nتم استلام رسالتك وسيتواصل معك أحد فريقنا في أقرب وقت ممكن. ✨";

        try {
            (new ChatwootService())->sendToPhone($name, $phone, $message);
        } catch (\Exception $e) {
            // silent fail — لا نوقف معالجة الرسالة بسبب خطأ في الترحيب
        }
    }

    private function handleStatusUpdate(array $status): void
    {
        // يمكن لاحقاً ربطها بحالة التسليم
        // $status['status'] = sent | delivered | read | failed
        // $status['id']     = message_id
    }

    private function markCampaignReply(int $clientId, $repliedAt): void
    {
        $recipient = CrmCampaignRecipient::where('client_id', $clientId)
            ->whereNull('replied_at')
            ->latest('sent_at')
            ->first();

        if ($recipient) {
            $recipient->update(['replied_at' => $repliedAt]);

            // حدّث عداد الردود على الحملة
            $recipient->campaign()->increment('replies_count');
        }
    }
}
