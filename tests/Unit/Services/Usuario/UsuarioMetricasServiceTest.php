<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado;
use App\Services\Usuario\UsuarioMetricasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioMetricasServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $metricasService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metricasService = $this->app->make(UsuarioMetricasService::class);
    }

    #[Test]
    public function calcula_correctamente_las_metricas_de_un_rrpp_para_un_evento()
    {
        // Arrange: Crear los datos necesarios
        $rrpp = User::factory()->create(['rol' => 'RRPP']);
        $evento = Evento::factory()->create();

        // Invitado 1: ingresó con 2 acompañantes (Total: 3 personas)
        Invitado::factory()->create([
            'usuario_id' => $rrpp->id,
            'evento_id' => $evento->id,
            'numero_acompanantes' => 2,
            'ingreso' => true,
        ]);

        // Invitado 2: ingresó solo (Total: 1 persona)
        Invitado::factory()->create([
            'usuario_id' => $rrpp->id,
            'evento_id' => $evento->id,
            'numero_acompanantes' => 0,
            'ingreso' => true,
        ]);

        // Invitado 3: NO ingresó, con 1 acompañante (Total: 2 personas)
        Invitado::factory()->create([
            'usuario_id' => $rrpp->id,
            'evento_id' => $evento->id,
            'numero_acompanantes' => 1,
            'ingreso' => false,
        ]);

        // Act: Llamar al servicio
        // Simulamos un request que filtra por ese evento
        $request = new Request(['evento_id' => $evento->id]);
        $metricasData = $this->metricasService->getMetricasRrpp($request, $rrpp);

        // Assert: Verificar los cálculos
        // Total personas = (1+2) + (1+0) + (1+1) = 6
        $this->assertEquals(6, $metricasData['metricas']['totalPersonas']);
        // Total ingresaron = (1+2) + (1+0) = 4
        $this->assertEquals(4, $metricasData['metricas']['totalIngresaron']);
        // Total no ingresaron = 6 - 4 = 2
        $this->assertEquals(2, $metricasData['metricas']['totalNoIngresaron']);
        // Tasa de asistencia = (4 / 6) * 100 = 66.67
        $this->assertEquals(66.67, $metricasData['metricas']['tasaAsistencia']);
    }

    #[Test]
    public function lanza_una_excepcion_si_el_usuario_no_es_rrpp()
    {
        $admin = User::factory()->create(['rol' => 'ADMIN']);
        $request = new Request();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Solo los RRPP pueden tener métricas.');

        $this->metricasService->getMetricasRrpp($request, $admin);
    }
}
