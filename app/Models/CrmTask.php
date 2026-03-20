<?php

namespace App\Models;

use App\Traits\CrmHelpers;
use Illuminate\Database\Eloquent\Model;

class CrmTask extends Model
{
    use CrmHelpers;

    protected $fillable = ['client_id', 'assigned_to', 'title', 'description', 'due_date', 'priority', 'status'];
    protected $casts    = ['due_date' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(CrmClient::class, 'client_id');
    }
}
