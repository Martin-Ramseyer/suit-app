<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InvitadoController;
use App\Services\Evento\EventoMetricasService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function (EventoMetricasService $metricasService) {
    $user = Auth::user();

    if ($user->rol !== 'ADMIN') {
        return redirect()->route('invitados.index');
    }
    $data = $metricasService->getMetricasParaDashboard();

    return view('dashboard', [
        'ultimoEvento' => $data['ultimoEvento'],
        'metricasUltimoEvento' => $data['metricasUltimoEvento']
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

Route::middleware('role:ADMIN')->group(function () {
    Route::resource('usuarios', UsuarioController::class);
    Route::get('usuarios/{usuario}/metricas', [UsuarioController::class, 'metricas'])->name('usuarios.metricas');

    Route::resource('eventos', EventoController::class);
    Route::get('historial/eventos', [EventoController::class, 'historial'])->name('eventos.historial');

    Route::post('eventos/{evento}/toggle-activo', [EventoController::class, 'toggleActivo'])->name('eventos.toggleActivo');
    Route::get('/api/metricas/evento/{evento}', [EventoController::class, 'getChartData'])->name('api.eventos.chart_data');
});

Route::middleware('role:RRPP,ADMIN,CAJERO')->group(function () {
    Route::get('invitados', [InvitadoController::class, 'index'])->name('invitados.index');
    Route::get('invitados/create', [InvitadoController::class, 'create'])->name('invitados.create');
    Route::post('invitados', [InvitadoController::class, 'store'])->name('invitados.store');
});


Route::middleware('role:ADMIN,RRPP')->group(function () {
    Route::get('invitados/{invitado}/edit', [InvitadoController::class, 'edit'])->name('invitados.edit');
    Route::put('invitados/{invitado}', [InvitadoController::class, 'update'])->name('invitados.update');
    Route::delete('invitados/{invitado}', [InvitadoController::class, 'destroy'])->name('invitados.destroy');
});

Route::middleware('role:ADMIN,CAJERO')->group(function () {
    Route::post('/invitados/{invitado}/toggle-ingreso', [InvitadoController::class, 'toggleIngreso'])->name('invitados.toggleIngreso');
    Route::patch('/invitados/{invitado}/update-acompanantes', [InvitadoController::class, 'updateAcompanantes'])->name('invitados.updateAcompanantes');
});


require __DIR__ . '/auth.php';
