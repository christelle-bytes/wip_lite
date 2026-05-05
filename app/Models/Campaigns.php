<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaigns extends Model
{
    protected $fillable = ['name','description','start_date','end_date','status'];
    
}
