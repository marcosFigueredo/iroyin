<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('cidade_clima', 100)->default('Alagoinhas');
            $table->string('weather_api_key', 100)->default('');
            $table->unsignedSmallInteger('duracao_horarios')->default(120); // segundos
            $table->unsignedSmallInteger('duracao_noticia')->default(30);   // segundos
            $table->string('tema', 30)->default('azul-uneb');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};
