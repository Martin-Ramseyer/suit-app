<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Models\Evento;
use App\Models\Invitado;
use App\Models\Beneficio;
use App\Services\Evento\EventoMetricasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EventoMetricasServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventoMetricasService $metricasService;

    // Se ejecuta antes de cada test para tener una instancia del servicio
    protected function setUp(): void
    {
        parent::setUp();
        // Resolvemos el servicio desde el contenedor de Laravel para que inyecte el repositorio real.
        $this->metricasService = $this->app->make(EventoMetricasService::class);

        // Creamos los beneficios que usaremos en los tests.
        Beneficio::factory()->create(['nombre_beneficio' => 'Entrada Free']);
        Beneficio::factory()->create(['nombre_beneficio' => 'Consumición']);
    }

    #[Test]
    public function calcula_correctamente_las_metricas_del_historial_de_un_evento(): void
    {
        // Arrange: Creamos un escenario complejo de datos.
        $evento = Evento::factory()->create(['precio_entrada' => 2000]);
        $rrpp1 = User::factory()->create(['rol' => 'RRPP']);
        $rrpp2 = User::factory()->create(['rol' => 'RRPP']);

        // Invitados de RRPP 1 (3 principales, 3 acompañantes, 6 personas en total, 3 ingresan)
        Invitado::factory()->create(['usuario_id' => $rrpp1->id, 'evento_id' => $evento->id, 'ingreso' => true, 'numero_acompanantes' => 1]); // Ingresan 2
        Invitado::factory()->create(['usuario_id' => $rrpp1->id, 'evento_id' => $evento->id, 'ingreso' => true, 'numero_acompanantes' => 0]); // Ingresa 1
        Invitado::factory()->create(['usuario_id' => $rrpp1->id, 'evento_id' => $evento->id, 'ingreso' => false, 'numero_acompanantes' => 2]); // No ingresan

        // Invitados de RRPP 2 (1 principal, 2 acompañantes, 3 personas en total, 3 ingresan)
        $invitadoConBeneficios = Invitado::factory()->create(['usuario_id' => $rrpp2->id, 'evento_id' => $evento->id, 'ingreso' => true, 'numero_acompanantes' => 2]); // Ingresan 3

        // Asignamos beneficios al invitado que ingresó
        $beneficioFree = Beneficio::where('nombre_beneficio', 'Entrada Free')->first();
        $invitadoConBeneficios->beneficios()->attach($beneficioFree->id, ['cantidad' => 1]);


        // Act: Llamamos al servicio como lo haría el controlador.
        $request = new Request(['evento_id' => $evento->id]);
        $data = $this->metricasService->getHistorialData($request);
        $metricas = $data['metricas'];

        // Assert: Verificamos todos los cálculos.
        // **CORRECCIÓN AQUÍ**: El total de personas es 9 (4 invitados + 5 acompañantes)
        $this->assertEquals(9, $metricas['totalInvitados']);
        $this->assertEquals(6, $metricas['invitadosIngresaron']); // (1+1) + (1+0) + (1+2) = 6 ingresos
        $this->assertStringContainsString($rrpp2->nombre_completo, $metricas['topRrpp']);
        $this->assertStringContainsString($rrpp1->nombre_completo, $metricas['bottomRrpp']);

        // Ingresos = (Total Ingresos - Entradas Free) * Precio
        // Ingresos = (6 - 1) * 2000 = 10000
        $this->assertEquals(10000, $metricas['ingresosEstimados']);
        $this->assertEquals(1, $metricas['beneficios']['Entrada Free']);
    }

    #[Test]
    public function devuelve_datos_correctos_para_el_grafico_del_dashboard(): void
    {
        // Arrange
        $evento = Evento::factory()->create();
        $rrpp1 = User::factory()->create(['rol' => 'RRPP', 'nombre_completo' => 'RRPP A']);
        $rrpp2 = User::factory()->create(['rol' => 'RRPP', 'nombre_completo' => 'RRPP B']);
        Invitado::factory()->create(['usuario_id' => $rrpp1->id, 'evento_id' => $evento->id, 'ingreso' => true, 'numero_acompanantes' => 4]); // 5 ingresos
        Invitado::factory()->create(['usuario_id' => $rrpp2->id, 'evento_id' => $evento->id, 'ingreso' => true, 'numero_acompanantes' => 1]); // 2 ingresos

        // Act
        $chartData = $this->metricasService->getChartDataForEvento($evento);

        // Assert
        $this->assertCount(2, $chartData['labels']);
        $this->assertEquals('RRPP A', $chartData['labels'][0]); // Ordenado por más ingresos
        $this->assertEquals(5, $chartData['datasets'][1]['data'][0]); // Ingresos RRPP A
        $this->assertEquals(2, $chartData['datasets'][1]['data'][1]); // Ingresos RRPP B
    }
}
