<?php

namespace Modules\Shop\Services;

use App\Abstractions\Service;
use Exception;
use Modules\Location\Services\LocationService;
use Modules\Shop\Repositories\ShopRepository;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Shop\Repositories\ShopMetaRepository;

use function PHPUnit\Framework\isEmpty;

class AdminShopService extends Service
{
    protected $shopRepository;
    protected $locationService;
    protected $shopMetaRepository;

    public function __construct(ShopRepository $shopRepository, LocationService $locationService, ShopMetaRepository $shopMetaRepository)
    {
        $this->locationService = $locationService;
        $this->shopRepository = $shopRepository;
        $this->shopMetaRepository =$shopMetaRepository;
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
                $discount = '"'.$this->getInput('discount_description').'"';
                break;
        }
        $data = [
            'enable_discount'=>$enableOpenHours,
            'discount_type'=>$discountType,
            'discount'=>$discount
        ];

        return $data;
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

    public function storeMetaData(): static
    {
        $this->collectOutput('shop', $shop);

        $opHours = $this->prepareOperatingHours();
        $discount = $this->prepareDiscount();
        
        //TODO check if it will affect any other items
        $opHours['open_hours'] = array_filter($opHours['open_hours']);

        $data = array_merge($opHours,$discount);
        $data['shop_id'] = $shop->id;
        $checkMeta = $this->shopMetaRepository->getMetaShop($shop->id);

        if(!$checkMeta->isEmpty()){
            $this->shopMetaRepository->update($data, $shop->id);
        }else{
            $this->shopMetaRepository->create($data);

        }

        return $this;
    }

    public function createExactLocation():static
    {
        $exactLocationData = $this->prepareDataBeforeCreation();
        $this->locationService->createExactLocation($exactLocationData);
        return $this;
    }

    public function getAllShops():static
    {
        $shops = $this->shopRepository->getAllShops();
        $this->setOutput('shops', $shops);
        return $this;
    }

    public function createShop():static
    {
        $shop = $this->shopRepository->create($this->getInputs());
        $this->setOutput('shop', $shop);
        return $this;
    }


    public function updateShop():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('id'));
        $this->shopRepository->update($this->getInput('id'), $this->getInputs());
        $this->setOutput('shop', $shop);
        return $this;
    }

    public function getShop():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('id'));
        // dd($shop);
        $gallery = $shop->getGallery();
        $this->setOutput('shop', $shop);
        $this->setOutput('galleryUrls', $gallery);
        return $this;
    }

    public function bulkDeleteShops():static
    {
        $ids = $this->getInput('ids');
        $this->shopRepository->delete($ids);
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
    

    public function getShopBySourceId():static
    {
        $shop = $this->shopRepository->getShopBySourceId($this->getInput('id'));
        $this->setOutput('shop', $shop);
        return $this;
    }

}

