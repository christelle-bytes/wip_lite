<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class timesheet_historie extends Model
{
    protected $fillable = ['timesheet_id','employee_id','old_statuts','new_status','changed_by','reason','created_at'];
}
