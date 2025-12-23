<?php

namespace Modules\User\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\app\Http\Requests\LoginRequest;
use Modules\User\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Notification\Services\NotificationService;
use Modules\Notification\app\Services\AsyncNotificationService;
use Modules\User\app\Http\Requests\SendNotificationBasedLocation;
use Modules\User\app\Http\Requests\StoreUserFcmRequest;
use Laravel\Socialite\Facades\Socialite;
use Modules\User\app\Http\Requests\ChangePasswordRequest;
use Modules\User\Services\UserService;
use Modules\User\Transformers\AuthResource;
use Modules\User\Transformers\UserResource;
use Modules\User\Services\LogoutUserService;
use Modules\User\Transformers\ErrorResource;
use Modules\User\app\Http\Requests\RegistrationRequest;
use Modules\User\app\Http\Requests\SocialLoginMobile;
use Modules\User\app\Http\Requests\UpdateUserProfileRequest;
use Modules\User\app\Http\Requests\UserProfileRequest;
use Modules\User\app\Http\Requests\PhoneLoginRequest;
use Modules\User\app\Http\Requests\RegisterInitRequest;
use Modules\User\app\Http\Requests\RegisterCompleteRequest;
use Modules\User\Transformers\UserProfileResource;
use Exception;

class AuthController extends Controller
{

