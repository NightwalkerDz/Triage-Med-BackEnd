<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'age',
        'sexe',
        'telephone',
        'antecedents_medicaux',
        'medecin_traitant',
        'medecin_id',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(Medecin::class);
    }

    public function triage(): HasOne
    {
        return $this->hasOne(Triage::class);
    }
}
