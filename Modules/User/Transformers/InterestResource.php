<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Media\Helpers\FileHelper;

class InterestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->getImageUrl(),
        ];
    }

    /**
     * Get the image URL
     */
    private function getImageUrl(): ?string
    {
        if (!$this->image_id) {
            return null;
        }

        return FileHelper::url($this->image_id, 'medium');
    }
}
