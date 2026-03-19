<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CrmClient;
use App\Models\CrmTask;
use App\Models\CrmNote;
use App\Models\CrmActivityLog;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\CrmCampaign;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Clients
        $clients = [
            ['name' => 'أحمد عبدالله', 'phone' => '962799112233', 'source' => 'whatsapp', 'stage' => 'new', 'deal_value' => 1500],
            ['name' => 'سارة المحيسن', 'phone' => '962788554433', 'source' => 'instagram', 'stage' => 'booked', 'deal_value' => 2800],
            ['name' => 'محمد العمري', 'phone' => '962777332211', 'source' => 'referral', 'stage' => 'interested', 'deal_value' => 500],
            ['name' => 'ليلى فريحات', 'phone' => '962791122334', 'source' => 'whatsapp', 'stage' => 'active', 'deal_value' => 4500],
            ['name' => 'خالد الصقور', 'phone' => '962781199554', 'source' => 'whatsapp', 'stage' => 'followup', 'deal_value' => 1200],
        ];

        foreach ($clients as $c) {
            $client = CrmClient::create($c);

            // Create Activity Log
            CrmActivityLog::create([
                'client_id' => $client->id,
                'action' => 'client_created',
                'performed_by' => 1,
                'metadata' => ['msg' => 'تم استيراد العميل بنجاح']
            ]);

            // Create Note
            CrmNote::create([
                'client_id' => $client->id,
                'author_id' => 1,
                'content' => 'عميل محتمل جداً، يرجى المتابعة بخصوص الخدمات التخصصية.'
            ]);

            // Create Task
            CrmTask::create([
                'client_id' => $client->id,
                'assigned_to' => 1,
                'title' => 'التواصل الأولي مع العميل',
                'description' => 'شرح الخدمات وباقات الأسعار المتوفرة حالياً.',
                'due_date' => now()->addDays(2),
                'priority' => 'high',
                'status' => 'pending'
            ]);

            // Create Conversation
            $conv = CrmConversation::create([
                'client_id' => $client->id,
                'channel' => 'whatsapp',
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            CrmMessage::create([
                'conversation_id' => $conv->id,
                'direction' => 'in',
                'content' => 'مرحباً، هل يمكنني الاستفسار عن برامج التقييم؟',
                'sent_at' => now()->subHours(1),
            ]);
        }

        // Create Campaigns
        CrmCampaign::create([
            'title' => 'حملة رمضان المبارك 2026',
            'message' => 'خصم 20% على باقات التقييم الشاملة طيلة شهر رمضان. بيوت محبة ترعاكم.',
            'type' => 'promotional',
            'status' => 'sent',
            'target_filter' => 'all',
            'sent_at' => now()->subDays(1),
            'recipients_count' => 150,
            'replies_count' => 12,
            'created_by' => 1
        ]);
    }
}
