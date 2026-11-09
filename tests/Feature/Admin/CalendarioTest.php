<?php

namespace Tests\Feature\Admin;

use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
    use RefreshDatabase;

    private function gestor(): User
    {
        return User::factory()->create(['role' => 'gestor', 'active' => true]);
    }

    private function semestre(string $nome = '2027.1'): Semestre
    {
        return Semestre::create(['nome' => $nome, 'inicio' => '2027-03-01', 'fim' => '2027-07-31']);
    }

    public function test_semestre_vigente_pela_data(): void
    {
        $s = $this->semestre();
        $this->travelTo(Carbon::parse('2027-05-10 10:00'));
        $this->assertTrue($s->is(Semestre::vigente()));
    }

    public function test_ultimo_dia_as_23h30_ainda_e_vigente(): void
    {
        $s = $this->semestre();
        $this->travelTo(Carbon::parse('2027-07-31 23:30'));
        $this->assertTrue($s->is(Semestre::vigente()));
    }

    public function test_noite_do_ultimo_dia_no_fuso_da_bahia_ainda_e_vigente(): void
    {
        $s = $this->semestre();
        $this->travelTo(Carbon::parse('2027-07-31 23:30', 'America/Bahia'));
        $this->assertTrue($s->is(Semestre::vigente()));
    }

    public function test_dia_da_semana_segue_o_fuso_da_bahia_a_noite(): void
    {
        $this->travelTo(Carbon::parse('2027-05-10 21:30', 'America/Bahia')); // segunda-feira
        $this->assertSame(1, now()->dayOfWeek);
    }

    public function test_gestor_corrige_datas_do_semestre(): void
    {
        $s = $this->semestre();

        $this->actingAs($this->gestor())->put(route('admin.semestres.update', $s), [
            'inicio' => '2027-03-10', 'fim' => '2027-08-05',
        ])->assertSessionHasNoErrors();

        $this->assertSame('2027-03-10', $s->fresh()->inicio->toDateString());
    }

    public function test_correcao_nao_pode_sobrepor_outro_semestre(): void
    {
        $s = $this->semestre();
        Semestre::create(['nome' => '2027.2', 'inicio' => '2027-08-10', 'fim' => '2027-12-20']);

        $this->actingAs($this->gestor())->put(route('admin.semestres.update', $s), [
            'inicio' => '2027-03-01', 'fim' => '2027-08-15',
        ])->assertSessionHasErrors('inicio');
    }

    public function test_semestre_com_horarios_nao_pode_ser_excluido(): void
    {
        $s = $this->semestre();
        $this->travelTo(Carbon::parse('2028-05-10 10:00'));
        \App\Models\Horario::create([
            'semestre_id' => $s->id, 'dia_semana' => 'segunda', 'disciplina' => 'A',
            'professor' => 'P', 'inicio' => '08:00', 'fim' => '09:00',
        ]);

        $this->actingAs($this->gestor())->delete(route('admin.semestres.destroy', $s))->assertSessionHas('error');

        $this->assertNotNull($s->fresh());
    }

    public function test_departamento_nao_altera_datas(): void
    {
        $s = $this->semestre();

        $this->actingAs(User::factory()->create(['role' => 'unidade']))
            ->put(route('admin.semestres.update', $s), ['inicio' => '2027-03-10', 'fim' => '2027-08-05'])
            ->assertForbidden();
    }

    public function test_fora_de_semestre_nao_ha_vigente(): void
    {
        $this->semestre();
        $this->travelTo(Carbon::parse('2027-08-01 00:10'));
        $this->assertNull(Semestre::vigente());
    }

    public function test_semestres_sobrepostos_sao_recusados(): void
    {
        $this->semestre();

        $this->actingAs($this->gestor())->post(route('admin.semestres.store'), [
            'nome' => '2027.2', 'inicio' => '2027-07-31', 'fim' => '2027-12-20',
        ])->assertSessionHasErrors('inicio');

        $this->assertSame(1, Semestre::count());
    }

    public function test_semestre_sem_sobreposicao_e_criado(): void
    {
        $this->semestre();

        $this->actingAs($this->gestor())->post(route('admin.semestres.store'), [
            'nome' => '2027.2', 'inicio' => '2027-08-01', 'fim' => '2027-12-20',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Semestre::count());
    }

    public function test_fim_antes_do_inicio_e_recusado(): void
    {
        $this->actingAs($this->gestor())->post(route('admin.semestres.store'), [
            'nome' => '2027.1', 'inicio' => '2027-07-31', 'fim' => '2027-03-01',
        ])->assertSessionHasErrors('fim');
    }

    public function test_semestre_vigente_nao_pode_ser_excluido(): void
    {
        $s = $this->semestre();
        $this->travelTo(Carbon::parse('2027-05-10 10:00'));

        $this->actingAs($this->gestor())->delete(route('admin.semestres.destroy', $s));

        $this->assertNotNull($s->fresh());
    }
}
