<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'role' => $user->getRoleNames()[0] ?? null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'last_login_at' => $user->last_login_at,
                'status' => $user->status,
                'note' => $this->when(!empty($user->curatorRequest), function () use ($user) {
                    return $user->curatorRequest->application_status;
                }),
                'verify_submit_status'=> $user->verify_submit_status ?? '',

                'application_status' => $this->when(!empty($user->curatorRequest), function () use ($user) {
                    return $user->curatorRequest->application_status;
                }),
            ];
        });
    }
}
