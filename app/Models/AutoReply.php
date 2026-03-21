<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    protected $fillable = [
        'trigger',
        'message',
        'is_active',
        'last_sent_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_sent_at' => 'datetime',
    ];
}
