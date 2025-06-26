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
        $eventos = Evento::orderBy('fecha_evento', 'desc')->get();

        $invitados = collect();
        $eventoSeleccionado = null;
        $metricas = null;
        $eventoIdSeleccionado = $request->input('evento_id');

        if ($eventoIdSeleccionado) {
            $eventoSeleccionado = Evento::find($eventoIdSeleccionado);

            if ($eventoSeleccionado) {
                $invitados = Invitado::where('evento_id', $eventoIdSeleccionado)
                    ->with(['rrpp', 'beneficios'])
                    ->orderBy('nombre_completo', 'asc')
                    ->get();

                $totalPersonas = $invitados->count() + $invitados->sum('numero_acompanantes');
                $invitadosQueIngresaron = $invitados->where('ingreso', true);
                $totalIngresaron = $invitadosQueIngresaron->count() + $invitadosQueIngresaron->sum('numero_acompanantes');

                // Inicializamos el contador de beneficios
                $beneficiosContador = [
                    'Pulsera Vip' => 0,
                    'Entrada Free' => 0,
                    'Consumición' => 0,
                ];

                foreach ($invitadosQueIngresaron as $invitado) {
                    // --- FIN DEL CAMBIO ---
                    foreach ($invitado->beneficios as $beneficio) {
                        if (isset($beneficiosContador[$beneficio->nombre_beneficio])) {
                            $beneficiosContador[$beneficio->nombre_beneficio] += $beneficio->pivot->cantidad;
                        }
                    }
                }

                $topRrpp = null;
                $bottomRrpp = null;

                if ($invitadosQueIngresaron->isNotEmpty()) {
                    $rrppConteoIngresos = $invitadosQueIngresaron->groupBy('rrpp.nombre_completo')
                        ->map(function ($invitadosDelRrpp) {
                            return $invitadosDelRrpp->sum('numero_acompanantes') + $invitadosDelRrpp->count();
                        })
                        ->sortDesc();

                    if ($rrppConteoIngresos->isNotEmpty()) {
                        $maxIngresos = $rrppConteoIngresos->max();
                        $topRrppNombres = $rrppConteoIngresos->filter(function ($count) use ($maxIngresos) {
                            return $count === $maxIngresos;
                        })->keys();
                        $topRrpp = $topRrppNombres->map(function ($nombre) use ($maxIngresos) {
                            return "{$nombre} ({$maxIngresos})";
                        })->implode(', ');

                        $minIngresos = $rrppConteoIngresos->min();
                        $bottomRrppNombres = $rrppConteoIngresos->filter(function ($count) use ($minIngresos) {
                            return $count === $minIngresos;
                        })->keys();

                        $bottomRrpp = $bottomRrppNombres->map(function ($nombre) use ($minIngresos) {
                            return "{$nombre} ({$minIngresos})";
                        })->implode(', ');
                    }
                }

                $metricas = [
                    'totalInvitados' => $totalPersonas,
                    'invitadosIngresaron' => $totalIngresaron,
                    'beneficios' => $beneficiosContador,
                    'topRrpp' => $topRrpp,
                    'bottomRrpp' => $bottomRrpp,
                ];
            }
        }

        // Retornar la vista con los datos necesarios.
        return view('eventos.historial', compact(
            'eventos',
            'invitados',
            'eventoSeleccionado',
            'eventoIdSeleccionado',
            'metricas'
        ));
    }

    public function obtenerMetricasDeEvento($eventoId)
    {
        $invitados = Invitado::where('evento_id', $eventoId)->get();
        $invitadosQueIngresaron = $invitados->where('ingreso', true);

        $totalPersonas = $invitados->count() + $invitados->sum('numero_acompanantes');
        $totalIngresaron = $invitadosQueIngresaron->count() + $invitadosQueIngresaron->sum('numero_acompanantes');

        $rrppConteoIngresos = $invitadosQueIngresaron->groupBy('rrpp.nombre_completo')
            ->map(fn($invitadosDelRrpp) => $invitadosDelRrpp->count() + $invitadosDelRrpp->sum('numero_acompanantes'))
            ->sortDesc();

        $topRrpp = $rrppConteoIngresos->isNotEmpty() ? $rrppConteoIngresos->keys()->first() . ' (' . $rrppConteoIngresos->first() . ')' : null;

        return [
            'totalInvitados' => $totalPersonas,
            'invitadosIngresaron' => $totalIngresaron,
            'topRrpp' => $topRrpp,
        ];
    }
}
