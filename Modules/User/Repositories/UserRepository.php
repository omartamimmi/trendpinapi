<?php

namespace Modules\User\Repositories;

use App\Models\User;
use App\Models\NotificationBasedLocation;
use Spatie\Permission\Models\Role;
use Modules\User\Filters\UserFilter;

class UserRepository
{

    public function getAllUsers()
    {
        return User::paginate(10);
    }

    public function getUserById($id)
    {
        return User::where('id',$id)->with('roles')->first();
    }

    public function create($data)
    {
        return User::create($data);
    }

    public function findUserByEmail($email)
    {
        return User::whereEmail($email)->first();
    }

    public function findUserByAppleId($appleId)
    {
        return User::where('appleId',$appleId)->first();
    }

    public function changePassword($user)
    {
        $user->save();
        return $user->getAttributes();
    }

    public function update($id, $data, $lang = 'en'): bool
    {
        $user = User::find($id);
        $user->fill($data);
        return $user->save();
    }

    public function delete($ids)
    {
        return User::whereIn('id', $ids['ids'])->delete();
    }

    // public function saveFcmToken($data)
    // {
    //     return NotificationBasedLocation::create($data);
    // }

    // public function updateFcmToken($id,$data)
    // {
    //     $token = NotificationBasedLocation::find($id);
    //     $token->fill($data);
    //     return $token->save($data);
    // }

    // public function findFcmToken($token)
    // {
    //     return NotificationBasedLocation::where('fcm_token',$token)->first();
    // }

    public function updatePassword($id, $data, $lang = 'en'): bool
    {
        $user = User::where('id',$id)->first();
        $user->password = $data;
        return $user->save();
    }
}
