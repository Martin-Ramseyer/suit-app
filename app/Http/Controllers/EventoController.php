<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Services\Evento\EventoService;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    protected $eventoService;

    public function __construct(EventoService $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    public function index()
    {
        $eventos = $this->eventoService->getAllEventos();
        return view('eventos.index', compact('eventos'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_evento' => 'required|date|unique:eventos,fecha_evento',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $this->eventoService->createEvento($request->all());

        return redirect()->route('eventos.index')
            ->with('success', 'Evento creado exitosamente.');
    }

    public function edit(Evento $evento)
    {
        return view('eventos.edit', compact('evento'));
    }

    public function update(Request $request, Evento $evento)
    {
        $request->validate([
            'fecha_evento' => 'required|date|unique:eventos,fecha_evento,' . $evento->id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        $this->eventoService->updateEvento($evento, $request->all());

        return redirect()->route('eventos.index')
            ->with('success', 'Evento actualizado exitosamente.');
    }

    public function destroy(Evento $evento)
    {
        $this->eventoService->deleteEvento($evento);
        return redirect()->route('eventos.index')
            ->with('success', 'Evento eliminado exitosamente.');
    }

    public function historial(Request $request)
    {
        $data = $this->eventoService->getHistorialData($request);
        return view('eventos.historial', $data);
    }

    public function toggleActivo(Evento $evento)
    {
        $this->eventoService->toggleActivo($evento);
        return redirect()->route('eventos.index')->with('success', 'Estado del evento actualizado.');
    }
}
