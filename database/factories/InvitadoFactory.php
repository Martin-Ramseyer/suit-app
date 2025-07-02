<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InvitadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_completo' => fake()->name(),
            'numero_acompanantes' => fake()->numberBetween(0, 5),
            'ingreso' => fake()->boolean(),
            'usuario_id' => \App\Models\User::factory(),
            'evento_id' => \App\Models\Evento::factory(),
        ];
    }
}