<?php

namespace Modules\Shop\Http\Controllers\Api;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Category\Services\AdminCategoryService;
use Modules\Shop\Http\Requests\FiltersShopRequest;
use Modules\Shop\Http\Requests\StoreShopRequest;
use Modules\Shop\Services\AdminShopService;
use Modules\Shop\Services\FrontendShopService;
use Modules\Shop\Services\ShopService;
use Exception;
use Modules\Shop\Http\Requests\UpdateShopStatusRequest;
use Modules\Shop\Transformers\ShopCollection;
use Modules\User\Transformers\ErrorResource;
use Illuminate\Http\JsonResponse;
use Modules\Shop\Http\Requests\OffersBasedLocationRequest;
use Modules\Shop\Http\Requests\StoreBranchRequest;
use Modules\Shop\Transformers\ShopFeaturedCollection;
use Modules\Shop\Transformers\ShopResource;

class ShopController extends Controller
{
    public function index(FiltersShopRequest $request ,FrontendShopService $shopService)
    {
        try{
            $shopService
            ->setInputs($request->validated())
            ->setInputs(request()->query())
            ->shopFilters()
            ->collectOutput('shops',$shops);
            
            return $this->getSuccessfulIndexShopResponse($shops);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function detail($id,ShopService $shopService)
    {
        try{
            $shopService
            ->setInput('id', $id)
            ->getShop()
            ->collectOutputs($shop);
            return $this->getSuccessfulDetailShopResponse($shop);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function storeBranch(StoreBranchRequest $storeBranchRequest, ShopService $shopService)
    {
        try{
            $shopService
            ->setInputs($storeBranchRequest->validated())
            ->createBranch()
            ->createShop()
            ->createExactLocation()
            ->storeMetaData()
            ->syncCategory()
            ->syncTag()
            ->collectOutput('shop',$shop);

            return response()->json($shop)->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updateBranch(StoreBranchRequest $storeBranchRequest, $id, ShopService $shopService)
    {
        try{
            $shopService
            ->setInputs($storeBranchRequest->validated())
            ->setInput('id', $id)
            ->setInput('authId', Auth::id())
            ->createBranch()
            ->updateShop()
            ->storeMetaData()
            ->updateExactLocation()
            ->syncCategory()
            ->syncTag()
            ->collectOutput('shop',$shop);

            return response()->json($shop)->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getMainBranches(ShopService $shopService)
    {
        try{
            $shopService
            ->getMainBranches()
            ->collectOutputs($shops);
            return response()->json($shops)->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    

    public function store(StoreShopRequest $request, ShopService $shopService)
    {
        try{
            $shopService
            ->setInputs($request->validated())
            ->createMainBranch()
            ->createShop()
            ->createExactLocation()
            ->storeMetaData()
            ->syncCategory()
            ->syncTag()
            ->collectOutput('shop',$shop);
            // return redirect(route('shop.shop-edit', ['id'=>$shop['shop']->id]));
            //by zaid
            return response()->json($shop)->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

        /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(StoreShopRequest $request, $id, ShopService $shopService, AdminCategoryService $categoryService)
    {
        try{
            $shopService
            ->setInputs($request->validated())
            ->setInput('id', $id)
            ->setInput('authId', Auth::id())
            ->updateShop()
            ->storeMetaData()
            ->updateExactLocation()
            ->syncCategory()
            ->syncTag()
            ->collectOutput('shop',$shop);

            return response()->json($shop)->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getAuthorShops(ShopService $shopService)
    {
        try{
            $shopService
            ->getAllShops()
            ->collectOutputs($shops);
            return response()->json($shops)->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getAuthorShop(ShopService $shopService, $id)
    {
        try{
            $shopService
            ->setInput('authId', Auth::id())
            ->setInput('id', $id)
            ->getShopByAuthor()
            ->collectOutputs($shops);
            return response()->json($shops)->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function shopsHasDiscount(ShopService $shopService)
    {
        try{
            $shopService
            ->shopsHasDiscount()
            ->collectOutput('shops',$shops);
            return $this->getSuccessfulFeaturedShopResponse($shops);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updateStatus(UpdateShopStatusRequest $request, ShopService $shopService, $id)
    {
        try{
            $shopService
            ->setInputs($request->validated())
            ->setInput('authId', Auth::id())
            ->setInput('shop_id', $id)
            ->updateShopStatus();
            return response()->json(['message'=>'shop status has been updated successfully'])->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function delete(ShopService $shopService, $id)
    {
        try{
            $shopService
            ->setInput('authId', Auth::id())
            ->setInput('shop_id', $id)
            ->deleteShop();
            return response()->json(['message'=>'shop status has been updated successfully'])->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getOffersBasedLocation(OffersBasedLocationRequest $request, FrontendShopService $shopService)
    {
        try{
            $shopService
            ->setInputs($request->validated())
            ->setInputs(request()->query())
            ->shopFiltersCoordinates()
            ->collectOutput('shops',$shops);
            return $this->getSuccessfulIndexShopResponse($shops);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function getErrorResponse($message, $statusCode)
    {
        $data = [
            'message' => $message,
            'code' => $statusCode,
        ];
        return (new ErrorResource($data))->response()->setStatusCode(403);
    }
    
    private function getSuccessfulIndexShopResponse($data): JsonResponse
    {
        return (new ShopCollection($data))->response()->setStatusCode(200);
    }

    private function getSuccessfulDetailShopResponse($data): JsonResponse
    {
        return (new ShopResource($data))->response()->setStatusCode(200);
    }

    private function getSuccessfulFeaturedShopResponse($data): JsonResponse
    {
        return (new ShopFeaturedCollection($data))->response()->setStatusCode(200);
    }
    

}
