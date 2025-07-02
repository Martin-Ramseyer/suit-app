<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fecha_evento' => fake()->unique()->date(),
            'descripcion' => fake()->sentence(),
            'precio_entrada' => fake()->randomFloat(2, 500, 5000),
            'activo' => true,
        ];
    }
}
