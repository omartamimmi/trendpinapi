<?php

namespace Modules\Category\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Category\Http\Requests\AdminDeleteCategoryRequest;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Contracts\Support\Renderable;
use Modules\Category\Services\AdminCategoryService;
use Modules\Category\Http\Requests\StoreCategoryRequest;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(AdminCategoryService $categoryService)
    {
        $categoryService->getAllCategories()->collectOutputs($categories);
        return view('category::admin.list', $categories);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('category::admin.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreCategoryRequest $request, AdminCategoryService $adminCategoryService)
    {
        $adminCategoryService
        ->setInputs($request->validated())
        ->createCategory()
        ->collectOutputs($category);
        Alert::success('Congrats', 'Create Category Successfully');

        return redirect()->route('admin.category.category-list');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('category::admin.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id, AdminCategoryService $adminCategoryService)
    {
        $adminCategoryService
        ->setInput('id', $id)
        ->getCategory()
        ->collectOutputs($category);
        return view('category::admin.edit', $category);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StoreCategoryRequest $request, $id, AdminCategoryService $adminCategoryService)
    {
        $adminCategoryService
        ->setInputs($request->validated())
        ->setInput('id', $id)
        ->updateCategory()
        ->collectOutputs($category);
        Alert::success('Congrats', 'Update Category Successfully');

        return redirect()->route('admin.category.category-list');

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(AdminDeleteCategoryRequest $request, AdminCategoryService $adminCategoryService , $id)
    {
        $adminCategoryService
        ->setInput('ids',  ['ids'=>[$request->id]])
        ->bulkDeleteCategories();
        return redirect()->back()->with('message', 'category deleted successfully');
    }
}
