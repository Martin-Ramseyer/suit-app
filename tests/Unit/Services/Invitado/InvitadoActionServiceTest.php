<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado;
use App\Services\Invitado\InvitadoActionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Exception;

class InvitadoActionServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $cajero;
    private Evento $eventoActivo;
    private Evento $eventoFuturo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cajero = User::factory()->create(['rol' => 'CAJERO']);
        $this->eventoActivo = Evento::factory()->create(['activo' => true, 'fecha_evento' => now()->addDay()]);
        $this->eventoFuturo = Evento::factory()->create(['activo' => false, 'fecha_evento' => now()->addMonth()]);
    }

    #[Test]
    public function un_cajero_puede_marcar_el_ingreso_de_un_invitado_solo_para_el_evento_activo(): void
    {
        $actionService = $this->app->make(InvitadoActionService::class);
        $invitadoEventoActivo = Invitado::factory()->create(['evento_id' => $this->eventoActivo->id, 'ingreso' => false]);

        Auth::login($this->cajero);

        $resultado = $actionService->toggleIngreso($invitadoEventoActivo, true);

        $this->assertTrue($resultado);
        // **CORRECCIÓN**: Usamos assertEquals para evitar problemas con tipos (1 vs true).
        $this->assertEquals(true, $invitadoEventoActivo->fresh()->ingreso);
    }

    #[Test]
    public function un_cajero_no_puede_marcar_ingreso_para_un_evento_no_activo(): void
    {
        $actionService = $this->app->make(InvitadoActionService::class);
        $invitadoEventoFuturo = Invitado::factory()->create(['evento_id' => $this->eventoFuturo->id]);

        Auth::login($this->cajero);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Solo se puede modificar el ingreso de invitados para el evento activo.');

        $actionService->toggleIngreso($invitadoEventoFuturo, true);
    }
}
