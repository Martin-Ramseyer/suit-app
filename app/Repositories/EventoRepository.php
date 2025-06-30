<?php

namespace App\Repositories;

use App\Interfaces\EventoRepositoryInterface;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EventoRepository implements EventoRepositoryInterface
{
    public function allOrderedByDate(): Collection
    {
        return Evento::orderBy('fecha_evento', 'desc')->get();
    }

    public function findById(int $id): ?Evento
    {
        return Evento::find($id);
    }

    public function create(array $data): Evento
    {
        return Evento::create($data);
    }

    public function update(Evento $evento, array $data): bool
    {
        return $evento->update($data);
    }

    public function delete(Evento $evento): bool
    {
        return $evento->delete();
    }

    public function toggleActivo(Evento $evento): bool
    {
        // Usamos una transacción para asegurar la atomicidad de la operación
        return DB::transaction(function () use ($evento) {
            Evento::where('id', '!=', $evento->id)->update(['activo' => false]);
            $evento->activo = !$evento->activo;
            return $evento->save();
        });
    }
}
