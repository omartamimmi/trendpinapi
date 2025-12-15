<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CuratorApplicationResource extends JsonResource
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
            'id' => $this['curator']->id,
            'country' => $this['curator']->country,
            'communication_language' => $this['curator']->communication_language,
            'hear_about_us' => $this['curator']->hear_about_us,
            'self_description' => $this['curator']->self_description,
            'experience_type' => $this['curator']->experience_type,
            'numberOfExperiences' => $this['curator']->numberOfExperiences,
            'ownerOfExperience' => $this['curator']->ownerOfExperience,
            'otherPlatform' => $this['curator']->otherPlatform,
            'image_id' => $this['curator']->image_id,
            'approved_by' => $this['curator']->approved_by,
            'status' => $this['curator']->status,
            'application_status' => $this['curator']->application_status,
            'experience_type_other' => $this['curator']->experience_type_other,
            'birthday' => $this['curator']->birthday,
            'city' => $this['curator']->city,
            'vaccinated' => $this['curator']->vaccinated,
            'user_id'=>$this['user']->id,
            'phone'=>$this['user']->phone,
            'email'=>$this['user']->email,
            'start_hosting'=>$this['curator']->start_hosting,
            'gallery'=>$this['curator']->gallery,
            'experience_link'=>$this['curator']->experience_link,
            'social_media_links'=>$this['curator']->social_media_links,
            'meta_data'=>$this['meta_data']
        ];
    }
}
