<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semestres', function (Blueprint $table) {
            $table->date('inicio')->nullable();
            $table->date('fim')->nullable();
        });

        Schema::table('semestres', function (Blueprint $table) {
            $table->dropColumn('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('semestres', function (Blueprint $table) {
            $table->boolean('ativo')->default(false);
        });

        Schema::table('semestres', function (Blueprint $table) {
            $table->dropColumn(['inicio', 'fim']);
        });
    }
};
