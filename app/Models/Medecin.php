<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medecin extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'specialite',
        'email',
        'telephone',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function getNomCompletAttribute(): string
    {
        return "Dr {$this->prenom} {$this->nom}";
    }
}
