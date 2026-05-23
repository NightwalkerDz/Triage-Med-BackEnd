<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->unsignedTinyInteger('age');
            $table->enum('sexe', ['M', 'F']);
            $table->string('telephone')->nullable();
            $table->text('antecedents_medicaux')->nullable();
            $table->string('medecin_traitant')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
