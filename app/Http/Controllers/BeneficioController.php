<?php

namespace App\Http\Controllers;

use App\Models\Beneficio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BeneficioController extends Controller
{
    public function index()
    {
        $beneficios = Beneficio::all();
        return view('beneficios.index', compact('beneficios'));
    }

    public function create()
    {
        return view('beneficios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_beneficio' => 'required|string|max:255|unique:beneficios,nombre_beneficio',
        ]);

        Beneficio::create($request->all());

        return redirect()->route('beneficios.index')->with('success', 'Beneficio creado exitosamente.');
    }

    public function edit(Beneficio $beneficio)
    {
        return view('beneficios.edit', compact('beneficio'));
    }

    public function update(Request $request, Beneficio $beneficio)
    {
        $request->validate([
            'nombre_beneficio' => ['required', 'string', 'max:255', Rule::unique('beneficios')->ignore($beneficio->id)],
        ]);

        $beneficio->update($request->all());

        return redirect()->route('beneficios.index')->with('success', 'Beneficio actualizado exitosamente.');
    }

    public function destroy(Beneficio $beneficio)
    {
        // Opcional: Añadir lógica para evitar borrar beneficios que ya están en uso.
        if ($beneficio->invitados()->count() > 0) {
            return redirect()->route('beneficios.index')->with('error', 'No se puede eliminar un beneficio que ya ha sido asignado a invitados.');
        }

        $beneficio->delete();
        return redirect()->route('beneficios.index')->with('success', 'Beneficio eliminado exitosamente.');
    }
}
