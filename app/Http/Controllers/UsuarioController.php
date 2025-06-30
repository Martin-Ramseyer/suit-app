<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Usuario\UsuarioService;
use App\Services\Usuario\MetricasService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    protected $usuarioService;
    protected $metricasService;

    public function __construct(UsuarioService $usuarioService, MetricasService $metricasService)
    {
        $this->usuarioService = $usuarioService;
        $this->metricasService = $metricasService;
    }

    public function index()
    {
        $usuarios = $this->usuarioService->getAllUsuarios();
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

        $this->usuarioService->createUsuario($request->all());

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

        $this->usuarioService->updateUsuario($usuario, $request->all());

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        try {
            $this->usuarioService->deleteUsuario($usuario);
            return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
    }

    public function metricas(Request $request, User $usuario)
    {
        try {
            $data = $this->metricasService->getMetricasRrpp($request, $usuario);
            return view('usuarios.metricas', $data);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
    }
}
