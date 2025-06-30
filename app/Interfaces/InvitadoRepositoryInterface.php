<?php

namespace App\Interfaces;

use App\Models\Invitado;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface InvitadoRepositoryInterface
{
    public function getFiltered(array $filters, User $user): Collection;
    public function create(array $data): Invitado;
    public function update(Invitado $invitado, array $data): bool;
    public function delete(Invitado $invitado): bool;
    public function associateBeneficios(Invitado $invitado, array $beneficios): void;
    public function syncBeneficios(Invitado $invitado, array $beneficios): void;
    public function toggleIngreso(Invitado $invitado, bool $ingreso): bool;
    public function updateAcompanantes(Invitado $invitado, int $cantidad): bool;
}
