<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function la_password_puede_ser_actualizada(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password'      => 'password',
                'password'              => 'nueva-password-segura',
                'password_confirmation' => 'nueva-password-segura',
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->assertTrue(Hash::check('nueva-password-segura', $user->refresh()->password));
    }

    #[Test]
    public function se_debe_proveer_la_password_actual_correcta_para_actualizar(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password'      => 'password-incorrecta',
                'password'              => 'nueva-password-segura',
                'password_confirmation' => 'nueva-password-segura',
            ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password')->assertRedirect('/profile');
    }
}
