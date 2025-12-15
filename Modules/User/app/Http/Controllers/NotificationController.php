<?php

namespace Modules\User\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\User\app\Http\Requests\UserInterestNotificationRequest;
use Modules\User\Services\UserService;

class NotificationController extends Controller
{
    /**
     * Enable/disable notifications for a shop based on user interest
     */
    public function userInterestToShop(UserInterestNotificationRequest $request, UserService $userService)
    {
        try {
            $userService
                ->setInputs($request->validated())
                ->setAuthUser(Auth::user())
                ->userEnableNotificationForShop()
                ->collectOutput('data', $data);

            return response()->json([
                'message' => __('status updated successfully')
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
