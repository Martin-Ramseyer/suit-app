<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Los roles permitidos para acceder a la ruta.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Primero, verificamos si el usuario ha iniciado sesión.
        if (!Auth::check()) {
            return redirect('login');
        }

        // Obtenemos el usuario autenticado.
        $user = Auth::user();

        // Verificamos si el rol del usuario está en la lista de roles permitidos.
        foreach ($roles as $role) {
            if ($user->rol == $role) {
                // Si el rol coincide, dejamos que la petición continúe.
                return $next($request);
            }
        }

        // Si el bucle termina y no se encontró una coincidencia, el usuario no tiene permiso.
        // Lo redirigimos al dashboard con un mensaje de error.
        return redirect('dashboard')->with('error', 'No tienes permiso para acceder a esta sección.');
    }
}
