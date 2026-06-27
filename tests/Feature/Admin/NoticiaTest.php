<?php

namespace Tests\Feature\Admin;

use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_view_noticias_index(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get('/admin/noticias')
            ->assertStatus(200);
    }

    public function test_editor_can_create_noticia(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->post('/admin/noticias', [
            'titulo' => 'Nova notícia de teste',
            'fonte'  => 'Fonte Teste',
            'inicio' => now()->format('Y-m-d\TH:i'),
            'fim'    => now()->addDays(7)->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('noticias', ['titulo' => 'Nova notícia de teste']);
    }

    public function test_admin_can_delete_noticia(): void
    {
        $admin   = User::factory()->admin()->create();
        $noticia = Noticia::create([
            'titulo' => 'Notícia a remover',
            'fonte'  => 'Teste',
            'inicio' => now(),
            'fim'    => now()->addDays(7),
        ]);

        $this->actingAs($admin)
            ->delete("/admin/noticias/{$noticia->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('noticias', ['id' => $noticia->id]);
    }

    public function test_noticia_requires_titulo_and_dates(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->post('/admin/noticias', [])
            ->assertSessionHasErrors(['titulo', 'inicio', 'fim']);
    }
}
