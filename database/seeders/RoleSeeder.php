<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Class RoleSeeder
 *
 * Seeder para inicializar roles y permisos en la base de datos.
 */
class RoleSeeder extends Seeder
{
    /**
     * Ejecuta la inserción de datos en la base de datos.
     */
    public function run(): void
    {
        // Crear el rol de administrador (admin) y el rol de usuario (user)
        $adminRole = Role::create(["id" => 1, "name" => "admin"]);
        Role::create(["id" => 2, "name" => "user"]);

        // Asignar permisos al rol de administrador
        // El administrador puede manipular categorías, marcas y roles
        Permission::create(["name" => "categorias"])->assignRole($adminRole);
        Permission::create(["name" => "marcas"])->assignRole($adminRole);
        Permission::create(["name" => "roles"])->assignRole($adminRole);
    }
}

