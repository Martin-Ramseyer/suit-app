<?php

namespace App\Interfaces;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Collection;

interface EventoRepositoryInterface
{
    public function allOrderedByDate(): Collection;
    public function findById(int $id): ?Evento;
    public function create(array $data): Evento;
    public function update(Evento $evento, array $data): bool;
    public function delete(Evento $evento): bool;
    public function toggleActivo(Evento $evento): bool;
}
