<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urgency_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('label');
            $table->unsignedTinyInteger('priority');
            $table->string('emoji', 10)->default('⚪');
            $table->string('theme', 20)->default('slate');
            $table->text('regle_appliquee')->nullable();
            $table->boolean('actif')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urgency_levels');
    }
};
