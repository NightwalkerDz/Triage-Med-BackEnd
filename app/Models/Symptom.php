<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Symptom extends Model
{
    protected $fillable = [
        'code',
        'label',
        'urgency_level_id',
        'actif',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function urgencyLevel(): BelongsTo
    {
        return $this->belongsTo(UrgencyLevel::class);
    }
}
