<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function la_pagina_de_perfil_se_muestra_a_un_usuario_autenticado(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/profile');
        $response->assertOk();
    }

    #[Test]
    public function la_pagina_de_perfil_no_se_muestra_a_un_invitado(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }
}
