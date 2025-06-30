<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    public function all(): Collection
    {
        return User::all();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        return User::create([
            'nombre_completo' => $data['nombre_completo'],
            'usuario' => $data['usuario'],
            'rol' => $data['rol'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function update(User $usuario, array $data): bool
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        return $usuario->update($data);
    }

    public function delete(User $usuario): bool
    {
        return $usuario->delete();
    }
}
