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
<<<<<<< HEAD
=======
    public function __construct(protected MediaService $mediaService) {}

>>>>>>> main
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
<<<<<<< HEAD
=======

    /**
     * Upload multiple files at once
     */
    public function uploadMultiple(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'required|file|max:10240|mimes:jpeg,png,bmp,gif,svg,heic,heif,webp,pdf',
            ]);

            $uploaded = [];
            foreach ($request->file('files') as $file) {
                $this->mediaService
                    ->setInput('file', $file)
                    ->folderGenerate(Auth::id())
                    ->storeFile()
                    ->prepareFileData()
                    ->imageOptimizer()
                    ->saveFile()
                    ->collectOutputs($media);

                $uploaded[] = $media['media'];
            }

            return response()->json([
                'success' => true,
                'media' => $uploaded,
            ]);
        } catch (Throwable $e) {
            return $this->getErrorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get a single media file by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $media = MediaFile::findOrFail($id);

            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'file_path' => $media->file_path,
                    'file_size' => $media->file_size,
                    'file_type' => $media->file_type,
                    'file_extension' => $media->file_extension,
                    'file_width' => $media->file_width,
                    'file_height' => $media->file_height,
                    'url' => $media->url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'created_at' => $media->created_at,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->getErrorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Get multiple media files by IDs
     */
    public function getByIds(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer',
            ]);

            $media = MediaFile::whereIn('id', $request->ids)->get();

            return response()->json([
                'success' => true,
                'media' => $media->map(fn($m) => [
                    'id' => $m->id,
                    'file_name' => $m->file_name,
                    'file_type' => $m->file_type,
                    'url' => $m->url,
                    'thumbnail_url' => $m->thumbnail_url,
                ]),
            ]);
        } catch (Throwable $e) {
            return $this->getErrorResponse($e->getMessage(), 400);
        }
    }
>>>>>>> main
}
