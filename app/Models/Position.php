<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(employee::class);
    }

    public function logs()
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }

    public function notif()
    {
        return $this->morphMany(Notification::class, 'model');
    }
}
