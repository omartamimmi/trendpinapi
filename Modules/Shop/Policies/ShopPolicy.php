<?php

namespace Modules\Shop\Policies;

use App\Models\Shop;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * by zaid
 * Summary of ShopPolicy
 */
class ShopPolicy
{
    use HandlesAuthorization;
    public function create(Shop $loggedUser)
    {
        return $loggedUser->hasPermissionTo('create')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function update(Shop $loggedUser)
    {
        return $loggedUser->checkPermissionTo('update')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function edit(Shop $loggedUser)
    {
        return $loggedUser->checkPermissionTo('edit')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function delete(Shop $loggedUser)
    {
        return $loggedUser->checkPermissionTo('delete')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }
    public function admin_dashboard(Shop $loggedUser)
    {

        return $loggedUser->checkPermissionTo('admin_dashboard')
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    // public function host_dashboard(Shop $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_dashboard')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_create(Shop $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_create')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_delete(Shop $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_delete')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_edit(Shop $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_edit')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }

    // public function host_update(Shop $loggedUser)
    // {
    //     return $loggedUser->checkPermissionTo('host_update')
    //         ? Response::allow()
    //         : Response::denyWithStatus(403);
    // }



}
