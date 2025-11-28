<?php

namespace Modules\Business\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusinessController extends Controller
{

    public function __construct()
    {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        dd('index');
        return view('business::index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        dd($id);
        return view('business::show');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        dd($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        dd($id);
    }
}
