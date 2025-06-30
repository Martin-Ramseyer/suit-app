<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UsuarioRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?User;
    public function create(array $data): User;
    public function update(User $usuario, array $data): bool;
    public function delete(User $usuario): bool;
}
