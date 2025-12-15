<?php

namespace Modules\Location\Services;

use App\Abstractions\Service;
use Illuminate\Support\Facades\Auth;
use Modules\Location\Repositories\LocationRepository;

class LocationService extends Service
{
    protected $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function createExactLocation(array $data):static
    {
        $this->locationRepository->create($data);
        return $this;
    }

    public function updateExactLocation(int $id, array $data):static
    {
        $shopLocation = $this->locationRepository->getShopById($id);
        if(count($shopLocation)){
            $this->locationRepository->update($id, $data);
        }else{
            $this->locationRepository->create($data);
        }
        return $this;
    }

    public function getAllCity():static
    {
        $cities = $this->locationRepository->getAllCity();
        $this->setOutput('cities', $cities);
        return $this;
    }



}
