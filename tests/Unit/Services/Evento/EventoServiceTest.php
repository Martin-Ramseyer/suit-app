<?php

namespace Tests\Unit\Services;

use App\Interfaces\EventoRepositoryInterface;
use App\Models\Evento;
use App\Services\Evento\EventoService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Database\Eloquent\Collection;

class EventoServiceTest extends TestCase
{
    private EventoRepositoryInterface|MockInterface $mockRepositorio;
    private EventoService $eventoService;

    // Preparamos el mock del repositorio y la instancia del servicio antes de cada test.
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepositorio = $this->mock(EventoRepositoryInterface::class);
        $this->eventoService = new EventoService($this->mockRepositorio);
    }

    #[Test]
    public function puede_obtener_todos_los_eventos(): void
    {
        // Arrange: Esperamos que el repositorio devuelva una colección vacía.
        $this->mockRepositorio->shouldReceive('allOrderedByDate')
            ->once()
            ->andReturn(new Collection());

        // Act: Llamamos al método del servicio.
        $resultado = $this->eventoService->getAllEventos();

        // Assert: Verificamos que el resultado sea una instancia de Collection.
        $this->assertInstanceOf(Collection::class, $resultado);
    }

    #[Test]
    public function puede_crear_un_evento(): void
    {
        // Arrange
        $datosEvento = [
            'fecha_evento' => '2025-01-01',
            'descripcion' => 'Evento de prueba',
            'precio_entrada' => 1000,
        ];

        // Esperamos que se llame al método `create` del repositorio con los datos correctos.
        $this->mockRepositorio->shouldReceive('create')
            ->once()
            ->with($datosEvento)
            ->andReturn(new Evento($datosEvento));

        // Act
        $eventoCreado = $this->eventoService->createEvento($datosEvento);

        // Assert
        $this->assertInstanceOf(Evento::class, $eventoCreado);
        $this->assertEquals('Evento de prueba', $eventoCreado->descripcion);
    }

    #[Test]
    public function puede_actualizar_un_evento(): void
    {
        // Arrange
        $evento = Evento::factory()->make(); // Creamos una instancia en memoria.
        $datosActualizados = ['descripcion' => 'Descripción actualizada'];

        // Esperamos que se llame al método `update` del repositorio.
        $this->mockRepositorio->shouldReceive('update')
            ->once()
            ->with($evento, $datosActualizados)
            ->andReturn(true);

        // Act
        $resultado = $this->eventoService->updateEvento($evento, $datosActualizados);

        // Assert
        $this->assertTrue($resultado);
    }

    #[Test]
    public function puede_eliminar_un_evento(): void
    {
        // Arrange
        $evento = Evento::factory()->make();
        $this->mockRepositorio->shouldReceive('delete')
            ->once()
            ->with($evento)
            ->andReturn(true);

        // Act & Assert
        $this->assertTrue($this->eventoService->deleteEvento($evento));
    }

    #[Test]
    public function puede_cambiar_el_estado_activo_de_un_evento(): void
    {
        // Arrange
        $evento = Evento::factory()->make();
        $this->mockRepositorio->shouldReceive('toggleActivo')
            ->once()
            ->with($evento)
            ->andReturn(true);

        // Act & Assert
        $this->assertTrue($this->eventoService->toggleActivo($evento));
    }
}
