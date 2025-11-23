<?php

namespace Modules\Shop\Repositories;

use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\ShopMeta;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\GeocodeQueryBuilder;
class ShopRepository
{

    public function getAllShops()
    {
        return Shop::paginate(10);
    }

    public function shopFilters($cat_ids, $openStatus, $coordinate, $query, $bestOffer, $sortCoordinate, $explore = null, $tagIds = null)
    {
        $currentDay = Carbon::now()->dayOfWeekIso;
        $currentTime = Carbon::now()->setTimezone('Europe/Oslo')->format('g:i');
        $shops = Shop::query()->select("shops.*");
        if(!empty($query)){
            $shops= Shop::where('title', 'LIKE', "%$query%");
        }

        if(!empty($cat_ids)){
            $shops->whereHas('category_shops', function($query) use ($cat_ids) {
                $query->whereIn('id', $cat_ids);
            });
        }

        if(!empty($openStatus) && $openStatus == 1){
            $shops->whereHas('meta', function($query) use ($currentDay, $currentTime) {
                $query->where('open_hours->'.$currentDay.'->hours[0]->from', '<=', $currentTime);
                $query->where('open_hours->'.$currentDay.'->hours[0]->to', '>=', $currentTime);
            });
        }

        if(!empty($bestOffer) && $bestOffer == 1){
            $shops->whereHas('meta', function($query) {
                $query->whereNotNull('enable_discount');
                $query->whereNotNull('discount_type');
                $query->whereNotNull('discount');
            });
        }

        if(!empty($coordinate) && isset($coordinate['lat']) && isset($coordinate['lng']) && !empty($coordinate['lng']) && !empty($coordinate['lat'])){

            $lat = $coordinate['lat'];
            $lng = $coordinate['lng'];

            $distance = 3;

            $haversine = "(
                6371 * acos(
                    cos(radians(" .$lat. "))
                    * cos(radians(`lat`))
                    * cos(radians(`lng`) - radians(" .$lng. "))
                    + sin(radians(" .$lat. ")) * sin(radians(`lat`))
                )
            )";
            $shops->whereHas('location',function($q) use ($haversine, $distance) {
                $q->selectRaw("$haversine AS distance")->having("distance", "<=", $distance);
            });
        }

        if(!empty($sortCoordinate) && isset($sortCoordinate['lat']) && isset($sortCoordinate['lng']) && !empty($sortCoordinate['lng']) && !empty($sortCoordinate['lat'])){

            $lat = $sortCoordinate['lat'];
            $lng = $sortCoordinate['lng'];

            $shops->orderByDistanceFrom($lat, $lng, 'asc');
        
        }

        if(!empty($explore)){
            $shops->where('is_main_branch', 1);
        }

        if(!empty($tagIds)){
            $shops->whereHas('tag_shops', function($query) use ($tagIds) {
                $query->whereIn('id', $tagIds);
            });
        }
        
        return $shops->with(['location', 'meta'])->where('status', 'publish')->orderBy('id','DESC')->paginate(12);
    }

    public function getAllShopsByAuthor($id)
    {
        return Shop::where('create_user', $id)->with('tag_shops')->get();
    }

    public function getMainBranchesByAuthor($id)
    {
        return Shop::where('create_user', $id)->where('is_main_branch', 1)->with('tag_shops')->select('id','title', 'featured_image')->get();
    }

    public function getShopById($id)
    {
        return Shop::where('id',$id)->with(['location', 'tag_shops'])->first();
    }

    public function getShopByIdWithBranches($id, $mainBranchId)
    {
        // dd( $id, $mainBranchId);
        return Shop::where('main_branch_id',$id)
        ->orWhere('id',$mainBranchId)
        ->orWhere('main_branch_id',$mainBranchId)
        ->orWhere('id',$id)
        ->select('title', 'featured_image', 'id','main_branch_id')->with(['meta'])->get();
    }

    public function create($data)
    {
        return Shop::create($data);
    }

    public function findShopByEmail($email)
    {
        return Shop::whereEmail($email)->first();
    }

    public function changePassword($Shop)
    {
        $Shop->save();
        return $Shop->getAttributes();
    }

    public function update($id, $data): bool
    {
        $shop = Shop::find($id);
        $shop->fill($data);
        return $shop->save($data);
    }

    public function delete($ids)
    {
        return Shop::whereIn('id', $ids['ids'])->delete();
    }

    public function shopsHasDiscount()
    {
        $shops = Shop::where('featured',1);
        return $shops->limit(10)->get();
    }

    public function updateShopStatus($id, $data):bool
    {
        $shop = Shop::where('id',$id)->update(['status'=>$data['status']]);
        return $shop;
    }

    public function deleteShop($id)
    {
        $shop = Shop::where('id',$id)->delete();
        return $shop;
    }


    public function shopFiltersBasedLocation($latStart, $lngStart, $latEnd, $lngEnd, $cat_ids)
    {
        $distance = "6371 * acos(cos(radiance($latStart)) * cos(radians($latEnd)) * cos(radians($lngEnd) - radians($lngStart)) + sin(radians($latStart)) * sin(radians($latEnd)))";
        $sql = 'SELECT * FROM shops WHERE distance < ?';
        // dd($distance);
        $dis = $this->calculateDistance($latStart, $lngStart, $latEnd, $lngEnd);
        $shops = Shop::select('*')
        ->whereBetween('lat',[($latStart - ($dis*0.014)),($latEnd + ($dis*0.014))])
        ->whereBetween('lng',[($lngStart - ($dis*0.014)),($lngEnd + ($dis*0.014))])
        ->whereHas('meta', function($query) {
            $query->whereNotNull('enable_discount');
            $query->whereNotNull('discount_type');
            $query->whereNotNull('discount');
        });

        if(!empty($cat_ids)){
            $shops->whereHas('category_shops', function($query) use ($cat_ids) {
                $query->whereIn('id', $cat_ids);
            });
        }
        $shops->where('status', 'publish');
        
        return $shops->orderByDistanceFrom($latStart, $lngStart, 'asc')->get();
    }

    private function calculateDistance($latStart, $lngStart, $latEnd, $lngEnd)
    {
        $lat1 = $latStart;
        $lon1 = $lngStart;
        $lat2 = $latEnd;
        $lon2 = $lngEnd;

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;

        return round($kilometers,2);
    }

    public function getShopBySourceId($id)
    {
        return Shop::where('source_id',$id)->with('tag_shops')->first();
    }
}
