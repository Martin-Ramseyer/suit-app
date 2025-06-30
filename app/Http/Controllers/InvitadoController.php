<?php

namespace App\Http\Controllers;

use App\Models\Invitado;
use App\Services\Invitado\InvitadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvitadoController extends Controller
{
    protected $invitadoService;

    public function __construct(InvitadoService $invitadoService)
    {
        $this->invitadoService = $invitadoService;
    }

    public function index(Request $request)
    {
        $data = $this->invitadoService->getInvitadosForIndex($request);

        if ($request->ajax()) {
            return view('invitados._invitados_table', ['invitados' => $data['invitados']]);
        }

        return view('invitados.index', [
            'invitados' => $data['invitados'],
            'search' => $request->input('search'),
            'eventosParaSelector' => $data['eventosParaSelector'],
            'eventoId' => $data['eventoId'],
            'eventoSeleccionado' => $data['eventoSeleccionado']
        ]);
    }

    public function create()
    {
        try {
            $data = $this->invitadoService->getDataForCreateForm();
            return view('invitados.create', $data);
        } catch (Exception $e) {
            return redirect()->route('invitados.index')->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_acompanantes' => 'required|integer|min:0',
            'evento_id' => 'required|exists:eventos,id',
            'beneficios' => 'nullable|array',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'required_with:beneficios.*|integer|min:1',
        ]);

        $this->invitadoService->createInvitado($request->all(), Auth::user());

        return redirect()->route('invitados.index')->with('success', 'Invitado agregado exitosamente.');
    }

    public function edit(Invitado $invitado)
    {
        // La autorización la maneja el servicio
        $data = $this->invitadoService->getDataForCreateForm();
        return view('invitados.edit', array_merge($data, compact('invitado')));
    }

    public function update(Request $request, Invitado $invitado)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'numero_acompanantes' => 'required|integer|min:0',
            'evento_id' => 'required|exists:eventos,id',
            'beneficios' => 'nullable|array',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'required_with:beneficios.*|integer|min:1',
        ]);

        $this->invitadoService->updateInvitado($invitado, $request->all(), Auth::user());

        return redirect()->route('invitados.index')->with('success', 'Invitado actualizado exitosamente.');
    }

    public function destroy(Invitado $invitado)
    {
        $this->invitadoService->deleteInvitado($invitado);
        return redirect()->route('invitados.index')->with('success', 'Invitado eliminado exitosamente.');
    }

    public function toggleIngreso(Request $request, Invitado $invitado)
    {
        try {
            $nuevoEstado = $this->invitadoService->toggleIngreso($invitado, $request->input('ingreso'));
            return response()->json(['success' => true, 'nuevo_estado' => $nuevoEstado]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    public function updateAcompanantes(Request $request, Invitado $invitado)
    {
        $request->validate(['numero_acompanantes' => 'required|integer|min:0']);
        $this->invitadoService->updateAcompanantes($invitado, $request->numero_acompanantes);
        return redirect()->back()->with('success', 'Número de acompañantes actualizado correctamente.');
    }
}