    public function login(LoginRequest $request, AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->attemptAuthentication()
                ->setAuthUser(Auth::id())
                ->checkIfUserBlocked()
                ->updateLastLogin()
                ->createUserLoginToken()
                ->collectOutput('data', $data);
            return $this->getSuccessfulUserLoginResponse($data);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function loginWithPhone(PhoneLoginRequest $request, AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->authenticateByPhone()
                ->checkIfUserBlocked()
                ->updateLastLogin()
                ->createUserLoginToken()
                ->collectOutput('data', $data);
            return $this->getSuccessfulUserLoginResponse($data);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function register(RegistrationRequest $request, AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->setInput('contact_email',$request->input('email'))
                ->setInput('password',Hash::make($request->input('password')))
                ->persistUserBasicInfo()
                ->setInput('role', 'customer')
                ->assignRoleToUser()
                ->createUserLoginToken()
                ->collectOutput('data', $data);

            // Queue welcome notification to the new customer (non-blocking)
            $userId = $data['id'] ?? null;
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    AsyncNotificationService::sendNewCustomerNotification($user);
                }
            }

            return $this->getSuccessfulUserCreationResponse($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Step 1: Initialize registration - validate data and send OTP
     */
    public function registerInit(RegisterInitRequest $request, AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->setInput('password', Hash::make($request->input('password')))
                ->storePendingRegistration()
                ->collectOutput('expires_at', $expiresAt)
                ->collectOutput('phone_number', $phoneNumber);

            return response()->json([
                'message' => __('otp::messages.otp_sent'),
                'phone_number' => $phoneNumber,
                'expires_at' => $expiresAt,
            ])->setStatusCode(200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Step 2: Complete registration - verify OTP and create user
     */
    public function registerComplete(RegisterCompleteRequest $request, AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->completePendingRegistration()
                ->setInput('role', 'customer')
                ->assignRoleToUser()
                ->createUserLoginToken()
                ->collectOutput('data', $data);

            // Queue welcome notification to the new customer (non-blocking)
            $userId = $data['id'] ?? null;
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    AsyncNotificationService::sendNewCustomerNotification($user);
                }
            }

            return $this->getSuccessfulUserCreationResponse($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updateUserProfile(UpdateUserProfileRequest $request, AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->setAuthUser(Auth::id())
                ->updateProfile()
                ->collectOutputs($user);
            return $this->getSuccessfulUserProfileUpdateResponse();
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getUserProfile(UserProfileRequest $request, AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->getUserData()
                ->collectOutputs($user);
            return $this->getSuccessfulUserProfileResponse($user);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function logout(LogoutUserService $logoutUserService)
    {
        $logoutUserService
            ->setAuthUser(Auth::id())
            ->revokeToken();
        return $this->getSuccessfulResponse();
    }

    public function saveToken(StoreUserFcmRequest $request, UserService $userService)
    {
        try {
            // Get location from IP as fallback
            $location = getLocation();

            // Override with device location if provided
            if ($request->has('lat') && $request->has('lng')) {
                $location['ip_lat'] = $request->validated('lat');
                $location['ip_lng'] = $request->validated('lng');
            }

            $userService
                ->setAuthUser(Auth::user())
                ->setInputs($location)
                ->setInput('fcm_token', $request->validated('token'))
                ->saveFcmToken();

            return response()->json(['message' => 'Token saved successfully.'])->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function ChangeMyPassword(ChangePasswordRequest $request, AuthService $authService)
    {
        try{
            $authService
                ->setInputs($request->validated())
                ->setInput('authId', Auth::id())
                ->ChangeMyPassword();
            return response()->json(['message'=>'Password has been updated successfully'])->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function socialLoginMobile(SocialLoginMobile $request,AuthService $authService)
    {
        try {
            $authService
                ->setInputs($request->validated())
                ->setInput('password',Hash::make(123123123))
                ->persistUserBasicInfoForSocialLogin()

                ->createUserLoginToken()
                ->collectOutput('data', $data);

            return $this->getSuccessfulUserCreationResponse($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function destroy(UserService $userService)
    {
        $user = Auth::user();
        $userService
            ->setInput('user',$user)
            ->deactivate();
        return response()->json(['message'=>'Your account has been deleted'], 200);
    }

    public function sendNotification(SendNotificationBasedLocation $request, NotificationService $notificationService)
    {
        try{
            $notificationService
            ->setInput('user', Auth::user())
            ->setInput('fcm_token', $request->input('fcm_token'))
            ->sendNotification();   
            return response()->json(['message'=>'Notification sent successfully'])->setStatusCode(200);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function getSuccessfulUserLoginResponse($data)
    {
        return (new AuthResource($data))->response()->setStatusCode(200);
    }

    private function getErrorResponse($message, $statusCode)
    {
        $data = [
            'message' => $message,
            'code' => $statusCode,
        ];
        return (new ErrorResource($data))->response()->setStatusCode(403);
    }

    private function getSuccessfulUserCreationResponse($user)
    {
        return (new UserResource($user))->response()->setStatusCode(201);
    }

    private function getSuccessfulResponse()
    {
        return response()->json()->setStatusCode(204);
    }

    private function getSuccessfulUserProfileResponse($user): JsonResponse
    {
        return (new UserProfileResource($user))->response()->setStatusCode(200);
    }

    private function getSuccessfulUserProfileUpdateResponse(): JsonResponse
    {
        return response()->json()->setStatusCode(204);
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }


    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        // Check if user already exists
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            // log the user in
            auth()->login($existingUser);
        } else {
            // create a new user account
            $newUser = new User();
            $newUser->name = $user->getName();
            $newUser->email = $user->getEmail();
            $newUser->password = Hash::make(Str::random(10));
            $newUser->save();

            // log the user in
            auth()->login($newUser);
        }
        $existingUser->createToken('login-token', ['*'], Carbon::now()->addMonth());
        return redirect()->to('/home');
    }


    public function socialLogin(Request $request)
    {
        $provider = "facebook";
        $token = $request->input('access_token');
        $providerUser = Socialite::driver($provider)->userFromToken($token);
        $user = User::where('name', $providerUser->name)
            ->where('email', $providerUser->email ?? '')
            ->first();

        if ($user == null) {
            $user = User::create([
                'name' => $providerUser->name,
                'email' => $providerUser->email ?? '',
                'password' => Hash::make($providerUser->password),
                'created_at' => $providerUser->created_at,
            ]);

        }
        $user->createToken('login-token', ['*'], Carbon::now()->addMonth());

        $userToken = $user->createToken($providerUser->token);
        // $user = Auth::loginUsingId($user->id);

        $response = (object) [];
        $response->user = UserResource::make($user);
        $response->token = $userToken->token;
        return response()->json($response, 200);

    }

}
