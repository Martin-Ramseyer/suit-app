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
        $eventoActivo = Evento::where('activo', true)->first();

        // Si el usuario es CAJERO, siempre usará el evento activo
        if ($user->rol === 'CAJERO') {
            $eventoId = $eventoActivo ? $eventoActivo->id : null;
        }

        $eventosFuturos = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();

        if (!$request->filled('evento_id') && $eventosFuturos->isNotEmpty() && $user->rol !== 'CAJERO') {
            $eventoId = $eventosFuturos->first()->id;
        }

        $query = Invitado::query();

        if ($user->rol === 'RRPP') {
            $query->where('usuario_id', $user->id);
        }

        if ($eventoId) {
            $query->where('evento_id', $eventoId);
        } else {
            if (in_array($user->rol, ['RRPP', 'CAJERO'])) {
                $query->whereRaw('1 = 0');
            }
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

        // **NUEVO CAMBIO**: Añadimos un orden para que los invitados sin RRPP (o de puerta) aparezcan al final
        $query->orderByRaw('CASE WHEN usuario_id IS NULL THEN 1 ELSE 0 END, nombre_completo ASC');

        $invitados = $query->with(['evento', 'beneficios', 'rrpp'])->get();


        if (in_array($user->rol, ['ADMIN', 'CAJERO'])) {
            $eventosParaSelector = Evento::orderBy('fecha_evento', 'desc')->get();
        } else {
            $eventosParaSelector = $eventosFuturos;
        }

        $eventoSeleccionado = $eventoId ? ($user->rol === 'CAJERO' ? $eventoActivo : Evento::find($eventoId)) : null;

        if ($request->ajax()) {
            return view('invitados._invitados_table', compact('invitados'));
        }

        return view('invitados.index', compact('invitados', 'search', 'eventosParaSelector', 'eventoId', 'eventoSeleccionado'));
    }


    public function create()
    {
        // **CAMBIO 1**: Permitimos que el CAJERO también pueda acceder a esta ruta.
        $this->authorizeRole(['RRPP', 'ADMIN', 'CAJERO']);

        $user = Auth::user();
        $eventos = collect();
        $beneficios = Beneficio::all();

        // **CAMBIO 2**: Lógica diferenciada para cada rol.
        if ($user->rol === 'CAJERO') {
            // El cajero SOLO puede cargar en el evento activo.
            $eventoActivo = Evento::where('activo', true)->first();
            if (!$eventoActivo) {
                return redirect()->route('invitados.index')->with('error', 'No hay ningún evento activo para cargar invitados en puerta.');
            }
            $eventos->push($eventoActivo);
        } else { // Para RRPP y ADMIN
            $eventos = Evento::where('fecha_evento', '>=', now()->toDateString())
                ->orderBy('fecha_evento', 'asc')
                ->get();
            if ($user->rol === 'RRPP' && $eventos->isEmpty()) {
                return redirect()->route('dashboard')->with('error', 'No hay eventos próximos activos para cargar invitados.');
            }
        }

        return view('invitados.create', compact('eventos', 'beneficios'));
    }

    public function store(Request $request)
    {
        // **CAMBIO 3**: Permitimos que el CAJERO también pueda usar esta funcionalidad.
        $this->authorizeRole(['RRPP', 'ADMIN', 'CAJERO']);

        $user = Auth::user();

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_acompanantes' => 'required|integer|min:0',
            'evento_id' => 'required|exists:eventos,id',
            'beneficios' => 'nullable|array',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'required_with:beneficios.*|integer|min:1',
        ]);

        // **CAMBIO 4**: Preparamos los datos del nuevo invitado.
        $datosInvitado = [
            'nombre_completo' => $request->nombre_completo,
            'numero_acompanantes' => $request->numero_acompanantes,
            'evento_id' => $request->evento_id,
            'usuario_id' => $user->id, // El invitado queda asociado a quien lo cargó (RRPP, Admin o Cajero)
        ];

        // **CAMBIO 5**: Si quien carga es un CAJERO, el invitado ya ingresa.
        if ($user->rol === 'CAJERO') {
            $datosInvitado['ingreso'] = true;
        }

        $invitado = Invitado::create($datosInvitado);

        // La lógica de beneficios solo se aplica para el Admin, lo cual está correcto.
        if ($user->rol === 'ADMIN' && $request->has('beneficios')) {
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

        // **CAMBIO**: Un cajero no debería poder editar invitados. La lógica de authorizeOwnership ya lo previene.
        // Si en el futuro se necesitara, aquí se añadiría la lógica. Por ahora, está bien así.

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

        $eventoActivo = Evento::where('activo', true)->first();

        // **CAMBIO**: Se ajusta la lógica para que el evento activo sea la única referencia para el cajero
        if (Auth::user()->rol === 'CAJERO' && (!$eventoActivo || $invitado->evento_id != $eventoActivo->id)) {
            return response()->json(['success' => false, 'message' => 'Solo se puede modificar el ingreso de invitados para el evento activo.'], 403);
        }

        $invitado->ingreso = $request->input('ingreso', !$invitado->ingreso);
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

        // **CAMBIO**: Un cajero no es dueño de ningún invitado, por lo que no puede editar ni eliminar.
        if ($user->rol === 'CAJERO') {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $eventoDelInvitado = Evento::find($invitado->evento_id);
        if ($user->rol === 'RRPP' && $eventoDelInvitado && $eventoDelInvitado->fecha_evento < now()->toDateString()) {
            abort(403, 'No puedes modificar invitados de eventos pasados.');
        }

        if ($user->rol === 'RRPP' && $invitado->usuario_id === $user->id) {
            return;
        }

        abort(403, 'No tienes permiso para realizar esta acción sobre este invitado.');
    }

    /**
     * Update the number of companions for a specific guest.
     */
    public function updateAcompanantes(Request $request, Invitado $invitado)
    {
        $this->authorizeRole(['CAJERO', 'ADMIN']);

        $request->validate([
            'numero_acompanantes' => 'required|integer|min:0',
        ]);

        $invitado->numero_acompanantes = $request->numero_acompanantes;
        $invitado->save();

        return redirect()->back()->with('success', 'Número de acompañantes actualizado correctamente.');
    }
}
