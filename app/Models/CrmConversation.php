<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmConversation extends Model {
    protected $fillable = ['client_id', 'chatwoot_id', 'channel', 'status', 'subject', 'assigned_to', 'last_message_at'];
    protected $casts = ['last_message_at' => 'datetime'];

    public function client() {
        return $this->belongsTo(CrmClient::class, 'client_id');
    }

    public function messages() {
        return $this->hasMany(CrmMessage::class, 'conversation_id');
    }
}
