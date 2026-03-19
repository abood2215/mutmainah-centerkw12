<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmNote extends Model {
    protected $fillable = ['client_id', 'author_id', 'content'];

    public function client() {
        return $this->belongsTo(CrmClient::class, 'client_id');
    }
}
