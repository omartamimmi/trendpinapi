<?php

namespace Modules\Location\Http\Controllers;

use App\Models\City;
use App\Models\TestData;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $pre_selected = $request->query('pre_selected');
        $selected = $request->query('selected');
        if ( $selected) {
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
                    'id' => $item->id,
                    'text' => $item->name
                ]);
            }
        }

        $q = $request->query('q');

        $query = City::select('cities.id', 'cities.name as name', 'states.name as state', 'countries.name as country')
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->join('countries', 'states.country_id', '=', 'countries.id');
            // ->where("cities.status", "publish")
            // ->where('states.status', 'publish')
            // ->where('countries.status', 'publish');

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
        return response()->json($result);

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('location::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('location::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('location::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function save(Request $request)
    {
        try{
            $result = [
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),  
            'text' => $request->input('text'),  

        ];
        TestData::create($result);
        return response()->json($result);
        }catch(Throwable $e){
            $data = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
            return response()->setStatusCode($e->getCode());
        }

    }
}
