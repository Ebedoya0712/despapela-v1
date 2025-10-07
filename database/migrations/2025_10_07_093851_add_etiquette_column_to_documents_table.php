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
        // Añadimos la columna 'etiquette' como JSON.
        // Usamos 'nullable()' ya que no todos los documentos podrían tener una etiqueta inicial.
        // La colocamos después de 'status' para mantener un orden lógico.
        Schema::table('documents', function (Blueprint $table) {
            $table->json('etiquette')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Al revertir la migración, eliminamos la columna 'etiquette'.
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('etiquette');
        });
    }
};
