<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanningModel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'monday_hours',
        'thursday_hours',
        'friday_hours',
        'saturday_hours',
        'sunday_hours',
        'total_hours',
        'created_by'
    ];
}
