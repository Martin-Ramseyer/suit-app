<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\InvitadoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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
});

Route::middleware('role:RRPP,ADMIN,CAJERO')->group(function () {
    Route::resource('invitados', InvitadoController::class);
    Route::post('/invitados/{invitado}/toggle-ingreso', [InvitadoController::class, 'toggleIngreso'])->name('invitados.toggleIngreso');
});

require __DIR__ . '/auth.php';
