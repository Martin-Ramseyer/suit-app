<?php

namespace App\Services\Evento;

use App\Interfaces\EventoRepositoryInterface;
use App\Models\Invitado;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class EventoMetricasService
{
    protected $eventoRepository;

    public function __construct(EventoRepositoryInterface $eventoRepository)
    {
        $this->eventoRepository = $eventoRepository;
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

    public function getChartDataForEvento(Evento $evento): array
    {
        $invitados = Invitado::where('evento_id', $evento->id)->with('rrpp')->get();

        $metricasPorRrpp = $invitados->groupBy('rrpp.nombre_completo')
            ->map(function ($invitadosDelRrpp) {
                $totalInvitados = $invitadosDelRrpp->count() + $invitadosDelRrpp->sum('numero_acompanantes');
                $ingresaron = $invitadosDelRrpp->where('ingreso', true);
                $totalIngresaron = $ingresaron->count() + $ingresaron->sum('numero_acompanantes');
                return [
                    'total' => $totalInvitados,
                    'ingresaron' => $totalIngresaron,
                ];
            })->sortByDesc('ingresaron');

        return [
            'labels' => $metricasPorRrpp->keys(),
            'datasets' => [
                [
                    'label' => 'Total de Personas Invitadas',
                    'data' => $metricasPorRrpp->pluck('total')->values(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Total de Ingresos',
                    'data' => $metricasPorRrpp->pluck('ingresaron')->values(),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    public function getMetricasParaDashboard(): array
    {
        $ultimoEvento = $this->eventoRepository->allOrderedByDate()->first();
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
