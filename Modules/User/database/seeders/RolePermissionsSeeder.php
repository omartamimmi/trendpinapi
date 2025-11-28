<?php
namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $host = Role::find(2);
        $host->givePermissionTo('host_dashboard');
        $host->givePermissionTo('host_create');
        $host->givePermissionTo('host_update');
        $host->givePermissionTo('host_edit');
        $host->givePermissionTo('host_delete');
        $host->givePermissionTo('host_view');

        $admin = Role::find(1);
        $admin->givePermissionTo('admin_dashboard');
        $admin->givePermissionTo('host_dashboard');
        $admin->givePermissionTo('view');
        $admin->givePermissionTo('create');
        $admin->givePermissionTo('update');
        $admin->givePermissionTo('delete');

        $customer = Role::find(3);
        $customer->givePermissionTo('customer_dashboard');
        // $customer->givePermissionTo('host_dashboard');
        $customer->givePermissionTo('customer_view');
        $customer->givePermissionTo('customer_create');
        $customer->givePermissionTo('customer_update');
        $customer->givePermissionTo('customer_edit');
        $customer->givePermissionTo('customer_delete');
    }
}







