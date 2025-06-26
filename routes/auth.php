<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

// Rutas para usuarios no autenticados (invitados)
Route::middleware('guest')->group(function () {
    // Muestra el formulario de login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Procesa el intento de login
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Rutas para usuarios ya autenticados
Route::middleware('auth')->group(function () {
    // Permite al usuario actualizar su propia contraseña (desde el perfil)
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Cierra la sesión del usuario
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
