<?php

namespace App\Models;
use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Campaign extends Model
{
    use HasFactory;
    use RecordsActivity;
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

    public function notif()
    {
        return $this->morphMany(Notification::class, 'model');
    }


}
