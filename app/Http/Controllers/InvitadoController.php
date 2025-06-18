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
    /**
     * Muestra la lista de invitados según el rol del usuario.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->rol === 'ADMIN' || $user->rol === 'CAJERO') {
            $invitados = Invitado::with(['evento', 'beneficios', 'rrpp'])->latest()->get();
        } else {
            $invitados = Invitado::where('usuario_id', $user->id)->with(['evento', 'beneficios'])->latest()->get();
        }

        return view('invitados.index', compact('invitados'));
    }

    /**
     * Muestra el formulario para crear un nuevo invitado.
     */
    public function create()
    {
        $this->authorizeRole(['RRPP', 'ADMIN']);

        $eventos = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();
        $beneficios = Beneficio::all();

        return view('invitados.create', compact('eventos', 'beneficios'));
    }

    /**
     * Guarda un nuevo invitado en la base de datos.
     */
    public function store(Request $request)
    {
        $this->authorizeRole(['RRPP', 'ADMIN']);

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_acompanantes' => 'required|integer|min:0',
            'evento_id' => 'required|exists:eventos,id',
            'beneficios' => 'nullable|array',
            'beneficios.*' => 'exists:beneficios,id',
        ]);

        $invitado = Invitado::create([
            'nombre_completo' => $request->nombre_completo,
            'numero_acompanantes' => $request->numero_acompanantes,
            'evento_id' => $request->evento_id,
            'usuario_id' => Auth::id(),
        ]);

        if ($request->has('beneficios')) {
            $invitado->beneficios()->attach($request->beneficios);
        }

        return redirect()->route('invitados.index')
            ->with('success', 'Invitado agregado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un invitado.
     */
    public function edit(Invitado $invitado)
    {
        // Autoriza que solo el ADMIN o el RRPP dueño del invitado puedan editar.
        $this->authorizeOwnership($invitado);

        $eventos = Evento::where('fecha_evento', '>=', now()->toDateString())->orderBy('fecha_evento', 'asc')->get();
        $beneficios = Beneficio::all();

        return view('invitados.edit', compact('invitado', 'eventos', 'beneficios'));
    }

    /**
     * Actualiza un invitado en la base de datos.
     */
    public function update(Request $request, Invitado $invitado)
    {
        // Autoriza que solo el ADMIN o el RRPP dueño del invitado puedan actualizar.
        $this->authorizeOwnership($invitado);

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_acompanantes' => 'required|integer|min:0',
            'evento_id' => 'required|exists:eventos,id',
            'beneficios' => 'nullable|array',
            'beneficios.*' => 'exists:beneficios,id',
        ]);

        // Actualiza los datos del invitado
        $invitado->update($request->only('nombre_completo', 'numero_acompanantes', 'evento_id'));

        // Sincroniza los beneficios. sync() se encarga de añadir/quitar según lo seleccionado.
        $invitado->beneficios()->sync($request->beneficios ?? []);

        return redirect()->route('invitados.index')
            ->with('success', 'Invitado actualizado exitosamente.');
    }

    /**
     * Elimina un invitado de la base de datos.
     */
    public function destroy(Invitado $invitado)
    {
        // Autoriza que solo el ADMIN o el RRPP dueño del invitado puedan eliminar.
        $this->authorizeOwnership($invitado);

        $invitado->delete();

        return redirect()->route('invitados.index')
            ->with('success', 'Invitado eliminado exitosamente.');
    }

    // Helper para verificar roles permitidos en una acción
    private function authorizeRole(array $roles)
    {
        if (!in_array(Auth::user()->rol, $roles)) {
            abort(403, 'Acción no autorizada.');
        }
    }

    // Helper para verificar si el usuario es dueño del recurso o es Admin
    private function authorizeOwnership(Invitado $invitado)
    {
        $user = Auth::user();
        // Si el usuario es ADMIN, puede pasar.
        if ($user->rol === 'ADMIN') {
            return;
        }
        // Si el usuario es RRPP, debe ser el dueño del invitado.
        if ($user->rol === 'RRPP' && $invitado->usuario_id === $user->id) {
            return;
        }
        // Si no se cumple ninguna condición, se deniega el acceso.
        abort(403, 'No tienes permiso para realizar esta acción sobre este invitado.');
    }
}
