<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'summary',
        'description',
        'icon',
        'accent_color',
        'minimum_age',
        'is_open',
        'closed_until',
        'closed_message',
        'sort_order',
        'steps',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'closed_until' => 'datetime',
            'minimum_age' => 'integer',
            'sort_order' => 'integer',
            'steps' => 'array',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ApplicationQuestion::class, 'category_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'type', 'slug');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
