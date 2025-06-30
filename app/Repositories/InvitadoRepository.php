<?php

namespace App\Repositories;

use App\Models\Invitado;
use App\Models\User;
use App\Interfaces\InvitadoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InvitadoRepository implements InvitadoRepositoryInterface
{
    public function getFiltered(array $filters, User $user): Collection
    {
        $query = Invitado::query();

        if ($user->rol === 'RRPP') {
            $query->where('usuario_id', $user->id);
        }

        if (!empty($filters['evento_id'])) {
            $query->where('evento_id', $filters['evento_id']);
        } else {
            if (in_array($user->rol, ['RRPP', 'CAJERO'])) {
                $query->whereRaw('1 = 0'); // Devuelve nada si no hay evento seleccionado para estos roles
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre_completo', 'like', "%{$search}%")
                    ->orWhereHas('rrpp', function ($rrppQuery) use ($search) {
                        $rrppQuery->where('nombre_completo', 'like', "%{$search}%")
                            ->orWhere('usuario', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderByRaw('CASE WHEN usuario_id IS NULL THEN 1 ELSE 0 END, nombre_completo ASC');

        return $query->with(['evento', 'beneficios', 'rrpp'])->get();
    }

    public function create(array $data): Invitado
    {
        return Invitado::create($data);
    }

    public function update(Invitado $invitado, array $data): bool
    {
        return $invitado->update($data);
    }

    public function delete(Invitado $invitado): bool
    {
        return $invitado->delete();
    }

    public function associateBeneficios(Invitado $invitado, array $beneficios): void
    {
        $invitado->beneficios()->attach($beneficios);
    }

    public function syncBeneficios(Invitado $invitado, array $beneficios): void
    {
        $invitado->beneficios()->sync($beneficios);
    }

    public function toggleIngreso(Invitado $invitado, bool $ingreso): bool
    {
        $invitado->ingreso = $ingreso;
        return $invitado->save();
    }

    public function updateAcompanantes(Invitado $invitado, int $cantidad): bool
    {
        $invitado->numero_acompanantes = $cantidad;
        return $invitado->save();
    }
}
