<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BeneficioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_beneficio' => fake()->unique()->word(),
        ];
    }
}
