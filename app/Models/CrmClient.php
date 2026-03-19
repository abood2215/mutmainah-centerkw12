<?php

namespace App\Models;

use App\Traits\CrmHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmClient extends Model
{
    use HasFactory, SoftDeletes, CrmHelpers;

    protected $fillable = [
        'name', 'phone', 'email', 'source', 'stage', 'priority', 
        'assigned_to', 'notes', 'chatwoot_contact_id', 'deal_value'
    ];

    /**
     * Get the constant stages for CRM Pipeline.
     */
    public static function getStages(): array
    {
        return [
            'new'       => ['name' => 'جديد',       'color' => '#6366F1'],
            'contacted' => ['name' => 'تم التواصل', 'color' => '#3B82F6'],
            'interested'=> ['name' => 'مهتم',        'color' => '#F59E0B'],
            'booked'    => ['name' => 'محجوز',       'color' => '#8B5CF6'],
            'active'    => ['name' => 'نشط',         'color' => '#10B981'],
            'followup'  => ['name' => 'متابعة',      'color' => '#F97316'],
            'completed' => ['name' => 'مكتمل',       'color' => '#64748B'],
        ];
    }

    public function activityLogs()
    {
        return $this->hasMany(CrmActivityLog::class, 'client_id');
    }

    public function tasks()
    {
        return $this->hasMany(CrmTask::class, 'client_id');
    }

    public function notes()
    {
        return $this->hasMany(CrmNote::class, 'client_id');
    }

    public function conversations()
    {
        return $this->hasMany(CrmConversation::class, 'client_id');
    }
}
