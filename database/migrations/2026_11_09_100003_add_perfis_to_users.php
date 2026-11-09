<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('unidade')->change();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->restrictOnDelete();
            $table->foreignId('unidade_id')->nullable()->constrained('unidades')->restrictOnDelete();
        });

        DB::table('users')->where('role', 'admin')->update(['role' => 'gestor']);
        DB::table('users')->where('role', 'editor')->update(['role' => 'departamento']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidade_id');
            $table->dropConstrainedForeignId('departamento_id');
        });
    }
};
