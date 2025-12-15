<?php

namespace Modules\Otp\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Otp\app\Http\Requests\SendOtpRequest;
use Modules\Otp\app\Http\Requests\VerifyOtpRequest;
use Modules\Otp\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Exception;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP to phone number
     */
    public function send(SendOtpRequest $request): JsonResponse
    {
        try {
            $verification = $this->otpService->send($request->phone_number);

            return response()->json([
                'success' => true,
                'message' => __('otp::messages.code_sent'),
                'data' => [
                    'expires_at' => $verification->expires_at->toIso8601String(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verify OTP code
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $this->otpService->verify($request->phone_number, $request->code);

            return response()->json([
                'success' => true,
                'message' => __('otp::messages.verification_success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check if phone is verified
     */
    public function check(SendOtpRequest $request): JsonResponse
    {
        $isVerified = $this->otpService->isVerified($request->phone_number);

        return response()->json([
            'success' => true,
            'data' => [
                'is_verified' => $isVerified,
            ],
        ]);
    }
}
