<?php

namespace Modules\Shop\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Shop\Services\ShopService;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Contracts\Support\Renderable;
use Modules\Category\Services\CategoryService;
use Modules\Shop\Http\Requests\StoreShopRequest;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ShopService $shopService)
    {
        $shopService
        ->getAllShops()
        ->collectOutputs($shops);
        return view('shop::host.list',$shops);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(ShopService $shopService)
    {
        $shopService
        ->getAllCategories()
        ->getAllCities()
        ->collectOutput('categories',$categories)
        ->collectOutput('cities',$cities);
        $data = [
            'categories'=>$categories,
            'locations'=>$cities
        ];

        return view('shop::host.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreShopRequest $request, ShopService $shopService)
    {
        $shopService
        ->setInputs($request->validated())
        ->createShop()
        ->createExactLocation()
        ->storeMetaData()
        ->syncCategory()
        ->collectOutputs($shop);

        Alert::success('Congrats', 'Create Shop Successfully ');
        return redirect()->route('user.shop.shop-list');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('shop::host.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id,ShopService $shopService, CategoryService $categoryService)
    {
        $shopService
        ->setInput('id', $id)
        ->getShop()
        ->collectOutputs($shop);

        $categoryService
        ->getAllCategories()
        ->collectOutputs($categories);
        $data = [
            'shop'=>$shop['shop'],
            'categories'=>$categories['categories']
        ];

        return view('shop::host.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StoreShopRequest $request, $id, ShopService $shopService,CategoryService $categoryService)
    {

        $shopService
        ->setInputs($request->validated())
        ->setInput('id', $id)
        ->updateShop()
        ->storeMetaData()
        ->syncCategory()
        ->updateExactLocation()
        ->collectOutputs($shop);

        $categoryService
        ->getAllCategories()
        ->collectOutputs($categories);

        $data = [
            'shop'=>$shop['shop'],
            'categories'=>$categories['categories']
        ];

        Alert::success('Congrats', 'Update Shop Successfully ');
        return redirect()->route('user.shop.shop-list');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($ids, ShopService $shopService)
    {
        $shopService
         ->setInput('id',$ids)
         ->bulkDeleteShops();

        Alert::success('Congrats', 'Delete Shop Successfully');
        return redirect()->route('user.shop.shop-list');


    }

    public function getForSelect2(Request $request)
    {
        $pre_selected = $request->query('pre_selected');
        $selected = $request->query('selected');

        if ($pre_selected && $selected) {
            if (is_array($selected)) {
                $items = City::select('id', 'name as text')->whereIn('id', $selected)->take(50)->get();
                return response()->json([
                    'items' => $items
                ]);
            } else {
                $item = City::find($selected);
            }
            if (empty($item)) {
                return response()->json([
                    'text' => ''
                ]);
            } else {
                return response()->json([
                    'text' => $item->name
                ]);
            }
        }

        $q = $request->query('q');

        $query = City::select('cities.id', 'cities.name as name', 'states.name as state', 'countries.name as country')
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->join('countries', 'states.country_id', '=', 'countries.id')
            ->where("cities.status", "publish")
            ->where('states.status', 'publish')
            ->where('countries.status', 'publish');

        if ($q) {
            $query->whereRaw("CONCAT_WS (' ', cities.name, states.name, countries.name) LIKE ?", ["%{$q}%"]);
        }
        $res = $query->orderBy('cities.id', 'desc')->limit(20)->get();
        $result = [];
        foreach($res as $value) {
            $result[] = [
                'id' => $value->id,
                'text' => ucfirst($value->name) . ' - ' . ucfirst($value->state)  . ' - ' . ucfirst($value->country)
            ];
        }

        return response()->json([
            'results' => $result
        ]);
    }
}
