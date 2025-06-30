<?php

namespace App\Services;

use App\Interfaces\UsuarioRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class UsuarioService
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepositoryInterface $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function getAllUsuarios()
    {
        return $this->usuarioRepository->all();
    }

    public function createUsuario(array $data): User
    {
        return $this->usuarioRepository->create($data);
    }

    public function updateUsuario(User $usuario, array $data): bool
    {
        $updateData = [
            'nombre_completo' => $data['nombre_completo'],
            'usuario' => $data['usuario'],
            'rol' => $data['rol'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        return $this->usuarioRepository->update($usuario, $updateData);
    }

    public function deleteUsuario(User $usuario): bool
    {
        if (Auth::id() === $usuario->id) {
            throw new Exception('No puedes eliminarte a ti mismo.');
        }
        return $this->usuarioRepository->delete($usuario);
    }
}
