<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UsuarioRepositoryInterface;
use App\Repositories\UsuarioRepository;
use App\Interfaces\InvitadoRepositoryInterface;
use App\Repositories\InvitadoRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UsuarioRepositoryInterface::class,
            UsuarioRepository::class
        );

        // Nueva vinculación para Invitados
        $this->app->bind(
            InvitadoRepositoryInterface::class,
            InvitadoRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
