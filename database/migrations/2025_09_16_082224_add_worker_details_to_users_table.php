<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // En el método up() del nuevo archivo de migración
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('dni')->nullable()->after('email');
        $table->string('phone')->nullable()->after('dni');
        $table->string('bank_account')->nullable()->after('phone');
        $table->text('address')->nullable()->after('bank_account');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
