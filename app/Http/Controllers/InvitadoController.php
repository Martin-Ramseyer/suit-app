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

        $invitados = $query->with(['evento', 'beneficios', 'rrpp'])->latest()->get();

        if (in_array($user->rol, ['ADMIN', 'CAJERO'])) {
            $eventosParaSelector = Evento::orderBy('fecha_evento', 'desc')->get();
        } else {
            $eventosParaSelector = $eventosFuturos;
        }

        // El evento seleccionado será el activo para el cajero
        $eventoSeleccionado = $eventoId ? ($user->rol === 'CAJERO' ? $eventoActivo : Evento::find($eventoId)) : null;

        if ($request->ajax()) {
            return view('invitados._invitados_table', compact('invitados'))->render();
        }

        return view('invitados.index', compact('invitados', 'search', 'eventosParaSelector', 'eventoId', 'eventoSeleccionado'));
    }


    public function create()
    {
        $this->authorizeRole(['RRPP', 'ADMIN']);

        $user = Auth::user();

        $eventos = Evento::where('fecha_evento', '>=', now()->toDateString())
            ->orderBy('fecha_evento', 'asc')
            ->get();

        $beneficios = Beneficio::all();

        if ($user->rol === 'RRPP' && $eventos->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No hay eventos próximos activos para cargar invitados.');
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
