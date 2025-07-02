<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado; // **CORRECCIÓN**: Se añade la importación que faltaba.
use App\Services\Invitado\InvitadoViewDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Request;
use Exception;

class InvitadoViewDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $rrpp;
    private User $cajero;
    private InvitadoViewDataService $viewDataService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['rol' => 'ADMIN']);
        $this->rrpp = User::factory()->create(['rol' => 'RRPP']);
        $this->cajero = User::factory()->create(['rol' => 'CAJERO']);
        $this->viewDataService = $this->app->make(InvitadoViewDataService::class);
    }

    //================================================================
    // Tests para getInvitadosForIndex()
    //================================================================

    #[Test]
    public function un_cajero_solo_ve_invitados_del_evento_activo(): void
    {
        // Arrange
        $eventoActivo = Evento::factory()->create(['activo' => true]);
        $eventoPasado = Evento::factory()->create(['activo' => false, 'fecha_evento' => now()->subDay()]);

        // Invitados que el cajero DEBERÍA ver
        Invitado::factory()->count(3)->create(['evento_id' => $eventoActivo->id]);
        // Invitados que el cajero NO debería ver
        Invitado::factory()->count(2)->create(['evento_id' => $eventoPasado->id]);

        Auth::login($this->cajero);
        $request = new Request();

        // Act
        $data = $this->viewDataService->getInvitadosForIndex($request);

        // Assert
        $this->assertCount(3, $data['invitados']);
        $this->assertEquals($eventoActivo->id, $data['eventoId']);
    }

    #[Test]
    public function un_rrpp_ve_invitados_del_proximo_evento_por_defecto(): void
    {
        // Arrange
        $eventoProximo = Evento::factory()->create(['fecha_evento' => now()->addDays(2)]);
        $eventoLejano = Evento::factory()->create(['fecha_evento' => now()->addDays(10)]);

        Invitado::factory()->count(2)->create(['usuario_id' => $this->rrpp->id, 'evento_id' => $eventoProximo->id]);
        Invitado::factory()->count(3)->create(['usuario_id' => $this->rrpp->id, 'evento_id' => $eventoLejano->id]);

        Auth::login($this->rrpp);
        $request = new Request();

        // Act
        $data = $this->viewDataService->getInvitadosForIndex($request);

        // Assert: Por defecto, debe seleccionar el evento más próximo y mostrar sus invitados.
        $this->assertCount(2, $data['invitados']);
        $this->assertEquals($eventoProximo->id, $data['eventoId']);
    }

    //================================================================
    // Tests para getDataForCreateForm()
    //================================================================

    #[Test]
    public function un_rrpp_no_puede_acceder_al_formulario_de_creacion_sin_eventos_futuros(): void
    {
        // Arrange: Nos aseguramos de que no hay eventos futuros.
        Evento::factory()->create(['fecha_evento' => now()->subDay()]);

        Auth::login($this->rrpp);

        // Assert: Esperamos la excepción definida en el servicio.
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No hay eventos próximos activos para cargar invitados.');

        // Act
        $this->viewDataService->getDataForCreateForm();
    }

    #[Test]
    public function un_cajero_no_puede_acceder_al_formulario_sin_un_evento_activo(): void
    {
        // Arrange: Nos aseguramos de que ningún evento esté activo.
        Evento::factory()->create(['activo' => false]);

        Auth::login($this->cajero);

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No hay ningún evento activo para cargar invitados en puerta.');

        // Act
        $this->viewDataService->getDataForCreateForm();
    }

    #[Test]
    public function un_cajero_obtiene_solo_el_evento_activo_en_el_formulario(): void
    {
        // Arrange
        Evento::factory()->create(['activo' => true]);
        Evento::factory()->create(['activo' => false]);

        Auth::login($this->cajero);

        // Act
        $data = $this->viewDataService->getDataForCreateForm();

        // Assert: El array de eventos solo debe contener 1 elemento (el activo).
        $this->assertCount(1, $data['eventos']);
        // **CORRECCIÓN**: Se cambia assertTrue por assertEquals para comparar correctamente.
        $this->assertEquals(true, $data['eventos']->first()->activo);
    }
}
