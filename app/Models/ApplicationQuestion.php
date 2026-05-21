<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationQuestion extends Model
{
    protected $fillable = [
        'category_id',
        'key',
        'label',
        'input_type',
        'placeholder',
        'help_text',
        'options',
        'rules',
        'step',
        'is_required',
        'is_answer',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'rules' => 'array',
            'step' => 'integer',
            'is_required' => 'boolean',
            'is_answer' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ApplicationCategory::class, 'category_id');
    }

    public static function inputTypes(): array
    {
        return [
            'text' => 'Texto corto',
            'number' => 'Numero',
            'textarea' => 'Texto largo',
            'textarea_urls' => 'Lista de links',
            'url' => 'URL',
            'select' => 'Seleccion',
            'checkbox' => 'Casilla',
        ];
    }
}
