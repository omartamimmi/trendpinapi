<?php

namespace Modules\User\Services;

use App\Abstractions\Service;
use Modules\Shop\Models\Shop;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\User\Repositories\UserRepository;
use LamaLama\Wishlist\Wishlistable;
use Modules\Business\app\Models\Brand;

class UserService extends Service
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers():static
    {
        $users = $this->userRepository->getAllUsers();
        $this->setOutput('users', $users);
        return $this;
    }

    public function createUser():static
    {

        $data = $this->getInputs();
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);
        $this->setOutput('user', $user);
        return $this;
    }

    public function assignRole(): static
    {
        $this->collectOutputs($user);
        $user['user']->assignRole('customer');

        return $this;
    }
    public function getUser():static
    {
        $user = $this->userRepository->getUserById($this->getInput('id'));
        $this->checkAuthority($user);

        $this->setOutput('user', $user);

        return $this;
    }
    public function updateUser():static
    {
        $user = $this->userRepository->getUserById($this->getInput('id'));
        $this->checkAuthority($user);
        $data = $this->getInputs();
        $this->userRepository->update($this->getInput('id'),$data);
        $this->setOutput('user', $user);
        return $this;
    }
    public function bulkDeleteUsers():static
    {
        $ids = $this->getInput('ids');
        $this->userRepository->delete($ids);
        return $this;
    }


    /**
     *
     * @param array $data
     * @return array
     */
    public function deactivate(): static
    {
        $user = $this->getInput('user');
        $user = $this->userRepository->findUserByEmail($user->email);
        $user->name = '';
        $user->email .= '_d';
        $user->phone = '';
        // $user->city_id = '';
        // $user->state = '';
        // $user->country = '';
        // $user->zip_code = '';
        // $user->time_zone = '';
        // $user->bio = '';
        // $user->business_name = '';
        $user->save();
        $user->delete();
        return $this;
    }

    public function setAuthUser($user)
    {
        $this->setOutput('user', $user);
        return $this;
    }

    public function addShopToWishlist():static
    {
        $this->collectOutput('user', $user);
        $shop = Shop::find($this->getInput('shop_id'));

        if (!$shop) {
            throw new Exception(__('validation.shop_not_found'), 404);
        }

        $user->wish($shop);
        return $this;
    }

    public function addBrandToWishlist():static
    {
        $this->collectOutput('user', $user);
        $brand = Brand::find($this->getInput('brand_id'));

        if (!$brand) {
            throw new Exception(__('validation.brand_not_found'), 404);
        }

        $user->wish($brand);
        return $this;
    }

    public function removeBrandFromWishlist():static
    {
        $this->collectOutput('user', $user);
        $brand = Brand::find($this->getInput('brand_id'));

        if (!$brand) {
            throw new Exception(__('validation.brand_not_found'), 404);
        }

        $user->unwish($brand);
        return $this;
    }

    public function removeShopFromWishlist():static
    {
        $this->collectOutput('user', $user);
        $shop = Shop::find($this->getInput('shop_id'));

        if (!$shop) {
            throw new Exception(__('validation.shop_not_found'), 404);
        }

        $user->unwish($shop);
        return $this;
    }

    public function getAllUserWishlist():static
    {
        $this->collectOutput('user', $user);

        $this->setOutput('wishlist', $user->wishlist());

        return $this;
    }

    public function saveFcmToken():static
    {
        $this->collectOutput('user', $user);

        if (empty($user)) {
            return $this;
        }

        $fcmToken = $this->getInput('fcm_token');

        // Update user's fcm_token
        $this->userRepository->update($user->id, ['fcm_token' => $fcmToken]);

        // Prepare data for user_locations table
        $data = [
            'user_id' => $user->id,
            'lat' => $this->getInput('ip_lat') ?? 0,
            'lng' => $this->getInput('ip_lng') ?? 0,
            'fcm_token' => $fcmToken,
        ];

        // Check if user already has a location record
        $existingLocation = $this->userRepository->findUserLocation($user->id);

        if (empty($existingLocation)) {
            // Create new record
            $this->userRepository->saveFcmToken($data);
        } else {
            // Update existing record
            $this->userRepository->updateFcmToken($existingLocation->id, $data);
        }

        return $this;
    }

    public function userEnableNotificationForShop():static
    {
        $this->collectOutput('user', $user);
        $shopId = $this->getInput('shop_id');
        $status = $this->getInput('status');
        if($status){
            $user->user_interest_shop()->syncWithoutDetaching($shopId);
        }else{
            $user->user_interest_shop()->detach($shopId);
        }

        return $this;
    }

    public function checkAuthority($user)
    {
        if($user->create_user != $this->getInput('authId')){
            throw new Exception('unauthorized', 403);
        }
    }

    public function changePassword(){
        $newPass = $this->getInput('password');
        $data = [
            'password' =>bcrypt($newPass),
        ];
        $this->collectOutput('user',$user);
        $this->userRepository->update($user->id,$data);

    }
}
