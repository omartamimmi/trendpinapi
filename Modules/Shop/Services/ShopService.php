<?php

namespace Modules\Shop\Services;

use App\Abstractions\Service;
use Illuminate\Support\Facades\Auth;
use Modules\Shop\Repositories\ShopRepository;
use Exception;
use Modules\Category\Services\CategoryService;
use Illuminate\Support\Facades\DB;
use Modules\Location\Models\Location;
use Modules\Location\Services\LocationService;
use Modules\Notification\Jobs\NotifyMe;
// use Modules\Notification\Jobs\NotifyMeSelectedShop;
use Modules\Shop\Repositories\ShopMetaRepository;

class ShopService extends Service
{
    protected $shopRepository;
    protected $locationService;
    protected $shopMetaRepository;
    protected $categoryService;
    public function __construct(ShopRepository $shopRepository, LocationService $locationService, ShopMetaRepository $shopMetaRepository, CategoryService $categoryService)
    {
        $this->locationService = $locationService;
        $this->shopRepository = $shopRepository;
        $this->shopMetaRepository =$shopMetaRepository;
        $this->categoryService=$categoryService;
    }
    public function prepareOperatingHours():array
    {
        $this->collectOutput('shop', $shop);
        $enableOpenHours = $this->getInput('enable_open_hours');
        $openHours = $this->getInput('open_hours');
        $data = [
            'enable_open_hours'=> $enableOpenHours,
            'open_hours'=>$openHours
        ];

        return $data;
    }

    public function prepareDiscount():array
    {
        $this->collectOutput('shop', $shop);
        $enableOpenHours = $this->getInput('enable_discount');
        $discountType = $this->getInput('discount_type');
        $discount = "";
        switch($discountType){
            case 'percentage':
                $discount = $this->getInput('discount_percentage');
                break;
            case 'items':
                $discount = $this->getInput('discount_items');
                break;
            case 'other':
                $discount = $this->getInput('discount_description');
                break;
        }
        $data = [
            'enable_discount'=>$enableOpenHours,
            'discount_type'=>$discountType,
            'discount'=>$discount
        ];

        return $data;
    }

    public function storeMetaData(): static
    {
        $this->collectOutput('shop', $shop);

        $opHours = $this->prepareOperatingHours();
        $discount = $this->prepareDiscount();
        $data = array_merge($opHours,$discount);
        $data['shop_id'] = $shop->id;
        $check = $this->shopMetaRepository->getMetaShop($shop->id);

        if($check->isEmpty()){
            $this->shopMetaRepository->create($data);
        }else{
            $this->shopMetaRepository->update($data, $shop->id);
        }
        $shops = DB::table('user_has_interest_in_shops')->select('user_id')->where('shop_id', $shop->id)->pluck('user_id');
        $data = collect($shops)->map(function($x){ return (array) $x; })->toArray(); 

        $userTokens = [array_values($shops->toArray())];
        // dd($userTokens);

        // if($shops){
        //      NotifyMeSelectedShop::dispatch($userTokens);
        // }
        $this->setOutput('meta', $shop->meta);
        return $this;
    }

    public function getAllShops():static
    {
        $userId = Auth::id();
        $shops = $this->shopRepository->getAllShopsByAuthor($userId);
        $this->setOutput('shops', $shops);
        return $this;
    }

    public function getMainBranches():static
    {
        $userId = Auth::id();
        $shops = $this->shopRepository->getMainBranchesByAuthor($userId);
        $this->setOutput('shops', $shops);
        return $this;
    }

    public function shopsHasDiscount():static
    {
        $shops = $this->shopRepository->shopsHasDiscount();
        $this->setOutput('shops', $shops);
        return $this;
    }

    public function createShop():static
    {
        $shop = $this->shopRepository->create($this->getInputs());
        $this->setOutput('shop', $shop);
        return $this;
    }

    public function createExactLocation():static
    {
        $exactLocationData = $this->prepareDataBeforeCreation();
        $this->locationService->createExactLocation($exactLocationData);
        return $this;
    }

    public function prepareDataBeforeCreation():array
    {
        $this->collectOutput('shop', $shop);

        $exactLocationData['exact_address'] = $this->getInput('exact_address');
        $exactLocationData['lat'] = $this->getInput('lat');
        $exactLocationData['lng'] = $this->getInput('lng');
        $exactLocationData['address'] = $this->getInput('address');
        $exactLocationData['shop_id'] = $shop->id;

        return $exactLocationData;
    }

