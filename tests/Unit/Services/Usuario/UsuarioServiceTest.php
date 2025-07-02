<?php

namespace Tests\Unit\Services\Usuario;

use Tests\TestCase;
use App\Models\User;
use App\Services\Usuario\UsuarioService;
use App\Interfaces\UsuarioRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Exception;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test; // Usar el atributo

class UsuarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private UsuarioRepositoryInterface|MockInterface $mockRepositorio;
    private UsuarioService $usuarioService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepositorio = $this->mock(UsuarioRepositoryInterface::class);
        $this->usuarioService = new UsuarioService($this->mockRepositorio);
    }

    #[Test]
    public function puede_crear_un_usuario(): void
    {
        $datos = ['nombre_completo' => 'Jane Doe', 'usuario' => 'jane', 'rol' => 'RRPP', 'password' => 'password'];
        $this->mockRepositorio->shouldReceive('create')->once()->with($datos)->andReturn(new User($datos));
        $user = $this->usuarioService->createUsuario($datos);
        $this->assertInstanceOf(User::class, $user);
    }

    #[Test]
    public function puede_actualizar_un_usuario_sin_cambiar_password(): void
    {
        $user = User::factory()->make(['id' => 1]);
        $datos = ['nombre_completo' => 'Jane Updated', 'usuario' => 'jane_up', 'rol' => 'CAJERO', 'password' => ''];
        $this->mockRepositorio->shouldReceive('update')->once()->andReturn(true);
        $this->assertTrue($this->usuarioService->updateUsuario($user, $datos));
    }

    #[Test]
    public function puede_actualizar_un_usuario_y_cambiar_la_password(): void
    {
        $user = User::factory()->make(['id' => 1]);
        $datos = ['nombre_completo' => 'Jane Updated', 'usuario' => 'jane_up', 'rol' => 'CAJERO', 'password' => 'nueva_pass'];
        $this->mockRepositorio->shouldReceive('update')->once()->andReturn(true);
        $this->assertTrue($this->usuarioService->updateUsuario($user, $datos));
    }

    #[Test]
    public function puede_eliminar_un_usuario(): void
    {
        Auth::shouldReceive('id')->andReturn(1);
        $usuarioAEliminar = User::factory()->make(['id' => 2]);
        $this->mockRepositorio->shouldReceive('delete')->once()->with($usuarioAEliminar)->andReturn(true);
        $this->assertTrue($this->usuarioService->deleteUsuario($usuarioAEliminar));
    }

    #[Test]
    public function no_se_puede_eliminar_a_si_mismo(): void
    {
        $this->expectException(Exception::class);
        $usuario = User::factory()->make(['id' => 1]);
        Auth::shouldReceive('id')->andReturn($usuario->id);
        $this->usuarioService->deleteUsuario($usuario);
    }
}
