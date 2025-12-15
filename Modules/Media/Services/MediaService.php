<?php

namespace Modules\Media\Services;

use App\Abstractions\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Media\Repositories\MediaRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Helpers\FileHelper;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Imagick;

class MediaService extends Service
{

    protected $presets = [
        95 => [95, 95],
        100 => [100, 100],
        320 => [320, 240],
        450 => [450, 360],
        688 => [688, 425],
        365 => [365, 241],
        480 => [450, 360],
        137 => [137, 45],
        270 => [270, 298],
        285 => [285, 350]
    ];

    protected ImageManager $imageManager;

    public function __construct(protected MediaRepository $mediaRepository)
    {
        // Use Imagick driver if available, otherwise fall back to GD
        $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $this->imageManager = new ImageManager($driver);
    }


    public function imageOptimizer():static
    {
        if(function_exists('proc_open') and function_exists('escapeshellarg')){
            ImageOptimizer::optimize(storage_path("app/public/". $this->getInput('file_path')));
        }
        return $this;
    }

    public function folderGenerate($id): static
    {
        $folder = '';
        $file = $this->getInput('file');
        if ($id) {
            $folder .= sprintf('%04d', (int)$id / 1000) . '/' . $id . '/';
        }
        $folder = $folder . date('Y/m/d');
        $newFileName = Str::slug(substr($file->getClientOriginalName(), 0, strrpos($file->getClientOriginalName(), '.')));
        if(empty($newFileName)) $newFileName = md5($file->getClientOriginalName());
        $this->setInput('folder', $folder);
        $this->setInput('file_name', $newFileName);

        return $this;
    }

    public function storeFile():static
    {
        $newFileName = $this->getInput('file_name');
        $folder = $this->getInput('folder');
        $file = $this->getInput('file');
        $extension = $file->getClientOriginalExtension();

        if($extension == 'heic' || $extension == 'heif'){
            $extension = 'png';
        }

        $i = 0;

        do {
            $newFileName2 = $newFileName . ($i ? $i : '');
            $testPath = $folder . '/' . $newFileName2 . '.' . $extension;

            $i++;
        } while (Storage::disk('public')->exists($testPath));
       
        $check = $file->storeAs( $folder, $newFileName2 . '.' . $extension,'public');
        if (env('FILESYSTEM_DRIVER') == 's3') {
            $file->storeAs( '/public/' . $folder, $newFileName2 . '.' . $extension,'s3');
        }

        $this->heicExtension($folder, $newFileName2, $file);
        $this->setInput('file_name', $newFileName2.".".$extension);
        $this->setInput('file_path', "/" . $check);
        return $this;

    }

    public function heicExtension($folder, $newFileName2, $file){
        $extension = strtolower($file->getClientOriginalExtension());

        // Only process HEIC/HEIF files
        if (!in_array($extension, ['heic', 'heif'])) {
            return;
        }

        $heicEx = $folder . '/' . $newFileName2 . '.png';

        $imagick = new Imagick($file->path());
        $imagick->setImageFormat('png');
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(75);

        $imagick->writeImage(public_path('storage/'.$heicEx));
        $imagick->destroy();
    }

    public function prepareFileData():static
    {
        $extension = $this->getInput('file')->getClientOriginalExtension();
        if($extension == 'heic' || $extension == 'heif'){
            $extension = 'png';
        }

        $this->setInput('file_size', $this->getInput('file')->getSize());
        $this->setInput('file_type', $this->getInput('file')->getMimeType());
        $this->setInput('file_extension', $extension);
        return $this;
    }

    public function saveFile():static
    {
        $file_name = $this->getInput('file_name');
        $filePath = $this->getInput('file')->path();
        $destinationPath = public_path('/storage/presets');

        $check = $this->getInput('file_path');
        if ($check) {
            if (FileHelper::checkMimeIsImage($this->getInput('file_type'))) {
                list($width, $height, $type, $attr) = getimagesize(storage_path('app/public/'.$check));
                $this->setInput('file_width', $width);
                $this->setInput('file_height', $height);
            }
            $mediaFile = $this->mediaRepository->create($this->getInputs());

            if(Storage::disk('public')->exists($mediaFile->file_path)) {
                if($mediaFile->file_extension != 'svg' && FileHelper::isImage($mediaFile)) {
                    // Create WebP version using Intervention Image v3
                    if($mediaFile->file_type != 'image/webp') {
                        try {
                            $image = $this->imageManager->read(storage_path('app/public' . $mediaFile->file_path));
                            $webpData = $image->toWebp(70);
                            $webpFileName = str_replace(['.jpg', '.jpeg', '.png'], '', $mediaFile->file_name) . '.webp';
                            Storage::disk('public')->put(
                                dirname($mediaFile->file_path) . '/' . $webpFileName,
                                $webpData
                            );

                            if (env('FILESYSTEM_DRIVER') == 's3') {
                                Storage::disk('s3')->put(
                                    'public/' . dirname($mediaFile->file_path) . '/' . $webpFileName,
                                    Storage::disk('public')->get(dirname($mediaFile->file_path) . '/' . $webpFileName)
                                );
                            }
                        } catch (\Exception $e) {
                            // WebP conversion failed, continue without it
                        }
                    }

                    // Create preset sizes using Intervention Image v3
                    foreach ($this->presets as $preset) {
                        $directory = "$preset[0]-$preset[1]";
                        Storage::disk('public')->makeDirectory('presets/' . $directory, intval('0775', 8), true);

                        try {
                            $img = $this->imageManager->read($filePath);
                            $img->cover($preset[0], $preset[1]);
                            $img->save($destinationPath . '/' . $directory . '/' . $this->getInput('file_name'));
                        } catch (\Exception $e) {
                            // Preset creation failed, continue
                        }
                    }
                }
            }

            // Sizes use for uploaderAdapter
            $mediaFile->sizes = [
                'default' => asset('public/' . $mediaFile->file_path),
                '150'     => url('media/preview/'.$mediaFile->id .'/thumb'),
                '600'     => url('media/preview/'.$mediaFile->id .'/medium'),
                '1024'    => url('media/preview/'.$mediaFile->id .'/large'),
            ];
        }
        $this->setOutput('media', $mediaFile);
        return $this;
    }

    public function getAllMediaByAuthor(): static
    {
        $media = $this->mediaRepository->getAllMediaByAuthor();
        $this->setOutput('media', $media);
        return $this;
    }

    public function delete(): static
    {
        $ids = $this->getInput('ids');
        $this->mediaRepository->delete($ids);
        return $this;
    }

    public function getImage(): static
    {
        $id = $this->getInput('id');
        $media = $this->mediaRepository->getImageById($id);
        $this->setOutput('media', $media);
        return $this;
    }
}
