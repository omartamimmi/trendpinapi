<?php

namespace Modules\Business\Services;

use App\Abstractions\Service;
use Illuminate\Support\Facades\Auth;
use Modules\Business\Repositories\BrandRepository;
use Modules\Business\Repositories\BrandMetaRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class BrandService extends Service
{
    protected $brandRepository;
    protected $brandMetaRepository;

    public function __construct(
        BrandRepository $brandRepository,
        BrandMetaRepository $brandMetaRepository
    ) {
        $this->brandRepository = $brandRepository;
        $this->brandMetaRepository = $brandMetaRepository;
    }

    public function prepareOperatingHours(): array
    {
        $enableOpenHours = $this->getInput('enable_open_hours');
        $openHours = $this->getInput('open_hours');
        return [
            'enable_open_hours' => $enableOpenHours,
            'open_hours' => $openHours
        ];
    }

    public function prepareDiscount(): array
    {
        $enableDiscount = $this->getInput('enable_discount');
        $discountType = $this->getInput('discount_type');
        $discount = "";
        switch ($discountType) {
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
        return [
            'enable_discount' => $enableDiscount,
            'discount_type' => $discountType,
            'discount' => $discount
        ];
    }

    public function storeMetaData(): static
    {
        $this->collectOutput('brand', $brand);
        $opHours = $this->prepareOperatingHours();
        $discount = $this->prepareDiscount();
        $data = array_merge($opHours, $discount);
        $data['brand_id'] = $brand->id;
        $check = $this->brandMetaRepository->getMetaBrand($brand->id);

        if ($check->isEmpty()) {
            $this->brandMetaRepository->create($data);
        } else {
            $this->brandMetaRepository->update($data, $brand->id);
        }
        $this->setOutput('meta', $brand->meta);
        return $this;
    }

    public function getAllBrands(): static
    {
        $userId = Auth::id();
        $brands = $this->brandRepository->getAllBrandsByAuthor($userId);
        $this->setOutput('brands', $brands);
        return $this;
    }

    public function createBrand(): static
    {
        $brand = $this->brandRepository->create($this->getInputs());
        $this->setOutput('brand', $brand);
        return $this;
    }

    public function updateBrand(): static
    {
        $brand = $this->brandRepository->getBrandById($this->getInput('id'));
        $this->checkAuthority($brand);
        $this->brandRepository->update($this->getInput('id'), $this->getInputs());
        $this->setOutput('brand', $brand);
        return $this;
    }

    public function getBrand(): static
    {
        $brand = $this->brandRepository->getBrandById($this->getInput('id'));
        $cats = [];
        if (!empty($brand)) {
            foreach ($brand->categories()->get() as $cat) {
                array_push($cats, ['id' => $cat->id, 'name' => $cat->name]);
            }
        }
        $gallery = $brand->getGallery();
        $this->setOutput('brand', $brand);
        $this->setOutput('categories', $cats);
        $this->setOutput('galleryUrls', $gallery);
        $this->setOutput('meta', $brand->meta);
        return $this;
    }

    public function syncCategory(): static
    {
        $this->collectOutput('brand', $brand);
        $brand->categories()->sync($this->getInput('category'));
        return $this;
    }

    public function syncTag(): static
    {
        $this->collectOutput('brand', $brand);
        $brand->tags()->sync($this->getInput('tags'));
        return $this;
    }

    public function checkAuthority($brand)
    {
        if ($brand->create_user != Auth::user()->id) {
            throw new Exception('unauthorized', 403);
        }
    }

    public function deleteBrand(): static
    {
        $brand = $this->brandRepository->getBrandById($this->getInput('brand_id'));
        $this->checkAuthority($brand);
        $this->brandRepository->deleteBrand($this->getInput('brand_id'));
        return $this;
    }
}
