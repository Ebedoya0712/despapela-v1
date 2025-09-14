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
        Schema::table('document_fields', function (Blueprint $table) {
            // Para guardar el texto que el usuario escribe (ej. "Juan Pérez").
            // Lo hacemos nullable por si algún campo no requiere un valor escrito.
            $table->string('name')->nullable()->after('tag_id');

            // Para guardar la firma en Base64. Usamos longText para asegurar que quepa.
            $table->longText('value')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            // Esto permite revertir la migración si es necesario
            $table->dropColumn(['name', 'value']);
        });
    }
};