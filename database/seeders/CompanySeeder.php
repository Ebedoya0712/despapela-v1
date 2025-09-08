<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company; // Importamos el modelo Company

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create(['name' => 'Empresa Ficticia S.A.']);
        Company::create(['name' => 'Soluciones Digitales LLC']);
        Company::create(['name' => 'Consultores Asociados']);
    }
}