    public function updateExactLocation():static
    {
        $this->collectOutput('shop', $shop);
        $exactLocationData = $this->prepareDataBeforeCreation();
        $this->locationService->updateExactLocation($shop->id,$exactLocationData);
        return $this;
    }

    public function updateShop():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('id'));
        $this->checkAuthority($shop);
        $this->shopRepository->update($this->getInput('id'), $this->getInputs());
        $this->setOutput('shop', $shop);

        return $this;
    }

    public function getShop():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('id'));
        $this->setInput('main_branch_id',(string)$shop->main_branch_id);

        $branches = $this->shopRepository->getShopByIdWithBranches($this->getInput('id'), $this->getInput('main_branch_id'));

        $cats = [];
        if(!empty($shop)){
            foreach($shop->category_shops()->get() as $cat){
                array_push($cats, ['id'=>$cat->id, 'name'=>$cat->name]);
            }
        }
        $location = Location::where('shop_id', $shop->id)->first();

        $exLocation=[
            'address' => $location->address ?? '',
            'lat'=> $location->lat ?? '',
            'lng'=>$location->lng ?? ''
        ];
        $gallery = $shop->getGallery();

        $this->setOutput('shop', $shop);
        $this->setOutput('categories', $cats);
        $this->setOutput('location', $exLocation);
        $this->setOutput('galleryUrls', $gallery);
        $this->setOutput('meta', $shop->meta);
        $this->setOutput('branches', $branches);
        return $this;
    }

    public function bulkDeleteShops():static
    {
        $ids = $this->getInput('id');
        $this->shopRepository->deleteShop($ids);
        return $this;
    }

    public function syncCategory():static
    {
        $this->collectOutput('shop', $shop);
        $shop->category_shops()->sync($this->getInput('category'));
        return $this;
    }

    public function syncTag():static
    {
        $this->collectOutput('shop', $shop);
        $shop->tag_shops()->sync($this->getInput('tags'));
        return $this;
    }
    
    public function checkAuthority($shop)
    {   
        if($shop->create_user != Auth::user()->id){
            return throw new Exception('unauthorized', 403);
        }
    }

    public function updateShopStatus():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('shop_id'));
        $this->checkAuthority($shop);
        $this->shopRepository->updateShopStatus($this->getInput('shop_id'), $this->getInputs());

        return $this;
    }

    public function deleteShop():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('shop_id'));
        $this->checkAuthority($shop);
        $this->shopRepository->deleteShop($this->getInput('shop_id'));

        return $this;
    }

    public function getShopByAuthor():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('id'));
        $this->checkAuthority($shop);

        $cats = [];
        if(!empty($shop)){
            foreach($shop->category_shops()->get() as $cat){
                array_push($cats, ['id'=>$cat->id, 'name'=>$cat->name]);
            }
        }
        $location = Location::where('shop_id', $shop->id)->first();

        $exLocation=[
            'address' => $location->address,
            'lat'=> $location->lat,
            'lng'=>$location->lng
        ];
        $gallery = $shop->getGallery();

        $this->setOutput('shop', $shop);
        $this->setOutput('categories', $cats);
        $this->setOutput('location', $exLocation);
        $this->setOutput('galleryUrls', $gallery);
        $this->setOutput('meta', $shop->meta);
        return $this;
    }

    public function getAllCategories():static
    {
        $categories = $this->categoryService->getAllCategories();
        $categories->collectOutput('categories', $categories);
        $this->setOutput('categories',$categories);
        return $this;
    }

    public function getAllCities():static
    {
        $cities = $this->locationService->getAllCity();
        $cities->collectOutput('cities', $cities);
        $this->setOutput('cities',$cities);

        return $this;
    }

    public function createBranch()
    {
        $branchId = $this->getInput('branch_id');
        $this->setInput('is_main_branch',1);
        $this->checkBranch();
        if(!empty($branchId)){
            $this->setInput('main_branch_id', $branchId);
            $this->setInput('is_main_branch',0);
        }
        return $this;
    }

    public function checkBranch()
    {
        $branchId = $this->getInput('branch_id');
        $shop = $this->shopRepository->getShopById($branchId);
        $this->setInput('is_main_branch',1);
        if(!empty($branchId) && !($shop->is_main_branch == 1)){
            return throw new Exception('The branch should be a main business', 400);
        }
        return $this;
    }

    public function createMainBranch()
    {
        $this->setInput('is_main_branch',1);
        return $this;
    }

}
