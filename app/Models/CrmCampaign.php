<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmCampaign extends Model {
    protected $fillable = ['title', 'message', 'type', 'status', 'target_filter', 'target_value', 'scheduled_at', 'sent_at', 'recipients_count', 'replies_count', 'created_by'];
    protected $casts = ['scheduled_at' => 'datetime', 'sent_at' => 'datetime'];

    public function resolveTargetClients() {
        $query = CrmClient::query();
        switch($this->target_filter) {
            case 'stage': return $query->where('stage', $this->target_value);
            case 'source': return $query->where('source', $this->target_value);
            case 'consultant': return $query->where('assigned_to', $this->target_value);
            case 'inactive': return $query->where('updated_at', '<=', now()->subDays((int)$this->target_value));
            case 'specific': return $query->whereIn('phone', explode(',', $this->target_value));
            default: return $query;
        }
    }
}
