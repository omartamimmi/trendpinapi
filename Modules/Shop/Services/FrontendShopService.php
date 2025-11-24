<?php

namespace Modules\Shop\Services;

use App\Abstractions\Service;
use Illuminate\Support\Facades\Auth;
use Modules\Shop\Repositories\ShopRepository;

class FrontendShopService extends Service
{
    protected $shopRepository;

    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    public function getAllShops():static
    {
        $shops = $this->shopRepository->getAllShops();
        $this->setOutput('shops', $shops);
        return $this;
    }


    public function shopFilters() :static
    {
        $catIds = $this->getInput('category_ids');
        $query = $this->getInput('query');
        $bestOffer = $this->getInput('best_offer');
        $openStatus = $this->getInput('open_status');
        $coordinate = ['lat'=>$this->getInput('lat'), 'lng'=>$this->getInput('lng')];
        $sortCoordinate = ['lat'=>$this->getInput('sort_lat'), 'lng'=>$this->getInput('sort_lng')];
        $explore = $this->getInput('explore');
        $tagIds = $this->getInput('tag_ids');

        $shops = $this->shopRepository->shopFilters($catIds, $openStatus, $coordinate, $query, $bestOffer,$sortCoordinate, $explore, $tagIds);
        $this->setOutput('shops', $shops);
        return $this;
    }

    public function getShop():static
    {
        $shop = $this->shopRepository->getShopById($this->getInput('id'));
        $this->setOutput('shop', $shop);
        return $this;
    }

    public function shopFiltersCoordinates() :static
    {
        $latStart = $this->getInput('lat_start');
        $lngStart = $this->getInput('lng_start');
        $latEnd = $this->getInput('lat_end');
        $lngEnd = $this->getInput('lng_end');
        $catIds = $this->getInput('category_ids');
        $shops = $this->shopRepository->shopFiltersBasedLocation($latStart, $lngStart, $latEnd, $lngEnd, $catIds);
        $this->setOutput('shops', $shops);
        return $this;
    }


}
