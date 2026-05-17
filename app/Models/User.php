<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'manager_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            UserRole::Admin => 'admin.dashboard',
            UserRole::Manager => 'manager.dashboard',
            default => 'employee.dashboard',
        };
    }
}
