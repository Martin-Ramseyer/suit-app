<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Services\Evento\EventoService;
use App\Services\Evento\EventoMetricasService; // 1. Importa el nuevo servicio
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventoController extends Controller
{
    protected $eventoService;
    protected $metricasService; // 2. Añade la propiedad para el nuevo servicio

    public function __construct(EventoService $eventoService, EventoMetricasService $metricasService) // 3. Inyéctalo aquí
    {
        $this->eventoService = $eventoService;
        $this->metricasService = $metricasService; // 4. Asígnalo
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
        // 5. Usa el servicio de métricas para obtener los datos del historial
        $data = $this->metricasService->getHistorialData($request);
        return view('eventos.historial', $data);
    }

    public function toggleActivo(Evento $evento)
    {
        $this->eventoService->toggleActivo($evento);
        return redirect()->route('eventos.index')->with('success', 'Estado del evento actualizado.');
    }
    public function getChartData(Evento $evento): JsonResponse
    {
        $data = $this->metricasService->getChartDataForEvento($evento);
        return response()->json($data);
    }
}
