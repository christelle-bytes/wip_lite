<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait RecordsActivity
{
    // Laravel appelle automatiquement boot[NomDuTrait]
    protected static function bootRecordsActivity()
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                if (method_exists($model, 'logs')) {
                    $model->logs()->create([
                        'user_id'     => Auth::id(),
                        'action'      => $event,
                        'description' => "Modèle " . class_basename($model) . " a été $event.",
                        'ip_address'  => request()->ip(),
                    ]);
                }
            });
        }
    }
}
