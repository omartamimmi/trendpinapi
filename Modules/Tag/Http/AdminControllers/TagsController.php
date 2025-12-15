<?php

namespace Modules\Tag\Http\AdminControllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Contracts\Support\Renderable;
use Modules\Category\Services\AdminCategoryService;
use Modules\Tag\Http\Requests\AdminDeleteTagRequest;
use Modules\Tag\Http\Requests\StoreTagRequest;
use Modules\Tag\Services\AdminTagService;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(AdminTagService $adminTagService, AdminCategoryService $adminCategoryService)
    {
        $adminTagService->getAllTags()->collectOutputs($tags);
        $adminCategoryService->getAllCategories()->collectOutputs($categories);

        return view('tag::admin.list', $tags);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(AdminCategoryService $adminCategoryService)
    {
        $adminCategoryService->getAllCategories()->collectOutputs($categories);

        return view('tag::admin.create', $categories);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreTagRequest $request, AdminTagService $adminTagService)
    {
        $adminTagService
        ->setInputs($request->validated())
        ->createTag()
        ->syncCategory()
        ->collectOutputs($tag);
        Alert::success('Congrats', 'Create Tag Successfully');

        return redirect()->route('admin.tag.tag-list');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('tag::admin.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id, AdminTagService $adminTagService, AdminCategoryService $adminCategoryService)
    {
        $adminCategoryService->getAllCategories()->collectOutputs($categories);

        $adminTagService
        ->setInput('id', $id)
        ->getTag()
        ->collectOutputs($tag);
        return view('tag::admin.edit', compact('tag', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StoreTagRequest $request, $id, AdminTagService $adminTagService)
    {
        $adminTagService
        ->setInputs($request->validated())
        ->setInput('id', $id)
        ->updateTag()
        ->syncCategory()
        ->collectOutputs($tag);
        Alert::success('Congrats', 'Update Tag Successfully');

        return redirect()->route('admin.tag.tag-list');

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(AdminDeleteTagRequest $request, AdminTagService $adminTagService , $id)
    {
        $adminTagService
        ->setInput('ids',  ['ids'=>[$request->id]])
        ->bulkDeleteTags();
        return redirect()->back()->with('message', 'tag deleted successfully');
    }
}
