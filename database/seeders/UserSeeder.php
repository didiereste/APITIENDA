<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

/**
 * Class UserSeeder
 *
 * Seeder para inicializar usuarios en la base de datos con roles asignados.
 */
class UserSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de datos en la base de datos.
     */
    public function run(): void
    {
        // Crear y asignar roles al usuario normal
        $normalUser = new User();
        $normalUser->name = "Normal user";
        $normalUser->email = "user@gmail.com";
        $normalUser->password = bcrypt('123456');
        $normalUser->assignRole(2)->save();

        // Crear y asignar roles al usuario administrador
        $adminUser = new User();
        $adminUser->name = "Admin user";
        $adminUser->email = "admin@gmail.com";
        $adminUser->password = bcrypt('123456');
        $adminUser->assignRole(1)->save();
    }
}

