<?php

namespace App\Http\Controllers;

use App\Models\Evento;
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
}
