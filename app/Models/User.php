<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'last_seen_at',
        'chatwoot_agent_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'     => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    /**
     * online  = last seen within 5 minutes
     * away    = last seen 5–15 minutes ago
     * offline = last seen more than 15 minutes ago OR never seen
     */
    public function getStatusAttribute(): string
    {
        if (!$this->last_seen_at) {
            return 'offline';
        }

        $diffMinutes = $this->last_seen_at->diffInMinutes(Carbon::now());

        if ($diffMinutes < 5) {
            return 'online';
        }

        if ($diffMinutes <= 15) {
            return 'away';
        }

        return 'offline';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'online'  => '🟢 متاح',
            'away'    => '🟡 بعيد',
            default   => '🔴 غائب',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'online'  => 'emerald',
            'away'    => 'amber',
            default   => 'slate',
        };
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
