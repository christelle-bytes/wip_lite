<?php

namespace App\Models;
use App\Traits\RecordsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningModel extends Model
{
    use RecordsActivity;
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'monday_hours',
        'tuesday_hours',
        'wednesday_hours',
        'thursday_hours',
        'friday_hours',
        'saturday_hours',
        'sunday_hours',
        'total_hours',
        'created_by',
        'status'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function planningAssignment(): HasMany{
        return $this->hasMany(PlanningAssignment::class);
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
