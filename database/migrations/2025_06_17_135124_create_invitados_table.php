<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invitados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->integer('numero_acompanantes')->default(0);
            $table->boolean('ingreso')->default(false);
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('evento_id')->constrained('eventos');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('invitados');
    }
};
