<?php

namespace Modules\Media\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Media\Models\MediaFile;

trait HasMedia
{
    /**
     * Get all media files attached to this model
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(MediaFile::class, 'mediable', 'mediables', 'mediable_id', 'media_file_id')
            ->withPivot(['collection', 'order', 'custom_properties'])
            ->withTimestamps()
            ->orderBy('mediables.order');
    }

    /**
     * Get media files for a specific collection
     */
    public function getMedia(string $collection = 'default'): \Illuminate\Database\Eloquent\Collection
    {
        return $this->media()->wherePivot('collection', $collection)->get();
    }

    /**
     * Get the first media file from a collection
     */
    public function getFirstMedia(string $collection = 'default'): ?MediaFile
    {
        return $this->media()->wherePivot('collection', $collection)->first();
    }

    /**
     * Get the first media URL from a collection
     */
    public function getFirstMediaUrl(string $collection = 'default', string $size = 'medium'): ?string
    {
        $media = $this->getFirstMedia($collection);
        if (!$media) {
            return null;
        }
        return $media->getPresetUrl($size);
    }

    /**
     * Attach media to this model
     */
    public function attachMedia(int|array $mediaIds, string $collection = 'default', array $customProperties = []): void
    {
        $mediaIds = is_array($mediaIds) ? $mediaIds : [$mediaIds];

        $existingCount = $this->media()->wherePivot('collection', $collection)->count();

        foreach ($mediaIds as $index => $mediaId) {
            $this->media()->attach($mediaId, [
                'collection' => $collection,
                'order' => $existingCount + $index,
                'custom_properties' => json_encode($customProperties),
            ]);
        }
    }

    /**
     * Sync media for a collection (removes old, adds new)
     */
    public function syncMedia(array $mediaIds, string $collection = 'default'): void
    {
        // Detach existing media for this collection
        $this->media()->wherePivot('collection', $collection)->detach();

        // Attach new media
        foreach ($mediaIds as $index => $mediaId) {
            $this->media()->attach($mediaId, [
                'collection' => $collection,
                'order' => $index,
                'custom_properties' => json_encode([]),
            ]);
        }
    }

    /**
     * Detach media from this model
     */
    public function detachMedia(int|array $mediaIds, ?string $collection = null): void
    {
        $mediaIds = is_array($mediaIds) ? $mediaIds : [$mediaIds];

        $query = $this->media();
        if ($collection) {
            $query->wherePivot('collection', $collection);
        }

        $query->detach($mediaIds);
    }

    /**
     * Clear all media from a collection
     */
    public function clearMediaCollection(string $collection = 'default'): void
    {
        $this->media()->wherePivot('collection', $collection)->detach();
    }

    /**
     * Update media order in a collection
     */
    public function updateMediaOrder(array $orderedIds, string $collection = 'default'): void
    {
        foreach ($orderedIds as $index => $mediaId) {
            $this->media()->updateExistingPivot($mediaId, [
                'order' => $index,
            ]);
        }
    }

    /**
     * Check if model has any media in a collection
     */
    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->media()->wherePivot('collection', $collection)->exists();
    }

    /**
     * Get media count for a collection
     */
    public function getMediaCount(string $collection = 'default'): int
    {
        return $this->media()->wherePivot('collection', $collection)->count();
    }

    /**
     * Get all media URLs for a collection
     */
    public function getMediaUrls(string $collection = 'default', string $size = 'medium'): array
    {
        return $this->getMedia($collection)->map(function ($media) use ($size) {
            return [
                'id' => $media->id,
                'url' => $media->getPresetUrl($size),
                'thumbnail_url' => $media->thumbnail_url,
                'original_url' => $media->url,
                'file_name' => $media->file_name,
                'file_type' => $media->file_type,
            ];
        })->toArray();
    }
}
