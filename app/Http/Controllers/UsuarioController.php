<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        // Obtenemos todos los usuarios para listarlos
        $usuarios = User::all();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        // Simplemente muestra la vista para crear un usuario
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        // Validación para la creación de un nuevo usuario
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:usuarios',
            'rol' => ['required', Rule::in(['ADMIN', 'RRPP', 'CAJERO'])],
            'password' => 'required|string|min:4|confirmed',
        ]);

        // Creación del nuevo usuario
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
        // Muestra la vista para editar, pasando el usuario específico
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        // Validación para la actualización
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:usuarios,usuario,' . $usuario->id,
            'rol' => ['required', Rule::in(['ADMIN', 'RRPP', 'CAJERO'])],
            'password' => 'nullable|string|min:4|confirmed', // La contraseña es opcional al actualizar
        ]);

        // Prepara los datos para actualizar
        $data = $request->only(['nombre_completo', 'usuario', 'rol']);

        // Si se proporcionó una nueva contraseña, la hasheamos y la añadimos a los datos
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        // Evitar que un usuario se elimine a sí mismo
        if (Auth::id() == $usuario->id) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
