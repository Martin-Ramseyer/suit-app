<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado;
use App\Services\Invitado\InvitadoAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InvitadoAuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $rrpp1;
    private User $rrpp2;
    private Invitado $invitadoRrpp1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rrpp1 = User::factory()->create(['rol' => 'RRPP']);
        $this->rrpp2 = User::factory()->create(['rol' => 'RRPP']);
        $evento = Evento::factory()->create();
        $this->invitadoRrpp1 = Invitado::factory()->create([
            'usuario_id' => $this->rrpp1->id,
            'evento_id' => $evento->id,
        ]);
    }

    #[Test]
    public function un_rrpp_no_puede_realizar_acciones_sobre_invitados_de_otro_rrpp(): void
    {
        $authService = $this->app->make(InvitadoAuthorizationService::class);

        // Simulamos que el RRPP 2 está logueado
        Auth::login($this->rrpp2);

        // Esperamos la excepción de autorización (403 Forbidden)
        $this->expectException(HttpException::class);

        // El servicio debería detener la ejecución aquí
        $authService->authorizeOwnership($this->invitadoRrpp1);
    }

    #[Test]
    public function un_rrpp_si_puede_realizar_acciones_sobre_su_propio_invitado(): void
    {
        $authService = $this->app->make(InvitadoAuthorizationService::class);

        // Simulamos que el RRPP 1 (dueño) está logueado
        Auth::login($this->rrpp1);

        // No debería lanzar ninguna excepción
        $authService->authorizeOwnership($this->invitadoRrpp1);

        // Afirmamos que el test llega a este punto sin errores
        $this->assertTrue(true);
    }
}
