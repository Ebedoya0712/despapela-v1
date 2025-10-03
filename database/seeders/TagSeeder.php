<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // <-- 1. Importa el Facade de Schema

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 2. Desactivamos la revisión de llaves foráneas
        Schema::disableForeignKeyConstraints();

        // Ahora el truncate funcionará
        DB::table('tags')->truncate();

        $tags = [
            ['name' => 'NOMBRE Y APELLIDOS'],
            ['name' => 'DNI'],
            ['name' => 'EMAIL'],
            ['name' => 'TELÉFONO'],
            ['name' => 'NÚMERO DE CUENTA BANCARIA'],
            ['name' => 'DIRECCIÓN'],
            ['name' => 'CAMPO DE FIRMA DIBUJADO'],
        ];

        Tag::insert($tags);

        // 3. ¡Muy importante! Volvemos a activar la revisión
        Schema::enableForeignKeyConstraints();
    }
}