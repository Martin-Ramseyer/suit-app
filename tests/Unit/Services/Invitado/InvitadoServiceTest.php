<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado;
use App\Models\Beneficio;
use App\Services\Invitado\InvitadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InvitadoServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $rrpp1;
    private User $cajero;
    private Evento $eventoActivo;
    private Evento $eventoFuturo;
    private Invitado $invitadoRrpp1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['rol' => 'ADMIN']);
        $this->rrpp1 = User::factory()->create(['rol' => 'RRPP']);
        $this->cajero = User::factory()->create(['rol' => 'CAJERO']);
        $this->eventoActivo = Evento::factory()->create(['activo' => true, 'fecha_evento' => now()->addDay()]);
        $this->eventoFuturo = Evento::factory()->create(['activo' => false, 'fecha_evento' => now()->addMonth()]);
        $this->invitadoRrpp1 = Invitado::factory()->create(['usuario_id' => $this->rrpp1->id, 'evento_id' => $this->eventoFuturo->id]);
        Beneficio::factory()->create(['nombre_beneficio' => 'Consumición']);
    }

    #[Test]
    public function un_rrpp_puede_crear_un_invitado_para_si_mismo(): void
    {
        $invitadoService = $this->app->make(InvitadoService::class);
        $datos = [
            'nombre_completo' => 'Invitado de RRPP1',
            'numero_acompanantes' => 2,
            'evento_id' => $this->eventoFuturo->id,
        ];

        // **CORRECCIÓN**: Autenticamos al usuario antes de llamar al servicio.
        Auth::login($this->rrpp1);

        $invitado = $invitadoService->createInvitado($datos, $this->rrpp1);

        $this->assertDatabaseHas('invitados', [
            'nombre_completo' => 'Invitado de RRPP1',
            'usuario_id' => $this->rrpp1->id,
            'ingreso' => false,
        ]);
    }

    #[Test]
    public function un_cajero_puede_crear_un_invitado_en_puerta_y_se_marca_como_ingresado(): void
    {
        $invitadoService = $this->app->make(InvitadoService::class);
        $datos = [
            'nombre_completo' => 'Invitado de Puerta',
            'numero_acompanantes' => 1,
            'evento_id' => $this->eventoActivo->id,
        ];

        // **CORRECCIÓN**: Autenticamos al usuario antes de llamar al servicio.
        Auth::login($this->cajero);

        $invitado = $invitadoService->createInvitado($datos, $this->cajero);

        $this->assertDatabaseHas('invitados', [
            'nombre_completo' => 'Invitado de Puerta',
            'usuario_id' => $this->cajero->id,
            'ingreso' => true,
        ]);
    }

    #[Test]
    public function un_admin_puede_actualizar_cualquier_invitado_y_asignar_beneficios(): void
    {
        $invitadoService = $this->app->make(InvitadoService::class);
        $beneficio = Beneficio::first();
        $datos = [
            'nombre_completo' => 'Nombre Actualizado por Admin',
            'numero_acompanantes' => 5,
            'evento_id' => $this->eventoFuturo->id,
            'beneficios' => [$beneficio->id => $beneficio->id],
            'cantidades' => [$beneficio->id => 2],
        ];

        // **CORRECCIÓN**: Autenticamos al usuario antes de llamar al servicio.
        Auth::login($this->admin);

        $resultado = $invitadoService->updateInvitado($this->invitadoRrpp1, $datos, $this->admin);

        $this->assertTrue($resultado);
        $this->assertDatabaseHas('invitados', ['nombre_completo' => 'Nombre Actualizado por Admin']);
        $this->assertDatabaseHas('beneficio_invitado', [
            'invitado_id' => $this->invitadoRrpp1->id,
            'beneficio_id' => $beneficio->id,
            'cantidad' => 2,
        ]);
    }
}
