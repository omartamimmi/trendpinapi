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

    public function findUserByPhone($phone)
    {
        return User::where('phone', $phone)->first();
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

    /**
     * Save FCM token to user_locations table
     */
    public function saveFcmToken($data)
    {
        return \Illuminate\Support\Facades\DB::table('user_locations')->insert([
            'user_id' => $data['user_id'],
            'fcm_token' => $data['fcm_token'],
            'lat' => $data['lat'] ?? 0,
            'lng' => $data['lng'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Update FCM token in user_locations table
     */
    public function updateFcmToken($id, $data)
    {
        return \Illuminate\Support\Facades\DB::table('user_locations')
            ->where('id', $id)
            ->update([
                'fcm_token' => $data['fcm_token'],
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'updated_at' => now(),
            ]);
    }

    /**
     * Find FCM token in user_locations table
     */
    public function findFcmToken($token)
    {
        return \Illuminate\Support\Facades\DB::table('user_locations')
            ->where('fcm_token', $token)
            ->first();
    }

    /**
     * Find user location by user ID
     */
    public function findUserLocation($userId)
    {
        return \Illuminate\Support\Facades\DB::table('user_locations')
            ->where('user_id', $userId)
            ->first();
    }

    public function updatePassword($id, $data, $lang = 'en'): bool
    {
        $user = User::where('id',$id)->first();
        $user->password = $data;
        return $user->save();
    }
}
