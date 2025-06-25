<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::all();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:usuarios',
            'rol' => ['required', Rule::in(['ADMIN', 'RRPP', 'CAJERO'])],
            'password' => 'required|string|min:4|confirmed',
        ]);

        User::create([
            'nombre_completo' => $request->nombre_completo,
            'usuario' => $request->usuario,
            'rol' => $request->rol,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:usuarios,usuario,' . $usuario->id,
            'rol' => ['required', Rule::in(['ADMIN', 'RRPP', 'CAJERO'])],
            'password' => 'nullable|string|min:4|confirmed',
        ]);

        $data = $request->only(['nombre_completo', 'usuario', 'rol']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        if (Auth::id() == $usuario->id) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }
    
    public function metricas(Request $request, User $usuario)
    {
        if ($usuario->rol !== 'RRPP') {
            return redirect()->route('usuarios.index')->with('error', 'Solo los RRPP pueden tener métricas.');
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

        $metricas = [
            'totalPersonas' => $totalPersonas,
            'totalIngresaron' => $totalIngresaron,
            'totalNoIngresaron' => $totalPersonas - $totalIngresaron,
            'tasaAsistencia' => round($tasaAsistencia, 2),
        ];

        $eventoSeleccionado = $eventoIdSeleccionado ? Evento::find($eventoIdSeleccionado) : null;

        return view('usuarios.metricas', compact(
            'usuario',
            'metricas',
            'eventos',
            'eventoSeleccionado',
            'invitados'
        ));
    }
}
