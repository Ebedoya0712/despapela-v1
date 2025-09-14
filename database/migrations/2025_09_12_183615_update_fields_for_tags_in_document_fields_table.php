<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            // Eliminamos las columnas viejas
            $table->dropColumn(['name', 'type', 'value']);

            // Añadimos la nueva columna para la relación con tags
            $table->foreignId('tag_id')->after('document_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            // Permite revertir la migración
            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');

            $table->string('name')->after('document_id');
            $table->string('type')->after('name');
            $table->text('value')->nullable()->after('type');
        });
    }
};