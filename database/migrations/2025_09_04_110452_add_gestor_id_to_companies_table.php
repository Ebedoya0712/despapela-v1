<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Añadimos la columna para el gestor.
            // Es nullable() porque un gestor puede registrarse antes de crear su empresa.
            $table->foreignId('gestor_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Elimina la clave foránea y la columna si hacemos rollback
            $table->dropForeign(['gestor_id']);
            $table->dropColumn('gestor_id');
        });
    }
};
