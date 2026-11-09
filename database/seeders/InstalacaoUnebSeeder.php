<?php

namespace Database\Seeders;

use App\Models\Instituicao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Carga inicial do servidor da UNEB: fontes padrão, campi, departamentos e o
 * usuário gestor. A senha inicial deve ser trocada no primeiro acesso.
 *
 *   php artisan db:seed --class=InstalacaoUnebSeeder --force
 */
class InstalacaoUnebSeeder extends Seeder
{
    public function run(): void
    {
        (new DatabaseSeeder)->run();
        (new UnebSeeder)->run();

        Instituicao::current()->update(['nome' => 'Universidade do Estado da Bahia', 'sigla' => 'UNEB']);

        if (User::exists()) {
            return;
        }

        User::create([
            'name'     => 'Gestor',
            'email'    => 'gestor',
            'password' => Hash::make('iroyn@2027'),
            'role'     => 'gestor',
            'active'   => true,
        ]);

        User::where('email', 'gestor')->update(['email_verified_at' => now()]);
    }
}
