<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id', 'campaign_id', 'position_id', 'status', 'start_date', 'end_date'];
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * RELATION : L'affectation concerne un employé.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * RELATION : L'affectation est liée à un rôle spécifique dans la campagne.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * RELATION HIÉRARCHIQUE : L'affectation a un manager (un autre employé).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * RELATION HIÉRARCHIQUE INVERSE : Voir les affectations gérées par cet employé.
     * (Utile pour voir tous les TC d'un SUP au sein de la table assignments)
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Assignment::class, 'manager_id', 'employee_id')
            ->where('campaign_id', $this->campaign_id);
    }

    /**
     * RELATION : Historique des changements pour cette affectation.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(AssignmentHistory::class, 'assignment_id');
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
