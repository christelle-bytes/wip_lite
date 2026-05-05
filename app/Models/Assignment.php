<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
     protected $fillable = ['employee_id','campaign_id','position_id','status','start_date','end_date'];
}
