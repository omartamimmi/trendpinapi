<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Media\Helpers\FileHelper;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' =>  $this['user']->id,
            'name' => $this['user']->name,
            'phone' =>  $this['user']->phone,
            // 'role' =>  $this->getRoleNames() ?? '',
            'first_name' => $this['user']->first_name,
            'last_name' =>  $this['user']->last_name,
            'email' => $this['user']->email,
            'profile_image' => $this->getProfileImageUrl(),
            'zip_code' =>  $this['user']->zip_code ?? '',
            'time_zone' => $this['user']->time_zone ?? '',
            'address2' =>  $this['user']->address2 ?? '',
            'address' =>  $this['user']->address ?? '',
            'bio' => $this['user']->bio ?? '' ,
            'sms_notification' => $this['user']->notification_preferences ?? '',
            'business_name' => $this['user']->business_name ?? '',
            'contact_email' => $this['user']->contact_email ?? '',
            'birthday' => $this['user']->birthday ?? '',
            'location_id' => $this['user']->location_id ?? '',
        ];
    }

    /**
     * Get the profile image URL from image_id
     */
    private function getProfileImageUrl(): ?string
    {
        $imageId = $this['user']->image_id ?? null;

        if (!$imageId) {
            return null;
        }

        return FileHelper::url($imageId, 'medium');
    }
}
