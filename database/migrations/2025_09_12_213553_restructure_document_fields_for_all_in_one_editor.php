<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            // Verificamos si la columna 'tag_id' todavía existe
            if (Schema::hasColumn('document_fields', 'tag_id')) {
                
                // 1. Eliminamos la conexión (llave foránea) PRIMERO 🔗
                $table->dropForeign(['tag_id']);
                
                // 2. Ahora sí, eliminamos la columna 🗑️
                $table->dropColumn('tag_id');
            }

            // El resto del código se asegura de que las otras columnas existan
            if (!Schema::hasColumn('document_fields', 'name')) {
                $table->string('name')->after('document_id');
            }
            if (!Schema::hasColumn('document_fields', 'type')) {
                $table->string('type')->after('name');
            }
            if (!Schema::hasColumn('document_fields', 'value')) {
                $table->longText('value')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        // Esto es opcional, define cómo revertir si es necesario
        Schema::table('document_fields', function (Blueprint $table) {
            $table->dropColumn(['name', 'type', 'value']);
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
        });
    }
};