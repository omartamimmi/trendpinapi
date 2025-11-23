<?php

namespace Modules\Tag\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tag\Services\FrontendTagService;

class TagsController extends Controller
{
    public function index(FrontendTagService $frontendTagService)
    {
        $frontendTagService
            ->getAllTagsApi()
            ->collectOutputs($tags);
        return response()->json($tags)->setStatusCode(200);
    }

    public function detail($id, FrontendTagService $frontendTagService)
    {
        $frontendTagService
            ->setInput('id', $id)
            ->getTag()
            ->collectOutputs($tag);
        return response()->json($tag)->setStatusCode(200);
    }

    public function getTagBasedCategory(Request $request, FrontendTagService $frontendTagService)
    {
        $frontendTagService
            ->setInput('category_id', $request->input('category_ids'))
            ->getTagBasedCategory()
            ->collectOutputs($tag);
        return response()->json($tag)->setStatusCode(200);
    }
}
