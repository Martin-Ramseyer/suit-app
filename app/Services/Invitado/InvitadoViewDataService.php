<?php

namespace App\Services\Invitado;

use App\Interfaces\InvitadoRepositoryInterface;
use App\Models\Evento;
use App\Models\Beneficio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvitadoViewDataService
{
    protected $invitadoRepository;

    public function __construct(InvitadoRepositoryInterface $invitadoRepository)
    {
        $this->invitadoRepository = $invitadoRepository;
    }

    /**
     * Obtiene los datos necesarios para la vista de listado de invitados (index).
     */
    public function getInvitadosForIndex(Request $request): array
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

    /**
     * Obtiene los datos para el formulario de creación de invitados.
     */
    public function getDataForCreateForm(): array
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
}
