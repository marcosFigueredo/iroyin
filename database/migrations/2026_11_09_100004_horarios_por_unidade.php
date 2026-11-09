<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->foreignId('unidade_id')->nullable()->constrained('unidades')->restrictOnDelete();
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->dropColumn('curso');
        });

        Schema::table('instituicao', function (Blueprint $table) {
            $table->dropColumn('departamento');
        });

        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropColumn(['cidade_clima', 'duracao_horarios', 'duracao_noticia', 'tema']);
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->string('curso')->default('');
            $table->dropConstrainedForeignId('unidade_id');
        });

        Schema::table('instituicao', function (Blueprint $table) {
            $table->string('departamento')->default('');
        });

        Schema::table('configuracoes', function (Blueprint $table) {
            $table->string('cidade_clima')->default('');
            $table->unsignedSmallInteger('duracao_horarios')->default(120);
            $table->unsignedSmallInteger('duracao_noticia')->default(30);
            $table->string('tema', 20)->default('azul');
        });
    }
};
