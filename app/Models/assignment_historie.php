<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment_historie extends Model
{
    protected $fillable = ['planning_assignment_id','old_status','new_status','changed_by','reason','created_at'];
}
