<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscar el ID del rol 'Administrador'
        $adminRole = Role::where('name', 'Administrador')->first();

        // 2. Crear el usuario administrador
        User::create([
            'name' => 'Admin',
            'email' => 'admin@despapela.com',
            'password' => Hash::make('password'), // Cambia 'password' por una contraseña segura
            'role_id' => $adminRole->id,
        ]);
    }
}