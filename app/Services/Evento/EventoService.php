<?php

namespace App\Services\Evento;

use App\Interfaces\EventoRepositoryInterface;
use App\Models\Evento;
use App\Models\Invitado;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EventoService
{
    protected $eventoRepository;

    public function __construct(EventoRepositoryInterface $eventoRepository)
    {
        $this->eventoRepository = $eventoRepository;
    }

    public function getAllEventos()
    {
        return $this->eventoRepository->allOrderedByDate();
    }

    public function createEvento(array $data): Evento
    {
        return $this->eventoRepository->create($data);
    }

    public function updateEvento(Evento $evento, array $data): bool
    {
        return $this->eventoRepository->update($evento, $data);
    }

    public function deleteEvento(Evento $evento): bool
    {
        return $this->eventoRepository->delete($evento);
    }

    public function toggleActivo(Evento $evento): bool
    {
        return $this->eventoRepository->toggleActivo($evento);
    }

    public function getHistorialData(Request $request): array
    {
        $eventos = $this->eventoRepository->allOrderedByDate();
        $eventoIdSeleccionado = $request->input('evento_id');
        $eventoSeleccionado = null;
        $invitados = collect();
        $metricas = null;

        if ($eventoIdSeleccionado) {
            $eventoSeleccionado = $this->eventoRepository->findById($eventoIdSeleccionado);
            if ($eventoSeleccionado) {
                $invitados = Invitado::where('evento_id', $eventoIdSeleccionado)
                    ->with(['rrpp', 'beneficios'])
                    ->orderBy('nombre_completo', 'asc')
                    ->get();

                $metricas = $this->calcularMetricas($invitados);
            }
        }

        return compact('eventos', 'invitados', 'eventoSeleccionado', 'eventoIdSeleccionado', 'metricas');
    }

    public function getMetricasParaDashboard(): array
    {
        $ultimoEvento = Evento::orderBy('fecha_evento', 'desc')->first();
        if (!$ultimoEvento) {
            return ['ultimoEvento' => null, 'metricasUltimoEvento' => []];
        }

        $invitados = Invitado::where('evento_id', $ultimoEvento->id)->get();
        $metricas = $this->calcularMetricas($invitados);

        return [
            'ultimoEvento' => $ultimoEvento,
            'metricasUltimoEvento' => [
                'totalInvitados' => $metricas['totalInvitados'],
                'invitadosIngresaron' => $metricas['invitadosIngresaron'],
                'topRrpp' => $metricas['topRrpp'],
            ],
        ];
    }

    private function calcularMetricas(Collection $invitados): array
    {
        $totalPersonas = $invitados->count() + $invitados->sum('numero_acompanantes');
        $invitadosQueIngresaron = $invitados->where('ingreso', true);
        $totalIngresaron = $invitadosQueIngresaron->count() + $invitadosQueIngresaron->sum('numero_acompanantes');

        $beneficiosContador = ['Pulsera Vip' => 0, 'Entrada Free' => 0, 'Consumición' => 0];
        foreach ($invitadosQueIngresaron as $invitado) {
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
                ->map(fn($invitadosDelRrpp) => $invitadosDelRrpp->sum('numero_acompanantes') + $invitadosDelRrpp->count())
                ->sortDesc();

            if ($rrppConteoIngresos->isNotEmpty()) {
                $maxIngresos = $rrppConteoIngresos->max();
                $topRrppNombres = $rrppConteoIngresos->filter(fn($count) => $count === $maxIngresos)->keys();
                $topRrpp = $topRrppNombres->map(fn($nombre) => "{$nombre} ({$maxIngresos})")->implode(', ');

                $minIngresos = $rrppConteoIngresos->min();
                $bottomRrppNombres = $rrppConteoIngresos->filter(fn($count) => $count === $minIngresos)->keys();
                $bottomRrpp = $bottomRrppNombres->map(fn($nombre) => "{$nombre} ({$minIngresos})")->implode(', ');
            }
        }

        return [
            'totalInvitados' => $totalPersonas,
            'invitadosIngresaron' => $totalIngresaron,
            'beneficios' => $beneficiosContador,
            'topRrpp' => $topRrpp,
            'bottomRrpp' => $bottomRrpp,
        ];
    }
}
