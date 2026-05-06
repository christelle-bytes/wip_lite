<?php

namespace App\Models;
use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;
    use RecordsActivity;
    protected $fillable = [
        'user_id',
        'matricule',
        'first_name',
        'last_name',
        'birth_date',
        'phone',
        'email',
        'address',
        'position_id',
        'salary_base',
        'status',
    ];
    protected $casts = [
        'birth_date' => 'date',
        'salary_base' => 'decimal:2',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(position::class);
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
