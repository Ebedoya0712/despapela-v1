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
            // Añadimos la columna company_id después de document_id
            $table->foreignId('company_id')->constrained()->onDelete('cascade')->after('document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            // Hacemos el drop de la clave foránea y la columna
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
