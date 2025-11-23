<?php

namespace Modules\Media\Repositories;

use Illuminate\Support\Facades\Auth;
use Modules\Media\Models\MediaFile;

class MediaRepository
{
    public function create($data)
    {
        return MediaFile::create($data);
    }

    public function getAllMediaByAuthor()
    {
        return MediaFile::where('create_user', Auth::id())->orderByDesc('id')->get()->toArray();
    }

    public function delete($ids)
    {
        return MediaFile::whereIn('id', $ids)->where('create_user', Auth::id())
        ->delete();
    }

    public function getImageById($id): ?MediaFile
    {
        return MediaFile::find($id);
    }
}
