<?php

namespace App\Services\Invitado;

use App\Interfaces\InvitadoRepositoryInterface;
use App\Models\Invitado;
use App\Models\Evento;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvitadoActionService
{
    protected $invitadoRepository;
    protected $authorizationService;

    public function __construct(
        InvitadoRepositoryInterface $invitadoRepository,
        InvitadoAuthorizationService $authorizationService
    ) {
        $this->invitadoRepository = $invitadoRepository;
        $this->authorizationService = $authorizationService;
    }

    /**
     * Cambia el estado de ingreso de un invitado.
     */
    public function toggleIngreso(Invitado $invitado, bool $ingreso): bool
    {
        $this->authorizationService->authorizeRole(['CAJERO', 'ADMIN']);

        $eventoActivo = Evento::where('activo', true)->first();

        if (Auth::user()->rol === 'CAJERO' && (!$eventoActivo || $invitado->evento_id != $eventoActivo->id)) {
            throw new Exception('Solo se puede modificar el ingreso de invitados para el evento activo.');
        }

        return $this->invitadoRepository->toggleIngreso($invitado, $ingreso);
    }

    /**
     * Actualiza la cantidad de acompañantes de un invitado.
     */
    public function updateAcompanantes(Invitado $invitado, int $cantidad): bool
    {
        $this->authorizationService->authorizeRole(['CAJERO', 'ADMIN']);
        return $this->invitadoRepository->updateAcompanantes($invitado, $cantidad);
    }
}
