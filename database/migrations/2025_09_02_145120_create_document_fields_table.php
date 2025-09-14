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
        Schema::create('document_fields', function (Blueprint $table) {
            $table->id();

            // --- Columnas Esenciales ---
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // --- Columnas para el Editor "Todo en Uno" ---
            $table->string('name'); // Guarda el nombre del tag (ej. "DNI")
            $table->string('type'); // Guarda el tipo interno ('text' o 'signature')
            $table->longText('value')->nullable(); // Guarda el texto escrito o la firma Base64
            $table->json('coordinates'); // Guarda la posición y tamaño

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_fields');
    }
};