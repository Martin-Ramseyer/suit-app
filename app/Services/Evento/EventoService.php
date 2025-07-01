<?php

namespace App\Services\Evento;

use App\Interfaces\EventoRepositoryInterface;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoService
{
    protected $eventoRepository;

    public function __construct(EventoRepositoryInterface $eventoRepository)
    {
        $this->eventoRepository = $eventoRepository;
    }

    public function getAllEventos()
    {
        return $this->eventoRepository->allOrderedByDate();
    }

    public function createEvento(array $data): Evento
    {
        return $this->eventoRepository->create($data);
    }

    public function updateEvento(Evento $evento, array $data): bool
    {
        return $this->eventoRepository->update($evento, $data);
    }

    public function deleteEvento(Evento $evento): bool
    {
        return $this->eventoRepository->delete($evento);
    }

    public function toggleActivo(Evento $evento): bool
    {
        return $this->eventoRepository->toggleActivo($evento);
    }
}
