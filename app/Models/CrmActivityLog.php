<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmActivityLog extends Model {
    protected $fillable = ['client_id', 'performed_by', 'action', 'metadata'];
    protected $casts = ['metadata' => 'json'];

    public function client() {
        return $this->belongsTo(CrmClient::class, 'client_id');
    }
}
