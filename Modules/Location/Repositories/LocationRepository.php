<?php
namespace Modules\Location\Repositories;

use App\Models\City;
use Modules\Location\Models\Location;

class LocationRepository
{

    public function getAllShopsByAuthor($id)
    {
        return Location::where('create_user', $id)->get();
    }

    public function getShopById($id)
    {
        return Location::where('shop_id', $id)->get();
    }

    public function create($data)
    {
        return Location::create($data);
    }

    public function update($id, $data): bool
    {

        $location = Location::where('shop_id',$id);
        
        return $location->update($data);
    }

    public function updateOrCreate($id, $data)
    {
            
        return Location::updateOrCreate($data);
    }
    
    public function getAllCity()
    {
        return City::paginate(10);
    }

}
