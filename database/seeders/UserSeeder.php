<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nombre_completo' => 'Admin',
            'usuario' => 'admin',
            'password' => Hash::make('root'), // ¡Cambia esta contraseña!
            'rol' => 'ADMIN',
            'activo' => true,
        ]);
    }
}
