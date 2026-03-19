<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmCampaignRecipient extends Model {
    protected $fillable = ['campaign_id', 'client_id', 'chatwoot_conversation_id', 'sent_at', 'replied_at'];
    protected $casts = ['sent_at' => 'datetime', 'replied_at' => 'datetime'];

    public function client() {
        return $this->belongsTo(CrmClient::class, 'client_id');
    }

    public function campaign() {
        return $this->belongsTo(CrmCampaign::class, 'campaign_id');
    }
}
