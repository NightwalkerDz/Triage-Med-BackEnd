<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->json('symptomes');
            $table->enum('niveau_urgence', ['immediat', 'modere', 'non_urgent']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triages');
    }
};
