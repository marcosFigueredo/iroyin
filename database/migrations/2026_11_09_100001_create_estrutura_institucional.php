<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campi', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('numero')->nullable();
            $table->string('nome', 120);
            $table->string('cidade', 100);
            $table->string('estado', 2)->default('BA');
            $table->timestamps();
        });

        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campi')->restrictOnDelete();
            $table->string('sigla', 30);
            $table->string('nome');
            $table->string('slug', 60)->unique();
            $table->boolean('ativo')->default(true);
            $table->string('tema', 20)->default('azul');
            $table->unsignedSmallInteger('duracao_horarios')->default(120);
            $table->unsignedSmallInteger('duracao_noticia')->default(30);
            $table->timestamps();
        });

        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos')->restrictOnDelete();
            $table->string('tipo', 20);
            $table->string('nome');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->unique(['departamento_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
        Schema::dropIfExists('departamentos');
        Schema::dropIfExists('campi');
    }
};
