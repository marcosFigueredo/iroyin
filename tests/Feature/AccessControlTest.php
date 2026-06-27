<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_news_index(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get('/admin/noticias')
            ->assertStatus(200);
    }

    public function test_editor_cannot_access_users_management(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get('/admin/users')
            ->assertStatus(403);
    }

    public function test_editor_cannot_access_feeds_management(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get('/admin/feeds')
            ->assertStatus(403);
    }

    public function test_editor_cannot_access_institution_settings(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get('/admin/instituicao')
            ->assertStatus(403);
    }

    public function test_admin_can_access_users_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertStatus(200);
    }

    public function test_admin_can_access_feeds_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/feeds')
            ->assertStatus(200);
    }

    public function test_admin_can_access_institution_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/instituicao')
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get('/admin/noticias')
            ->assertRedirect('/login');
    }
}
