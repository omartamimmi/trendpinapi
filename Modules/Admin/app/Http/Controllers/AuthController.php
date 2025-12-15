<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Admin\app\Exceptions\InvalidCredentialsException;
use Modules\Admin\app\Exceptions\UnauthorizedAccessException;
use Modules\Admin\app\Http\Requests\LoginRequest;
use Modules\Admin\app\Services\Contracts\AuthServiceInterface;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $data,
            ]);
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        } catch (UnauthorizedAccessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login.',
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout.',
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $data = $this->authService->getCurrentUser($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
