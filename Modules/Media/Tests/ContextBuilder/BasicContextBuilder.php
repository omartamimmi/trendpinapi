<?php

namespace Modules\Media\Tests\ContextBuilder;

use App\Models\Settings;
use App\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\User\Models\CuratorRequest;


class BasicContextBuilder
{
    protected array $flags = [];

    protected ?Role $lastCreatedRole = null;

    public function createAdminUser(User &$user = null): static
    {

        $user = User::factory()->create();

        $role = Role::query()->where('name', 'administrator')->first();
        $user->assignRole($role->id);

        return $this;
    }

    public function createCustomerUser(User &$user = null): static
    {

        $user = User::factory()->create();

        $role = Role::query()->where('name', 'customer')->first();
        $user->assignRole($role->id);

        return $this;
    }

    public function createCuratorUser(User &$user = null): static
    {

        $user = User::factory()->create();

        $role = Role::query()->where('name', 'curator')->first();
        $user->assignRole($role->id);

        return $this;
    }

    public function updateUser($user, $array): static
    {
        $user->update($array);

        return $this;
    }

    public function createCuratorRequest($user): static
    {
        $dataCurator['role_request'] = settingItem('vendor_role');
        $dataCurator['status'] = 'pending';

        $user->curatorRequest()->save(new CuratorRequest($dataCurator));

        return $this;
    }

    public function setupAdministratorRole(): static
    {
        if (!Role::query()->where('name', 'administrator')->first()) {
            $this->lastCreatedRole = Role::findOrCreate('administrator');
            $settings = new Settings();
            $settings->name = 'admin_role';
            $settings->group = 'user';
            $settings->val = $this->lastCreatedRole->id;
            $settings->save();
        }
        return $this;
    }

    public function setupCustomerRole(): static
    {
        if (!Role::query()->where('name', 'customer')->first()) {
            $this->lastCreatedRole = Role::findOrCreate('customer');
            $settings = new Settings();
            $settings->name = 'customer_role';
            $settings->group = 'user';
            $settings->val = $this->lastCreatedRole->id;
            $settings->save();
        }
        return $this;
    }

    public function setupCuratorRole(): static
    {
        if (!Role::query()->where('name', 'curator')->first()) {
            $this->lastCreatedRole = Role::findOrCreate('curator');
            $settings = new Settings();
            $settings->name = 'vendor_role';
            $settings->group = 'user';
            $settings->val = $this->lastCreatedRole->id;
            $settings->save();
        }
        return $this;
    }

    public function setupUserManagementPermissions($linkToLastCreatedRole = true): static
    {
        $this->setupPermissions(['user_view', 'user_create', 'user_update', 'user_delete'], $linkToLastCreatedRole);
        return $this;
    }

    public function setupCuratorPermissions($linkToLastCreatedRole = true): static
    {
        $this->setupPermissions(['media_upload','experience_view','experience_create','experience_update','experience_delete','dashboard_vendor_access','event_view','event_create','event_update','event_delete'], $linkToLastCreatedRole);
        return $this;
    }

    private function setupPermissions($permissions, $linkToLastCreatedRole = true)
    {
        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName);
            if ($linkToLastCreatedRole && $this->lastCreatedRole) {
                $this->lastCreatedRole->givePermissionTo($permissionName);
            }
        }
    }
}
