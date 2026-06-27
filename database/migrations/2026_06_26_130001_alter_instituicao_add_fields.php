<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituicao', function (Blueprint $table) {
            $table->string('departamento', 255)->after('sigla')->default('');
            $table->string('cidade', 100)->after('departamento')->default('');
            $table->string('estado', 100)->after('cidade')->default('');
            $table->dropColumn('titulo_kiosk');
        });

        // Migra dados existentes: titulo_kiosk já foi removido; popula cidade/estado se vazio
        DB::table('instituicao')->whereNull('departamento')->orWhere('departamento', '')->update([
            'departamento' => DB::raw('nome'),
        ]);

        // Renomeia temas antigos para os novos nomes simples
        DB::table('configuracoes')->where('tema', 'azul-uneb')    ->update(['tema' => 'azul']);
        DB::table('configuracoes')->where('tema', 'verde-ifba')   ->update(['tema' => 'verde']);
        DB::table('configuracoes')->where('tema', 'vermelho-ufba')->update(['tema' => 'vermelho']);
    }

    public function down(): void
    {
        Schema::table('instituicao', function (Blueprint $table) {
            $table->string('titulo_kiosk', 255)->after('sigla')->default('');
            $table->dropColumn(['departamento', 'cidade', 'estado']);
        });
    }
};
