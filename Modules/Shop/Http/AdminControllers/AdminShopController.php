<?php

namespace Modules\Shop\Http\AdminControllers;

use Illuminate\Http\Request;
use Modules\Shop\Http\Requests\AdminDeleteShopRequest;
use Modules\Shop\Models\Shop;
use Illuminate\Routing\Controller;
use Modules\Shop\Services\ShopService;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Shop\Services\AdminShopService;
use Illuminate\Contracts\Support\Renderable;
use Modules\Shop\Http\Requests\StoreShopRequest;
use Modules\Category\Services\AdminCategoryService;

class AdminShopController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(AdminShopService $shopService)
    {
        $shopService
            ->getAllShops()
            ->collectOutputs($shops);
        return view('shop::admin.list', $shops);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(AdminCategoryService $categoryService)
    {
        $categoryService->getAllCategories()->collectOutputs($categories);
        return view('shop::admin.create', $categories);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreShopRequest $request, AdminShopService $adminShopService)
    {
        $adminShopService
            ->setInputs($request->validated())
            ->createShop()
            ->createExactLocation()
            ->storeMetaData()
            ->syncCategory()
            ->syncTag()
            ->collectOutputs($shop);
        Alert::success('Congrats', 'Create Shop Successfully ');
        return redirect()->route('admin.shop.shop-list');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('shop::admin.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id, AdminShopService $adminShopService, AdminCategoryService $categoryService)
    {
        $adminShopService
            ->setInput('id', $id)
            ->getShop()
            ->collectOutputs($shop);
        $categoryService
            ->getAllCategories()
            ->collectOutputs($categories);
        $data = [
            'shop' => $shop['shop'],
            'categories' => $categories['categories']
        ];
        return view('shop::admin.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StoreShopRequest $request, $id, AdminShopService $adminShopService, AdminCategoryService $categoryService)
    {
        $adminShopService
            ->setInputs($request->validated())
            ->setInput('id', $id)
            ->updateShop()
            ->storeMetaData()
            ->syncCategory()
            ->syncTag()
            ->updateExactLocation()
            ->collectOutputs($shop);

        $categoryService
            ->getAllCategories()
            ->collectOutputs($categories);

        $data = [
            'shop' => $shop['shop'],
            'categories' => $categories['categories']
        ];
        Alert::success('Congrats', 'Update Shop Successfully ');

        return redirect()->route('admin.shop.shop-list', $data);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(AdminDeleteShopRequest $request, AdminShopService $shopService)
    {
        $shopService
            ->setInput('ids', ['ids'=>[$request->id]])
            ->bulkDeleteShops();
        return redirect()->back()->with('message', 'Users deleted successfully');
    }

    /**
     * Get shop id.
     * @param int $id
     * @return Renderable
     */
    public function getShop($id, AdminShopService $shopService)
    {
        $shopService
            ->setInput('id', $id)
            ->getShop();
        return redirect()->back()->with('message', 'Users deleted successfully');
    }
}
