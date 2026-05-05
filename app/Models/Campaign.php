<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = ['name','description','start_date','end_date','status'];

     public function logs()
{
    return $this->morphMany(ActivityLog::class, 'model');
}

}
