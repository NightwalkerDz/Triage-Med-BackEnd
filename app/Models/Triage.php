<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Triage extends Model
{
    protected $fillable = [
        'patient_id',
        'symptomes',
        'niveau_urgence',
    ];

    protected $casts = [
        'symptomes' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
