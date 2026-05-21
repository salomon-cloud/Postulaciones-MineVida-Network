<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'discord_id',
        'discord_username',
        'discord_global_name',
        'discord_avatar',
        'discord_access_token',
        'discord_refresh_token',
        'role',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'discord_access_token' => 'encrypted',
            'discord_refresh_token' => 'encrypted',
            'role' => UserRole::class,
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function reviewedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'reviewed_by');
    }

    public function applicationNotes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class, 'admin_id');
    }

    public function assignedInterviews(): HasMany
    {
        return $this->hasMany(ApplicationInterview::class, 'interviewer_id');
    }

    public function isAtLeast(UserRole $role): bool
    {
        $current = $this->role instanceof UserRole ? $this->role : UserRole::from($this->role);

        return $current->level() >= $role->level();
    }

    public function isReviewer(): bool
    {
        return $this->isAtLeast(UserRole::Reviewer);
    }

    public function isAdmin(): bool
    {
        return $this->isAtLeast(UserRole::Admin);
    }

    public function isOwner(): bool
    {
        return $this->isAtLeast(UserRole::Owner);
    }

    public function discordAvatarUrl(): ?string
    {
        if (! $this->discord_id || ! $this->discord_avatar) {
            return null;
        }

        return "https://cdn.discordapp.com/avatars/{$this->discord_id}/{$this->discord_avatar}.png?size=128";
    }
}
