<?php

namespace Modules\Media\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Models\MediaFile;
use Intervention\Image\ImageManager as Image;
use ImageOptimizer;
use Modules\Media\Http\Requests\MediaRequest;
use Modules\Media\Services\MediaService;
use Modules\User\Transformers\ErrorResource;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Media\Http\Requests\AllMediaRequest;

class MediaController extends Controller
{
    /**
     * Store media files
     *
     * @authenticated as user
     *
     * @group Media
     *
     * @response
        "data": {
           "message": "File uploaded successfully"
        }
     */
    public function store(MediaRequest $request, MediaService $mediaService)
    {
        try{
            $mediaService
            ->setInputs($request->validated())
            ->folderGenerate(Auth::id())
            ->storeFile()
            ->prepareFileData()
            ->imageOptimizer()
            ->saveFile()
            ->collectOutputs($media);
            // dd($media);
            return $this->getSuccessfulUserResponse($media);
        }catch(Throwable $e){
            return $this->getErrorResponse($e->getMessage(), 400);
        }
    }


    /**
     * get all media files
     *
     * @authenticated as user
     *
     * @group Media
     *
     * @response
        "data": {
           "media": []
        }
     */
    public function allMedia(MediaService $mediaService)
    {
        try{
            $mediaService
            ->getAllMediaByAuthor()
            ->collectOutputs($media);
            return response()->json($media)->setStatusCode(200);
        }catch(Throwable $e){
            return $this->getErrorResponse($e->getMessage(), 400);
        }
    }

    /**
     * delete media files
     *
     * @authenticated as user
     *
     * @group Media
     *
     * @response
        "data": {
           "message": "File uploaded successfully"
        }
     */
    public function delete(AllMediaRequest $request, MediaService $mediaService)
    {
        try{
            $mediaService
            ->setInputs($request->validated())
            ->delete();

            return $this->getSuccessfulUserResponse('Files deleted successfully');
        }catch(Throwable $e){
            return $this->getErrorResponse($e->getMessage(), 400);
        }
    }

    /**
     * get all media files
     *
     * @authenticated as user
     *
     * @group Media
     *
     * @response
        "data": {
        }
     */
    public function getImage(Request $request, MediaService $mediaService)
    {
        try{
            $mediaService
                ->setInput('id', $request->get('id'))
                ->getImage()
                ->collectOutput('media', $media);
            return response()->json(['data' => $media])->setStatusCode(200);
        }catch(Throwable $e){
            return $this->getErrorResponse($e->getMessage(), 400);
        }
    }

    private function getErrorResponse($message, $statusCode): JsonResponse
    {
        $data = [
            'message' => $message,
            'code' => $statusCode,
        ];
        return (new ErrorResource($data))->response()->setStatusCode($statusCode);
    }


    private function getSuccessfulUserResponse($data): JsonResponse
    {
        return response()->json($data)->setStatusCode(200);
    }
}
