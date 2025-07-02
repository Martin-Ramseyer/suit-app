<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function la_pantalla_de_login_se_muestra_correctamente(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    #[Test]
    public function un_usuario_admin_es_redirigido_al_dashboard_al_iniciar_sesion(): void
    {
        $user = User::factory()->create(['rol' => 'ADMIN']);
        $this->post('/login', ['usuario' => $user->usuario, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function un_usuario_rrpp_o_cajero_es_redirigido_a_invitados_al_iniciar_sesion(): void
    {
        $rrpp = User::factory()->create(['rol' => 'RRPP']);
        $this->post('/login', ['usuario' => $rrpp->usuario, 'password' => 'password'])
            ->assertRedirect(route('invitados.index'));

        $this->post('/logout'); // Cerramos sesión para probar el siguiente

        $cajero = User::factory()->create(['rol' => 'CAJERO']);
        $this->post('/login', ['usuario' => $cajero->usuario, 'password' => 'password'])
            ->assertRedirect(route('invitados.index'));
    }

    #[Test]
    public function un_usuario_no_puede_iniciar_sesion_con_password_incorrecta(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['usuario' => $user->usuario, 'password' => 'wrong-password']);
        $this->assertGuest();
    }
}
