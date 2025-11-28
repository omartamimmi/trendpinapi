<?php

namespace Modules\Shop\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\ShopMeta;

class ShopMetaRepository
{
    public function getMetaShop($id)
    {
        return ShopMeta::where('shop_id', $id)->get();
    }

    public function createShopMeta($data)
    {
        return ShopMeta::create($data);
    }

    public function create($data)
    {
        return ShopMeta::create($data);
    }

    public function update($data, $id)
    {
        return ShopMeta::where('shop_id', $id)->update($data);
    }

    public function delete($id)
    {
        return ShopMeta::where('shop_id', $id)->delete();
    }
}
