<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use Modules\User\Database\Seeders\PermissionSeeder;
use Modules\User\Database\Seeders\RolePermissionsSeeder;
use Modules\User\Database\Seeders\RolesSeeder;
use Modules\User\Database\Seeders\UsersTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
$this->call(\Modules\User\Database\Seeders\PermissionSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(RolePermissionsSeeder::class);
        $this->call(UsersTableSeeder::class);
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
