<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Beneficio;

class BeneficioSeeder extends Seeder
{
    public function run(): void
    {
        Beneficio::firstOrCreate(['nombre_beneficio' => 'Pulsera Vip']);
        Beneficio::firstOrCreate(['nombre_beneficio' => 'Entrada Free']);
        Beneficio::firstOrCreate(['nombre_beneficio' => 'Consumición']);
    }
}
