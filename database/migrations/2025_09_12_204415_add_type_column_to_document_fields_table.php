<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            // Añadimos la columna 'type' para saber si es 'text' o 'signature'
            $table->string('type')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};