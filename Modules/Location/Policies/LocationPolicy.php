<?php

namespace Modules\Location\Policies;
use Illuminate\Auth\Access\Response;

use App\Models\Location;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

   /**
     * by zaid
     * Create a new policy instance.
     *
     * @return void
     */
    public function create(Location $loggedUser)
    {
        return $loggedUser->hasPermissionTo('create')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function update(Location $loggedUser)
    {
        return $loggedUser->checkPermissionTo('update')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function edit(Location $loggedUser)
    {
        return $loggedUser->checkPermissionTo('edit')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function delete(Location $loggedUser)
    {
        return $loggedUser->checkPermissionTo('delete')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }
    public function admin_dashboard(Location $loggedUser)
    {

        return $loggedUser->checkPermissionTo('admin_dashboard')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    // public function host_dashboard(Location $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_dashboard')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_create(Location $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_create')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_delete(Location $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_delete')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_edit(Location $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_edit')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_update(Location $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_update')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }
}
