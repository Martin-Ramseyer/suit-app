<?php

namespace App\Services\Invitado;

use App\Interfaces\InvitadoRepositoryInterface;
use App\Models\Invitado;
use App\Models\Evento;
use App\Models\Beneficio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvitadoService
{
    protected $invitadoRepository;

    public function __construct(InvitadoRepositoryInterface $invitadoRepository)
    {
        $this->invitadoRepository = $invitadoRepository;
    }

    public function getInvitadosForIndex(Request $request)
    {
        $user = Auth::user();
        $eventoActivo = Evento::where('activo', true)->first();
        $eventoId = $request->input('evento_id');

        if ($user->rol === 'CAJERO') {
            $eventoId = $eventoActivo ? $eventoActivo->id : null;
        } else {
            $eventosFuturos = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();
            if (!$request->filled('evento_id') && $eventosFuturos->isNotEmpty()) {
                $eventoId = $eventosFuturos->first()->id;
            }
        }

        $filters = [
            'search' => $request->input('search'),
            'evento_id' => $eventoId,
        ];

        $invitados = $this->invitadoRepository->getFiltered($filters, $user);

        if (in_array($user->rol, ['ADMIN', 'CAJERO'])) {
            $eventosParaSelector = Evento::orderBy('fecha_evento', 'desc')->get();
        } else {
            $eventosParaSelector = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();
        }

        $eventoSeleccionado = $eventoId ? Evento::find($eventoId) : null;

        return compact('invitados', 'eventosParaSelector', 'eventoId', 'eventoSeleccionado');
    }

    public function getDataForCreateForm()
    {
        $user = Auth::user();
        $beneficios = Beneficio::all();
        $eventos = collect();

        if ($user->rol === 'CAJERO') {
            $eventoActivo = Evento::where('activo', true)->first();
            if (!$eventoActivo) {
                throw new Exception('No hay ningún evento activo para cargar invitados en puerta.');
            }
            $eventos->push($eventoActivo);
        } else {
            $eventos = Evento::where('fecha_evento', '>=', now()->toDateString())
                ->orderBy('fecha_evento', 'asc')
                ->get();
            if ($user->rol === 'RRPP' && $eventos->isEmpty()) {
                throw new Exception('No hay eventos próximos activos para cargar invitados.');
            }
        }
        return compact('eventos', 'beneficios');
    }

    public function createInvitado(array $data, User $user): Invitado
    {
        $this->authorizeRole(['RRPP', 'ADMIN', 'CAJERO']);

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
        $this->authorizeOwnership($invitado);

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
        $this->authorizeOwnership($invitado);
        return $this->invitadoRepository->delete($invitado);
    }

    public function toggleIngreso(Invitado $invitado, bool $ingreso): bool
    {
        $this->authorizeRole(['CAJERO', 'ADMIN']);
        $eventoActivo = Evento::where('activo', true)->first();

        if (Auth::user()->rol === 'CAJERO' && (!$eventoActivo || $invitado->evento_id != $eventoActivo->id)) {
            throw new Exception('Solo se puede modificar el ingreso de invitados para el evento activo.');
        }

        return $this->invitadoRepository->toggleIngreso($invitado, $ingreso);
    }

    public function updateAcompanantes(Invitado $invitado, int $cantidad): bool
    {
        $this->authorizeRole(['CAJERO', 'ADMIN']);
        return $this->invitadoRepository->updateAcompanantes($invitado, $cantidad);
    }

    // Métodos de autorización privados
    private function authorizeRole(array $roles): void
    {
        if (!in_array(Auth::user()->rol, $roles)) {
            abort(403, 'Acción no autorizada.');
        }
    }

    private function authorizeOwnership(Invitado $invitado): void
    {
        $user = Auth::user();
        if ($user->rol === 'ADMIN' || ($user->rol === 'RRPP' && $invitado->usuario_id === $user->id)) {
            return;
        }
        abort(403, 'No tienes permiso para realizar esta acción sobre este invitado.');
    }
}
