<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return optional($this->role)->name === strtoupper($role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCP(): bool
    {
        return $this->hasRole('cp');
    }

    public function isSUP(): bool
    {
        return $this->hasRole('sup');
    }

    public function isTC(): bool
    {
        return $this->hasRole('tc');
    }
    public function notif()
    {
        return $this->morphMany(Notification::class, 'model');
    }
}
