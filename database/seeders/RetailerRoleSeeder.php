<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RetailerRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'retailer', 'guard_name' => 'web']
        );
    }
}
