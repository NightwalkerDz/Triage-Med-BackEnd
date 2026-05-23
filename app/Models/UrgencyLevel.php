<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UrgencyLevel extends Model
{
    protected $fillable = [
        'code',
        'label',
        'priority',
        'emoji',
        'theme',
        'regle_appliquee',
        'actif',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'priority' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function symptoms(): HasMany
    {
        return $this->hasMany(Symptom::class);
    }
}
