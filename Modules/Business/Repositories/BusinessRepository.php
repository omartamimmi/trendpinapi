<?php

namespace Modules\Business\Repositories;

use App\Models\NotificationBasedLocation;
use Modules\Business\app\Models\Business;
use Spatie\Permission\Models\Role;

class BusinessRepository
{

    public function getAllBusinesses()
    {
        return Business::paginate(10);
    }

    public function getBusinessById($id)
    {
        return Business::where('id',$id)->with('roles')->first();
    }

    public function create($data)
    {
        return Business::create($data);
    }

    public function findBusinessByEmail($email)
    {
        return Business::whereEmail($email)->first();
    }

    public function update($id, $data, $lang = 'en'): bool
    {
        $business = Business::find($id);
        $business->fill($data);
        return $business->save();
    }

    public function delete($ids)
    {
        return Business::whereIn('id', $ids['ids'])->delete();
    }
}
