<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planning_assignments extends Model
{
    protected $fillable = [
        'planning_model_id',
        'employee_id',
        'start_date',
        'status',
        'validated_by',
        'validated_at'
    ];
}
