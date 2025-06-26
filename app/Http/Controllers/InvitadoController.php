<?php

namespace App\Http\Controllers;

use App\Models\Invitado;
use App\Models\Evento;
use App\Models\Beneficio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InvitadoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $eventoId = $request->input('evento_id');

        // Obtenemos los eventos futuros para la lógica de selección
        $eventosFuturos = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();

        // Lógica de selección de evento por defecto
        if (!$request->has('evento_id')) {
            if (in_array($user->rol, ['RRPP', 'CAJERO'])) {
                // Si hay un solo evento futuro, se selecciona automáticamente
                if ($eventosFuturos->count() === 1) {
                    $eventoId = $eventosFuturos->first()->id;
                }
            } elseif ($user->rol === 'ADMIN') {
                // Para admin, por defecto muestra el próximo evento si existe
                $eventoId = $eventosFuturos->first()->id ?? null;
            }
        }

        $query = Invitado::query();

        if ($user->rol === 'RRPP') {
            $query->where('usuario_id', $user->id);
        }

        // Si no hay evento ID (ni por request ni por defecto), RRPP y Cajero no ven nada,
        // excepto si hay múltiples eventos futuros para que elijan.
        if ($eventoId) {
            $query->where('evento_id', $eventoId);
        } elseif (in_array($user->rol, ['RRPP', 'CAJERO']) && $eventosFuturos->count() !== 1) {
            $query->whereRaw('1 = 0');
        }

        $query->when($search, function ($q, $search) {
            return $q->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre_completo', 'like', "%{$search}%")
                    ->orWhereHas('rrpp', function ($rrppQuery) use ($search) {
                        $rrppQuery->where('nombre_completo', 'like', "%{$search}%")
                            ->orWhere('usuario', 'like', "%{$search}%");
                    });
            });
        });

        $invitados = $query->with(['evento', 'beneficios', 'rrpp'])->latest()->get();
        // Los admins ven todos los eventos, los demás roles solo los futuros
        $eventosParaSelector = $user->rol === 'ADMIN' ? Evento::orderBy('fecha_evento', 'desc')->get() : $eventosFuturos;

        $eventoSeleccionado = $eventoId ? Evento::find($eventoId) : null;

        // Si es una petición AJAX, solo devolvemos la tabla
        if ($request->ajax()) {
            return view('invitados._invitados_table', compact('invitados'))->render();
        }

        return view('invitados.index', compact('invitados', 'search', 'eventosParaSelector', 'eventoId', 'eventoSeleccionado'));
    }

    // ... (El resto de los métodos del controlador permanecen igual) ...
    public function create()
    {
        $this->authorizeRole(['RRPP', 'ADMIN']);

        $user = Auth::user();
        $eventos = collect(); // Inicializamos como colección vacía

        if ($user->rol === 'ADMIN') {
            $eventos = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();
        } else { // RRPP
            $eventoActual = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->first();
            if ($eventoActual) {
                $eventos->push($eventoActual);
            }
        }

        $beneficios = Beneficio::all();

        if ($user->rol === 'RRPP' && $eventos->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No hay un evento próximo activo para cargar invitados.');
        }

        return view('invitados.create', compact('eventos', 'beneficios'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['RRPP', 'ADMIN']);

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_acompanantes' => 'required|integer|min:0',
            'evento_id' => 'required|exists:eventos,id',
            'beneficios' => 'nullable|array',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'required_with:beneficios.*|integer|min:1',
        ]);

        // Verificación adicional para RRPP
        if (Auth::user()->rol === 'RRPP') {
            $eventoActual = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->first();
            if (!$eventoActual || $request->evento_id != $eventoActual->id) {
                abort(403, 'Solo puedes agregar invitados al próximo evento activo.');
            }
        }

        $invitado = Invitado::create([
            'nombre_completo' => $request->nombre_completo,
            'numero_acompanantes' => $request->numero_acompanantes,
            'evento_id' => $request->evento_id,
            'usuario_id' => Auth::id(),
        ]);

        if (Auth::user()->rol === 'ADMIN' && $request->has('beneficios')) {
            $beneficiosParaAdjuntar = [];
            foreach ($request->beneficios as $beneficioId => $value) {
                $beneficiosParaAdjuntar[$beneficioId] = ['cantidad' => $request->cantidades[$beneficioId] ?? 1];
            }
            $invitado->beneficios()->attach($beneficiosParaAdjuntar);
        }

        return redirect()->route('invitados.index')
            ->with('success', 'Invitado agregado exitosamente.');
    }

    public function edit(Invitado $invitado)
    {
        $this->authorizeOwnership($invitado);

        $user = Auth::user();
        $eventos = collect();

        if ($user->rol === 'ADMIN') {
            $eventos = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();
        } else { // RRPP
            $eventoActual = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->first();
            if ($eventoActual) {
                $eventos->push($eventoActual);
            }
        }

        $beneficios = Beneficio::all();
        return view('invitados.edit', compact('invitado', 'eventos', 'beneficios'));
    }

    public function update(Request $request, Invitado $invitado)
    {
        $this->authorizeOwnership($invitado);

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_acompanantes' => 'required|integer|min:0',
            'evento_id' => 'required|exists:eventos,id',
            'beneficios' => 'nullable|array',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'required_with:beneficios.*|integer|min:1',
        ]);

        $invitado->update($request->only('nombre_completo', 'numero_acompanantes', 'evento_id'));

        if (Auth::user()->rol === 'ADMIN') {
            $beneficiosParaSincronizar = [];
            if ($request->has('beneficios')) {
                foreach ($request->beneficios as $beneficioId => $value) {
                    $beneficiosParaSincronizar[$beneficioId] = ['cantidad' => $request->cantidades[$beneficioId] ?? 1];
                }
            }
            $invitado->beneficios()->sync($beneficiosParaSincronizar);
        }

        return redirect()->route('invitados.index')
            ->with('success', 'Invitado actualizado exitosamente.');
    }

    public function destroy(Invitado $invitado)
    {
        $this->authorizeOwnership($invitado);
        $invitado->delete();
        return redirect()->route('invitados.index')
            ->with('success', 'Invitado eliminado exitosamente.');
    }

    public function toggleIngreso(Request $request, Invitado $invitado)
    {
        $this->authorizeRole(['CAJERO', 'ADMIN']);

        $eventoActual = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->first();
        if (Auth::user()->rol === 'CAJERO' && (!$eventoActual || $invitado->evento_id != $eventoActual->id)) {
            return response()->json(['success' => false, 'message' => 'Solo se puede modificar el ingreso de invitados para el evento actual.'], 403);
        }

        $invitado->ingreso = !$invitado->ingreso;
        $invitado->save();
        return response()->json(['success' => true, 'nuevo_estado' => $invitado->ingreso]);
    }

    private function authorizeRole(array $roles)
    {
        if (!in_array(Auth::user()->rol, $roles)) {
            abort(403, 'Acción no autorizada.');
        }
    }

    private function authorizeOwnership(Invitado $invitado)
    {
        $user = Auth::user();
        if ($user->rol === 'ADMIN') {
            return;
        }

        // RRPP solo puede editar invitados de eventos futuros
        $eventoDelInvitado = Evento::find($invitado->evento_id);
        if ($user->rol === 'RRPP' && $eventoDelInvitado && $eventoDelInvitado->fecha_evento < now()->toDateString()) {
            abort(403, 'No puedes modificar invitados de eventos pasados.');
        }

        if ($user->rol === 'RRPP' && $invitado->usuario_id === $user->id) {
            return;
        }

        abort(403, 'No tienes permiso para realizar esta acción sobre este invitado.');
    }
}
