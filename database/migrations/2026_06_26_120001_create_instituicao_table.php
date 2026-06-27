<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituicao', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);           // ex: DEPARTAMENTO DE CIÊNCIAS EXATAS E DA TERRA
            $table->string('sigla', 20);           // ex: DCET
            $table->string('titulo_kiosk', 255);   // ex: Kiosk DCET — UNEB
            $table->string('texto_banner', 100);   // ex: NOTÍCIAS DCET
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituicao');
    }
};
