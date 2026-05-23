<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('triages', function (Blueprint $table) {
            $table->string('niveau_urgence', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('triages', function (Blueprint $table) {
            $table->enum('niveau_urgence', ['immediat', 'modere', 'non_urgent'])->change();
        });
    }
};
