<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_manage_content_but_cannot_access_admin_configuration(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->get('/admin/noticias')->assertOk();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_cannot_create_an_unsupported_super_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from('/admin/users/create')
            ->post('/admin/users', [
                'name' => 'Super Admin indevido',
                'email' => 'superadmin@example.test',
                'role' => 'superAdmin',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect('/admin/users/create')
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'superadmin@example.test',
        ]);
    }

    public function test_web_form_without_csrf_token_is_rejected(): void
    {
        $user = User::factory()->create();

        // O middleware ignora CSRF no ambiente "testing". Alterar apenas o
        // ambiente deste teste exercita a mesma proteção usada em produção.
        $this->app->detectEnvironment(fn () => 'local');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(419);

        $this->assertGuest();
    }

    public function test_sixth_invalid_password_attempt_is_rate_limited(): void
    {
        Event::fake([Lockout::class]);

        $user = User::factory()->create();
        $key = Str::transliterate(Str::lower($user->email).'|127.0.0.1');
        RateLimiter::clear($key);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'senha-incorreta',
            ])->assertSessionHasErrors([
                'email' => trans('auth.failed'),
            ]);
        }

        Event::assertNotDispatched(Lockout::class);
        $this->assertTrue(RateLimiter::tooManyAttempts($key, 5));

        $sixthResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-incorreta',
        ])->assertSessionHasErrors('email');

        $message = $sixthResponse->getSession()->get('errors')->first('email');
        $this->assertStringContainsString('Muitas tentativas', $message);
        Event::assertDispatched(Lockout::class, 1);
        $this->assertGuest();
    }
}
