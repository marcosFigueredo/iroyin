<?php

namespace Tests\Feature\Admin;

use App\Models\Semestre;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HorarioTest extends TestCase
{
    use RefreshDatabase;

    private function semestre(): Semestre
    {
        return Semestre::create(['nome' => '2026.1', 'ativo' => true]);
    }

    public function test_admin_can_create_semestre(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/semestres', ['nome' => '2026.2'])
            ->assertRedirect();

        $this->assertDatabaseHas('semestres', ['nome' => '2026.2']);
    }

    public function test_admin_can_add_horario(): void
    {
        $admin    = User::factory()->admin()->create();
        $semestre = $this->semestre();

        $this->actingAs($admin)
            ->post("/admin/semestres/{$semestre->id}/horarios", [
                'dia_semana'  => 'segunda',
                'disciplina'  => 'Cálculo I',
                'professor'   => 'Prof. Teste',
                'curso'       => 'Engenharia',
                'inicio'      => '08:00',
                'fim'         => '09:40',
                'sala'        => 'LAB 01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('horarios', ['disciplina' => 'Cálculo I']);
    }

    public function test_admin_can_delete_horario(): void
    {
        $admin    = User::factory()->admin()->create();
        $semestre = $this->semestre();
        $horario  = Horario::create([
            'semestre_id' => $semestre->id,
            'dia_semana'  => 'terca',
            'disciplina'  => 'Álgebra',
            'professor'   => 'Prof. X',
            'curso'       => 'Matemática',
            'inicio'      => '10:00',
            'fim'         => '11:40',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/semestres/{$semestre->id}/horarios/{$horario->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('horarios', ['id' => $horario->id]);
    }

    public function test_csv_import_creates_horarios(): void
    {
        $admin    = User::factory()->admin()->create();
        $semestre = $this->semestre();

        $csv = "dia,disciplina,professor,curso,inicio,fim,sala\n" .
               "segunda,Física I,Prof. Silva,Engenharia,07:00,08:40,LAB 03\n" .
               "quarta,Química,Prof. Lima,Engenharia,09:00,10:40,LAB 04\n";

        $arquivo = UploadedFile::fake()->createWithContent('horarios.csv', $csv);

        $this->actingAs($admin)
            ->post("/admin/semestres/{$semestre->id}/horarios/importar", [
                'csv' => $arquivo,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('horarios', ['disciplina' => 'Física I']);
        $this->assertDatabaseHas('horarios', ['disciplina' => 'Química']);
    }

    public function test_csv_import_rejects_invalid_columns(): void
    {
        $admin    = User::factory()->admin()->create();
        $semestre = $this->semestre();

        $csv     = "coluna_errada,outra_errada\nvalor,outro\n";
        $arquivo = UploadedFile::fake()->createWithContent('invalido.csv', $csv);

        $this->actingAs($admin)
            ->post("/admin/semestres/{$semestre->id}/horarios/importar", [
                'csv' => $arquivo,
            ])
            ->assertSessionHas('error');
    }
}
