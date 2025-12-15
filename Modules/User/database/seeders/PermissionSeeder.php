<?php
namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        app()['cache']->forget('spatie.permission.cache');

        Permission::updateOrCreate(['name' => 'host_dashboard']);
        Permission::updateOrCreate(['name' => 'view']);
        Permission::updateOrCreate(['name' => 'create']);
        Permission::updateOrCreate(['name' => 'update']);
        Permission::updateOrCreate(['name' => 'delete']);
        Permission::updateOrCreate(['name' => 'host_view']);
        Permission::updateOrCreate(['name' => 'host_create']);
        Permission::updateOrCreate(['name' => 'host_update']);
        Permission::updateOrCreate(['name' => 'host_delete']);
        Permission::updateOrCreate(['name' => 'host_edit']);
        Permission::updateOrCreate(['name' => 'customer_dashboard']);
        Permission::updateOrCreate(['name' => 'customer_view']);
        Permission::updateOrCreate(['name' => 'customer_create']);
        Permission::updateOrCreate(['name' => 'customer_update']);
        Permission::updateOrCreate(['name' => 'customer_edit']);
        Permission::updateOrCreate(['name' => 'customer_delete']);
        Permission::updateOrCreate(['name' => 'admin_dashboard']);

        
        // Permission::findOrCreate('host_dashboard');
        // Permission::findOrCreate('view');
        // Permission::findOrCreate('create');
        // Permission::findOrCreate('update');
        // Permission::findOrCreate('delete');
        // Permission::findOrCreate('host_view');
        // Permission::findOrCreate('host_create');
        // Permission::findOrCreate('host_update');
        // Permission::findOrCreate('host_delete');
        // Permission::findOrCreate('host_edit');
        // Permission::findOrCreate('customer_dashboard');
        // Permission::findOrCreate('host_dashboard');
        // Permission::findOrCreate('customer_view');
        // Permission::findOrCreate('customer_create');
        // Permission::findOrCreate('customer_update');
        // Permission::findOrCreate('customer_edit');
        // Permission::findOrCreate('customer_delete');

    }
}
