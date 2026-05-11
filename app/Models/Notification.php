<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];
    protected $casts = [
        'data' => 'array',        // Transforme le JSON en tableau PHP automatiquement
        'read_at' => 'datetime',  // Permet d'utiliser Carbon pour les dates
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
