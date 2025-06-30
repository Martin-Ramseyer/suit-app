<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InvitadoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->rol !== 'ADMIN') {
        return redirect()->route('invitados.index');
    }

    // Lógica para el dashboard del ADMIN
    $eventoController = new EventoController();
    $ultimoEvento = App\Models\Evento::orderBy('fecha_evento', 'desc')->first();
    $metricasUltimoEvento = [];

    if ($ultimoEvento) {
        $metricasUltimoEvento = $eventoController->obtenerMetricasDeEvento($ultimoEvento->id);
    }

    return view('dashboard', compact('ultimoEvento', 'metricasUltimoEvento'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('role:ADMIN')->group(function () {
    Route::resource('usuarios', UsuarioController::class);
    Route::get('usuarios/{usuario}/metricas', [UsuarioController::class, 'metricas'])->name('usuarios.metricas');

    Route::resource('eventos', EventoController::class);
    Route::get('historial/eventos', [EventoController::class, 'historial'])->name('eventos.historial');

    Route::post('eventos/{evento}/toggle-activo', [EventoController::class, 'toggleActivo'])->name('eventos.toggleActivo');
});

// --- SECCIÓN DE INVITADOS REESTRUCTURADA ---

// Rutas accesibles para todos los roles (ver lista, acceder al formulario de creación y guardar)
Route::middleware('role:RRPP,ADMIN,CAJERO')->group(function () {
    Route::get('invitados', [InvitadoController::class, 'index'])->name('invitados.index');
    Route::get('invitados/create', [InvitadoController::class, 'create'])->name('invitados.create');
    Route::post('invitados', [InvitadoController::class, 'store'])->name('invitados.store');
});

// Rutas para la edición completa (Editar, Actualizar, Eliminar) solo para Admin y RRPP
Route::middleware('role:ADMIN,RRPP')->group(function () {
    Route::get('invitados/{invitado}/edit', [InvitadoController::class, 'edit'])->name('invitados.edit');
    Route::put('invitados/{invitado}', [InvitadoController::class, 'update'])->name('invitados.update');
    Route::delete('invitados/{invitado}', [InvitadoController::class, 'destroy'])->name('invitados.destroy');
});

// Grupo de rutas para acciones específicas del Cajero y Admin
Route::middleware('role:ADMIN,CAJERO')->group(function () {
    Route::post('/invitados/{invitado}/toggle-ingreso', [InvitadoController::class, 'toggleIngreso'])->name('invitados.toggleIngreso');
    Route::patch('/invitados/{invitado}/update-acompanantes', [InvitadoController::class, 'updateAcompanantes'])->name('invitados.updateAcompanantes');
});


require __DIR__ . '/auth.php';
