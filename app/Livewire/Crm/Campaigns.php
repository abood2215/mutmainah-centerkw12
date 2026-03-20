<?php

namespace App\Livewire\Crm;

use Livewire\Component;
use App\Models\CrmCampaign;
use App\Models\CrmClient;
use App\Models\CrmCampaignRecipient;
use App\Services\ChatwootService;

class Campaigns extends Component
{
    public $title = '';
    public $message = '';
    public $type = 'promotional';
    public $targetFilter = 'all';
    public $targetValue = '';
    public $scheduledAt = '';
    
    public $campaigns;

    public function mount()
    {
        $this->loadCampaigns();
    }

    public function loadCampaigns()
    {
        $this->campaigns = CrmCampaign::orderBy('created_at', 'desc')->get();
    }

    public function createCampaign()
    {
        $this->validate([
            'title' => 'required|min:3',
            'message' => 'required',
        ]);

        CrmCampaign::create([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'target_filter' => $this->targetFilter,
            'target_value' => $this->targetValue,
            'scheduled_at' => $this->scheduledAt ?: null,
            'status' => $this->scheduledAt ? 'scheduled' : 'draft',
            'created_by' => auth()->id() ?? 1
        ]);

        $this->reset(['title', 'message', 'type', 'targetFilter', 'targetValue', 'scheduledAt']);
        $this->loadCampaigns();
        session()->flash('success', 'تم إنشاء الحملة بنجاح');
    }

    public function sendCampaign($campaignId)
    {
        $campaign = CrmCampaign::find($campaignId);
        if (!$campaign || $campaign->status === 'sent') return;

        $clients = $campaign->resolveTargetClients()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        $sent = 0;
        try {
            $chatwoot = new ChatwootService();
            foreach ($clients as $client) {
                try {
                    $chatwoot->sendToPhone($client->name, $client->phone, $campaign->message);
                    $sent++;
                    // تأخير بسيط لتجنب flood
                    usleep(300000); // 0.3 ثانية
                } catch (\Exception $e) {}
            }
        } catch (\Exception $e) {}

        $campaign->update([
            'status'           => 'sent',
            'sent_at'          => now(),
            'recipients_count' => $sent,
        ]);

        $this->loadCampaigns();
        session()->flash('success', "تم إرسال الحملة لـ {$sent} عميل");
    }

    public function deleteCampaign($campaignId)
    {
        CrmCampaign::where('id', $campaignId)->whereNotIn('status', ['sent'])->delete();
        $this->loadCampaigns();
    }

    public function getAudienceCountProperty()
    {
        $campaign = new CrmCampaign([
            'target_filter' => $this->targetFilter,
            'target_value' => $this->targetValue
        ]);
        return $campaign->resolveTargetClients()->count();
    }

    public function render()
    {
        return view('livewire.crm.campaigns')->layout('layouts.app');
    }
}
