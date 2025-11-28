<?php

namespace Modules\Category\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Category\Services\FrontendCategoryService;
use Throwable;

class CategoryController extends Controller
{

     /**
     * Retrieve a Categories for the authenticated user.
     *
     * This method accepts request parameters (),
     * FrontendCategoryService fetches the all categories
     * if it success. Returns all categories as a JSON response
     * or an error response if an exception occurs.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable If any error or exception occurs during categories retrieval.
     */
    public function index(FrontendCategoryService $frontendCategoryService)
    {
        try{
            $frontendCategoryService
                ->getAllCategories()
                ->collectOutputs($categories);
            return response()->json($categories)->setStatusCode(200);
        }catch(Throwable $e){
            return $this->errorResponse($e);
        }
    }

    /**
     * Retrieve a category for the authenticated user based on id.
     *
     * This method accepts request parameters (`id`),
     * passes them to the FrontendCategoryService, and fetches the corresponding category
     * if it exist. Returns the category as a JSON response
     * or an error response if an exception occurs.
     *
     * @param  $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable If any error or exception occurs during category retrieval.
     */
    public function detail($id, FrontendCategoryService $frontendCategoryService)
    {
        try{
            $frontendCategoryService
                ->setInput('id', $id)
                ->getCategory()
                ->collectOutputs($category);
            return response()->json($category)->setStatusCode(200);
        }catch(Throwable $e){
            return $this->errorResponse($e);
        }
    }

    /**
     * Handle error response
     * 
     * @param  Throwable $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function errorResponse($e)
    {
        $code = ($e->getCode() != 0) ? $e->getCode() : 500;
        return response()->json([
            'error' => [
                'message' => $e->getMessage(),
                'code' => $code,
            ]
        ])->setStatusCode($code);
    }
}
