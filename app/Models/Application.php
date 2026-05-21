<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Support\ApplicationCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'minecraft_nick',
        'age',
        'country',
        'timezone',
        'available_schedule',
        'admin_response',
        'reviewed_by',
        'reviewed_at',
        'selected_announced_at',
        'cooldown_until',
        'correction_requested',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',
            'selected_announced_at' => 'datetime',
            'cooldown_until' => 'datetime',
            'correction_requested' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ApplicationCategory::class, 'type', 'slug');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApplicationLog::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(ApplicationInterview::class);
    }

    public function discordNotifications(): HasMany
    {
        return $this->hasMany(DiscordNotification::class);
    }

    public function canBeCancelledByUser(): bool
    {
        return in_array($this->status, [ApplicationStatus::Pending, ApplicationStatus::InReview], true);
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function typeLabel(): string
    {
        return ApplicationCatalog::types(true)[$this->type] ?? str($this->type)->replace(['-', '_'], ' ')->title()->toString();
    }
}
