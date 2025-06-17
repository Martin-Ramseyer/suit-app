<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('beneficios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_beneficio')->unique();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('beneficios');
    }
};
