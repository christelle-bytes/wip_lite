<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'status',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'validated_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validated_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class);
    }

    public function scopeForEmployee($query, Employee $employee)
    {
        return $query->where('employee_id', $employee->id);
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
