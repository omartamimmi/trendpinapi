<?php

namespace Modules\User\database\seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\User\database\seeders\PermissionSeeder;
use Modules\User\database\seeders\RolePermissionsSeeder;
use Modules\User\database\seeders\RolesSeeder;
use Modules\User\database\seeders\UsersTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(RolePermissionsSeeder::class);
        $this->call(UsersTableSeeder::class);
    }
}
