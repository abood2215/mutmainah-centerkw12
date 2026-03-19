<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmMessage extends Model {
    protected $fillable = ['conversation_id', 'chatwoot_id', 'direction', 'message_type', 'content', 'sent_at'];
    protected $casts = ['sent_at' => 'datetime'];

    public function conversation() {
        return $this->belongsTo(CrmConversation::class, 'conversation_id');
    }
}
