<?php
namespace App\Models;
use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;
    use RecordsActivity;

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
        'birth_date'  => 'date',
        'salary_base' => 'decimal:2',
    ];


    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class); // ← majuscule corrigée
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    // ── Relation assignments ──────────────────────────────────────────────────
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    // Assignments actifs uniquement
    public function activeAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class)->where('status', 'actif');
    }

    // Employés sous gestion (SUPs ou TCs dont manager_id = cet employé)
    public function subordinates(): HasMany
    {
        return $this->hasMany(Assignment::class, 'manager_id');
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