<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->restrictOnDelete();
            $table->foreignId('unidade_id')->nullable()->constrained('unidades')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('formato', 10)->default('texto');
            $table->string('titulo', 70);
            $table->string('texto', 160)->nullable();
            $table->string('imagem')->nullable();
            $table->string('urgencia', 12)->default('normal');
            $table->dateTime('inicio');
            $table->dateTime('fim');
            $table->timestamps();
            $table->index(['departamento_id', 'inicio', 'fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes');
    }
};
