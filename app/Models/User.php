<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['email', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use RecordsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function logs()
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }
    public function employee():HasOne{
      return $this->hasOne(Employee::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class)->withDefault([
            'name' => 'Invité'
        ]);
    }

    public function hasRole(string $role): bool
    {
        return strtoupper(optional($this->role)->name) === strtoupper($role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }



    public function isCP(): bool
    {
        return $this->hasRole('CP');
    }

    public function isSUP(): bool
    {
        return $this->hasRole('SUP');
    }

    public function isTC(): bool
    {
        return $this->hasRole('TC');
    }
    public function notif()
    {
        return $this->morphMany(Notification::class, 'model');
    }
}
