<?php

namespace Modules\User\Services;

use App\Abstractions\Service;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\User\Repositories\UserRepository;
use Modules\Media\Services\MediaService;

class AuthService extends Service
{
    protected $userRepository;
    protected $logoutUserService;
    protected $mediaService;

    public function __construct(
        UserRepository $userRepository,
        LogoutUserService $logoutUserService,
        MediaService $mediaService
    ) {
        $this->userRepository = $userRepository;
        $this->logoutUserService = $logoutUserService;
        $this->mediaService = $mediaService;
    }


    public function getAuthUser()
    {
        $user = Auth::user();
        return $user;
    }

    public function persistUserBasicInfo():static
    {
        $data = $this->getInputs();

        // Handle profile image upload if provided
        if (isset($data['profile_image']) && $data['profile_image'] instanceof \Illuminate\Http\UploadedFile) {
            $imageId = $this->uploadProfileImage($data['profile_image']);
            if ($imageId) {
                $data['image_id'] = $imageId;
            }
            unset($data['profile_image']);
        }

        $user = $this->userRepository->create($data);
        $this->setOutput('user', $user);
        return $this;
    }

    /**
     * Upload profile image and return the media file ID
     */
    protected function uploadProfileImage(\Illuminate\Http\UploadedFile $file): ?int
    {
        try {
            $this->mediaService
                ->setInput('file', $file)
                ->folderGenerate(0) // Use 0 for new users, will be organized by date
                ->storeFile()
                ->prepareFileData()
                ->imageOptimizer()
                ->saveFile()
                ->collectOutput('media', $media);

            return $media->id ?? null;
        } catch (\Exception $e) {
            // Log the error but don't fail registration
            \Log::warning('Failed to upload profile image during registration: ' . $e->getMessage());
            return null;
        }
    }

    public function assignRoleToUser():static
    {
        $role = $this->getInput('role');
        $this->collectOutput('user', $user);
        if($role){
            $user->assignRole($role);
        }
        return $this;
    }

    public function attemptAuthentication(): static
    {
        $email = $this->getInput('email');
        $password = $this->getInput('password');
        $remember = $this->getInput('remember');
        $authAttempt = Auth::attempt(['email' => $email, 'password' => $password], $remember);
        if (!$authAttempt) {
            throw new Exception(__('validation.password or email incorrect'), 403);
        }
        return $this;
    }

    public function setAuthUser(int $id): static
    {
        $user = $this->userRepository->getUserById($id);
        $this->setOutput('user', $user);
        return $this;
    }

    public function collectUser(&$user): static
    {
        $this->collectOutput('user', $user);
        return $this;
    }

    public function checkIfUserBlocked(): static
    {
        $this->collectUser($user);
        if (in_array($user->status, ['blocked'])) {
            $this->logoutUserService
                ->setAuthUser($user->id)
                ->revokeToken();
            throw new Exception(__('Your account has been blocked'), 401);
        }
        return $this;
    }

    public function updateLastLogin(): static
    {
        $this->collectUser($user);
        $this->userRepository->update($user->id, [
            'last_login_at' => Carbon::now()
        ]);
        return $this;
    }

    public function createUserLoginToken(): static
    {
        $this->collectUser($user);
        // Create sanctum login token with expiry date 1 month from creation
        $token = $user->createToken('login-token', ['*'], Carbon::now()->addMonth());
        $loginArray = [
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $this->getProfileImageUrl($user->image_id),
                'role' => $user->getRoleNames()[0] ?? null,
                'type' => 'Bearer',
                'access_token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at
            ]
        ];
        $this->setOutputs($loginArray);
        return $this;
    }

    /**
     * Get profile image URL from image_id
     */
    protected function getProfileImageUrl(?int $imageId): ?string
    {
        if (!$imageId) {
            return null;
        }

        return \Modules\Media\Helpers\FileHelper::url($imageId, 'medium');
    }

    public function getUserData(): static
    {
        $lang = $this->getInput('lang');
        $userId = $this->getAuthUser()->id;
        $user = $this->userRepository->getUserById($userId);
        $this->setOutput('user', $user);
        return $this;
    }

    public function updateProfile(): static
    {
        $this->collectUser($user);
        $this->userRepository->update($user->id, $this->getInputs(), $this->getInput('lang', 'en'));
        return $this;
    }

    public function ChangeMyPassword():static
    {
        $id = $this->getInput('authId');
        $password = Hash::make($this->getInput('password'));
        $this->userRepository->updatePassword($id,$password);

        return $this;
    }

    public function persistUserBasicInfoForSocialLogin():static
    {
        $data = $this->getInputs();
        $user = null;
        if(isset($data['email'])){
            $user = $this->userRepository->findUserByEmail($data['email']);
        }
        if(isset($data['appleId']) && empty($user)){
            $user = $this->userRepository->findUserByAppleId($data['appleId']);
        }
        $this->setOutput('user', $user);

        if(empty($user)){
            $this->setInput('role', 'customer');
            $user = $this->userRepository->create($data);
            $this->setOutput('user', $user);
            $this->assignRoleToUser();
        }
        return $this;
    }

    public function sendVerifications():static
    {
        $data = $this->getInputs();
        // $phone = $this->otpService->send($data['phone_number'], 'sms');
// dd($phone);
        return $this;
    }
}
