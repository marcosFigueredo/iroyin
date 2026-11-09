<?php

namespace Database\Seeders;

use App\Models\Instituicao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Carga inicial do servidor da UNEB: fontes padrão, campi, departamentos e o
 * usuário gestor. A senha inicial vem do ambiente, nunca do código, e o painel
 * exige a troca no primeiro acesso.
 *
 *   GESTOR_SENHA_INICIAL='...' php artisan db:seed --class=InstalacaoUnebSeeder --force
 */
class InstalacaoUnebSeeder extends Seeder
{
    public function run(): void
    {
        $senha = env('GESTOR_SENHA_INICIAL');

        if (! $senha && ! User::exists()) {
            throw new RuntimeException('Defina GESTOR_SENHA_INICIAL para criar o usuário gestor.');
        }

        (new DatabaseSeeder)->run();
        (new UnebSeeder)->run();

        Instituicao::current()->update(['nome' => 'Universidade do Estado da Bahia', 'sigla' => 'UNEB']);

        if (User::exists()) {
            return;
        }

        User::create([
            'name'     => 'Gestor',
            'email'    => 'gestor',
            'password' => Hash::make($senha),
            'role'     => 'gestor',
            'active'   => true,
            'trocar_senha' => true,
        ]);

        User::where('email', 'gestor')->update(['email_verified_at' => now()]);
    }
}
