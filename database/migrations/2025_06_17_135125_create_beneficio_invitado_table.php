<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('beneficio_invitado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitado_id')->constrained('invitados')->onDelete('cascade');
            $table->foreignId('beneficio_id')->constrained('beneficios')->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('beneficio_invitado');
    }
};
