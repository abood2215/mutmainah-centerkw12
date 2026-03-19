<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignRecipient;
use App\Services\ChatwootService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| CRM Campaign Scheduler — يرسل عبر Chatwoot → WhatsApp
|--------------------------------------------------------------------------
*/
Schedule::call(function () {

    $campaigns = CrmCampaign::where('status', 'scheduled')
                             ->where('scheduled_at', '<=', now())
                             ->get();

    if ($campaigns->isEmpty()) return;

    $chatwoot = new ChatwootService();

    foreach ($campaigns as $campaign) {

        $clients = $campaign->resolveTargetClients()->get();
        $sent    = 0;

        foreach ($clients as $client) {
            if (empty($client->phone)) continue;

            try {
                $success = $chatwoot->sendToPhone(
                    $client->name,
                    $client->phone,
                    $campaign->message
                );

                if ($success) {
                    CrmCampaignRecipient::create([
                        'campaign_id' => $campaign->id,
                        'client_id'   => $client->id,
                        'sent_at'     => now(),
                    ]);
                    $sent++;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Campaign [{$campaign->id}] failed for client [{$client->id}]: " . $e->getMessage());
            }
        }

        $campaign->update([
            'status'           => 'sent',
            'sent_at'          => now(),
            'recipients_count' => $sent,
        ]);
    }

})->name('crm-send-campaigns')->everyMinute();
