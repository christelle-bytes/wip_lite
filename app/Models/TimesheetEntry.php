<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'timesheet_id',
        'date',
        'check_in',
        'check_out',
        'break_duration',
        'total_hours',
        'planned_hours',
        'overtime_hours',
        'absence_type',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'time:H:i',
        'check_out' => 'time:H:i',
        'break_duration' => 'integer',
        'total_hours' => 'decimal:2',
        'planned_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function logs()
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }

    public function scopeForTimesheet($query, Timesheet $timesheet)
    {
        return $query->where('timesheet_id', $timesheet->id);
    }

    public function notif()
    {
        return $this->morphMany(Notification::class, 'model');
    }
}
