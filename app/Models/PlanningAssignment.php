<?php

namespace App\Models;
use App\Traits\RecordsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningAssignment extends Model
{
    use RecordsActivity;
    use HasFactory;
    protected $fillable = [
        'planning_model_id',
        'employee_id',
        'start_date',
        'status',
        'validated_by',
        'validated_at'
    ];

    public function planningModel() {
        return $this->belongsTo(PlanningModel::class);
    }
    public function employee() {
        return $this->hasMany(Employee::class);
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
