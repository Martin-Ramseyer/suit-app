<?php

namespace App\Services\Invitado;

use App\Models\Invitado;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InvitadoAuthorizationService
{

    public function authorizeRole(array $roles): void
    {
        if (!in_array(Auth::user()->rol, $roles)) {
            abort(403, 'Acción no autorizada.');
        }
    }
    public function authorizeOwnership(Invitado $invitado): void
    {
        $user = Auth::user();
        if ($user->rol === 'ADMIN' || ($user->rol === 'RRPP' && $invitado->usuario_id === $user->id)) {
            return;
        }
        abort(403, 'No tienes permiso para realizar esta acción sobre este invitado.');
    }
}
