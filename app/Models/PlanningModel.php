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

    protected $appends = ['status'];

    public function getStatusAttribute()
    {
        if (array_key_exists('active_assignment_count', $this->attributes)) {
            return $this->attributes['active_assignment_count'] > 0 ? 'actif' : 'inactif';
        }

        if ($this->relationLoaded('planningAssignment')) {
            return $this->planningAssignment->whereIn('status', ['validé', 'suspendu'])->isNotEmpty() ? 'actif' : 'inactif';
        }

        return $this->planningAssignment()
            ->whereIn('status', ['validé', 'suspendu'])
            ->exists()
            ? 'actif'
            : 'inactif';
    }

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
