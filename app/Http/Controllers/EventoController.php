<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Invitado;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    /**
     * Muestra una lista de todos los eventos.
     */
    public function index()
    {
        // Obtenemos todos los eventos, ordenados por fecha descendente.
        $eventos = Evento::orderBy('fecha_evento', 'desc')->get();

        // Retornamos la vista y le pasamos la variable 'eventos'.
        return view('eventos.index', compact('eventos'));
    }

    /**
     * Muestra el formulario para crear un nuevo evento.
     */
    public function create()
    {
        return view('eventos.create');
    }

    /**
     * Guarda un nuevo evento en la base de datos.
     */
    public function store(Request $request)
    {
        // Validación de los datos del formulario.
        $request->validate([
            'fecha_evento' => 'required|date|unique:eventos,fecha_evento',
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Crea el nuevo evento.
        Evento::create($request->all());

        // Redirige al usuario a la lista de eventos con un mensaje de éxito.
        return redirect()->route('eventos.index')
            ->with('success', 'Evento creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un evento existente.
     */
    public function edit(Evento $evento)
    {
        return view('eventos.edit', compact('evento'));
    }

    /**
     * Actualiza un evento existente en la base de datos.
     */
    public function update(Request $request, Evento $evento)
    {
        // Validación (la regla 'unique' debe ignorar el evento actual).
        $request->validate([
            'fecha_evento' => 'required|date|unique:eventos,fecha_evento,' . $evento->id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Actualiza el evento.
        $evento->update($request->all());

        // Redirige con un mensaje de éxito.
        return redirect()->route('eventos.index')
            ->with('success', 'Evento actualizado exitosamente.');
    }

    /**
     * Elimina un evento de la base de datos.
     */
    public function destroy(Evento $evento)
    {
        $evento->delete();

        // Redirige con un mensaje de éxito.
        return redirect()->route('eventos.index')
            ->with('success', 'Evento eliminado exitosamente.');
    }

    public function historial(Request $request)
    {
        // 1. Obtener todos los eventos para el selector.
        $eventos = Evento::orderBy('fecha_evento', 'desc')->get();

        $invitados = collect();
        $eventoSeleccionado = null;
        // Obtenemos el ID del evento que el usuario seleccionó en el formulario.
        $eventoIdSeleccionado = $request->input('evento_id');

        // 2. Si se seleccionó un ID, procedemos a buscar.
        if ($eventoIdSeleccionado) {
            $eventoSeleccionado = Evento::find($eventoIdSeleccionado);

            if ($eventoSeleccionado) {
                $invitados = Invitado::where('evento_id', $eventoIdSeleccionado)
                    ->with(['rrpp', 'beneficios'])
                    ->orderBy('nombre_completo', 'asc')
                    ->get();
            }
        }

        // 3. Retornar la vista con los datos necesarios.
        //    Añadimos 'eventoIdSeleccionado' a las variables que pasamos a la vista.
        return view('eventos.historial', compact('eventos', 'invitados', 'eventoSeleccionado', 'eventoIdSeleccionado'));
    }
}
