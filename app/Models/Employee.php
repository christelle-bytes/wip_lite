<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'matricule',
        'first_name',
        'last_name',
        'birth_date',
        'phone',
        'email',
        'address',
        'position_id',
        'salary_base',
        'status',
    ];
    protected $casts = [
        'birth_date' => 'date',
        'salary_base' => 'decimal:2',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

}
