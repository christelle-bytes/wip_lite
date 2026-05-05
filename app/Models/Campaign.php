<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Campaign extends Model
{
    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'status'];
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
   public function employees(): HasManyThrough
    {
        return $this->hasManyThrough(
            Employee::class,
            Assignment::class,
            'campaign_id',
            'id',
            'id',
            'employee_id'
        );
    }
    public function logs()
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }

}
