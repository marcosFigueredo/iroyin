<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_page_is_accessible_when_no_users_exist(): void
    {
        $response = $this->get('/setup');

        $response->assertStatus(200);
        $response->assertSee('IROYIN');
    }

    public function test_setup_redirects_to_login_when_users_already_exist(): void
    {
        User::factory()->create();

        $response = $this->get('/setup');

        $response->assertRedirect('/login');
    }

    public function test_setup_creates_admin_and_redirects_to_institution(): void
    {
        $response = $this->post('/setup', [
            'name'                  => 'Admin Teste',
            'email'                 => 'admin@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@test.com',
            'role'  => 'admin',
        ]);

        $response->assertRedirect(route('admin.instituicao.edit'));
    }

    public function test_setup_requires_all_fields(): void
    {
        $response = $this->post('/setup', []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_setup_blocks_post_when_users_already_exist(): void
    {
        User::factory()->create();

        $response = $this->post('/setup', [
            'name'                  => 'Outro Admin',
            'email'                 => 'outro@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('users', ['email' => 'outro@test.com']);
    }
}
