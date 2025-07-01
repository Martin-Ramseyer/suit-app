<?php

namespace App\Services\Usuario;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado;
use Illuminate\Http\Request;

class UsuarioMetricasService
{
    public function getMetricasRrpp(Request $request, User $usuario): array
    {
        if ($usuario->rol !== 'RRPP') {
            throw new \InvalidArgumentException('Solo los RRPP pueden tener métricas.');
        }

        $eventos = Evento::orderBy('fecha_evento', 'desc')->get();
        $eventoIdSeleccionado = $request->input('evento_id');

        $invitadosQuery = Invitado::where('usuario_id', $usuario->id);

        if ($eventoIdSeleccionado) {
            $invitadosQuery->where('evento_id', $eventoIdSeleccionado);
        }

        $invitados = $invitadosQuery->with('evento')->get();

        $totalInvitadosPrincipales = $invitados->count();
        $totalAcompanantes = $invitados->sum('numero_acompanantes');
        $totalPersonas = $totalInvitadosPrincipales + $totalAcompanantes;

        $invitadosIngresaron = $invitados->where('ingreso', true);
        $ingresaronPrincipales = $invitadosIngresaron->count();
        $ingresaronAcompanantes = $invitadosIngresaron->sum('numero_acompanantes');
        $totalIngresaron = $ingresaronPrincipales + $ingresaronAcompanantes;

        $tasaAsistencia = ($totalPersonas > 0) ? ($totalIngresaron / $totalPersonas) * 100 : 0;

        $eventoSeleccionado = $eventoIdSeleccionado ? Evento::find($eventoIdSeleccionado) : null;

        return [
            'usuario' => $usuario,
            'metricas' => [
                'totalPersonas' => $totalPersonas,
                'totalIngresaron' => $totalIngresaron,
                'totalNoIngresaron' => $totalPersonas - $totalIngresaron,
                'tasaAsistencia' => round($tasaAsistencia, 2),
            ],
            'eventos' => $eventos,
            'eventoSeleccionado' => $eventoSeleccionado,
            'invitados' => $invitados,
        ];
    }
}
