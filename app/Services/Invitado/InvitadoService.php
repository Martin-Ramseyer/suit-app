<?php

namespace App\Services\Invitado;

use App\Interfaces\InvitadoRepositoryInterface;
use App\Models\Invitado;
use App\Models\User;
use App\Services\Invitado\InvitadoAuthorizationService;
use Illuminate\Container\Container;

class InvitadoService
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
     * Crea un nuevo invitado.
     */
    public function createInvitado(array $data, User $user): Invitado
    {
        $this->authorizationService->authorizeRole(['RRPP', 'ADMIN', 'CAJERO']);

        $invitadoData = [
            'nombre_completo' => $data['nombre_completo'],
            'numero_acompanantes' => $data['numero_acompanantes'],
            'evento_id' => $data['evento_id'],
            'usuario_id' => $user->id,
            'ingreso' => ($user->rol === 'CAJERO'),
        ];

        $invitado = $this->invitadoRepository->create($invitadoData);

        if ($user->rol === 'ADMIN' && !empty($data['beneficios'])) {
            $beneficiosParaAdjuntar = [];
            foreach ($data['beneficios'] as $beneficioId => $value) {
                $beneficiosParaAdjuntar[$beneficioId] = ['cantidad' => $data['cantidades'][$beneficioId] ?? 1];
            }
            $this->invitadoRepository->associateBeneficios($invitado, $beneficiosParaAdjuntar);
        }

        return $invitado;
    }
    public function updateInvitado(Invitado $invitado, array $data, User $user): bool
    {
        $this->authorizationService->authorizeOwnership($invitado);

        $updateData = [
            'nombre_completo' => $data['nombre_completo'],
            'numero_acompanantes' => $data['numero_acompanantes'],
            'evento_id' => $data['evento_id'],
        ];

        $this->invitadoRepository->update($invitado, $updateData);

        if ($user->rol === 'ADMIN') {
            $beneficiosParaSincronizar = [];
            if (!empty($data['beneficios'])) {
                foreach ($data['beneficios'] as $beneficioId => $value) {
                    $beneficiosParaSincronizar[$beneficioId] = ['cantidad' => $data['cantidades'][$beneficioId] ?? 1];
                }
            }
            $this->invitadoRepository->syncBeneficios($invitado, $beneficiosParaSincronizar);
        }

        return true;
    }

    public function deleteInvitado(Invitado $invitado): bool
    {
        $this->authorizationService->authorizeOwnership($invitado);
        return $this->invitadoRepository->delete($invitado);
    }
}
