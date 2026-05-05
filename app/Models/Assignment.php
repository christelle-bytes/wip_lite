<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
     protected $fillable = ['employee_id', 'campaign_id', 'position_id', 'status', 'start_date', 'end_date'];

     public function logs()
     {
          return $this->morphMany(ActivityLog::class, 'model');
     }

     public function notif()
     {
          return $this->morphMany(Notification::class, 'model');
     }
}
