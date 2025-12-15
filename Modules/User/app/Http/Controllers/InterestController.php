<?php

namespace Modules\User\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\User\app\Http\Requests\SelectInterestsRequest;
use Modules\User\Services\Contracts\InterestServiceInterface;
use Modules\User\Transformers\InterestResource;

class InterestController extends Controller
{
    public function __construct(
        private readonly InterestServiceInterface $interestService
    ) {}

    /**
     * Get all active interests
     *
     * @group Interests
     * @unauthenticated
     */
    public function index(): JsonResponse
    {
        $interests = $this->interestService->getAllInterests();

        return response()->json([
            'success' => true,
            'data' => InterestResource::collection($interests),
        ]);
    }

    /**
     * Get authenticated user's interests
     *
     * @group Interests
     * @authenticated
     */
    public function getUserInterests(): JsonResponse
    {
        $interests = $this->interestService->getAuthUserInterests();

        return response()->json([
            'success' => true,
            'data' => InterestResource::collection($interests),
        ]);
    }

    /**
     * Set user interests (replaces all existing)
     *
     * @group Interests
     * @authenticated
     */
    public function setInterests(SelectInterestsRequest $request): JsonResponse
    {
        $interests = $this->interestService->setUserInterests(
            $request->validated('interest_ids')
        );

        return response()->json([
            'success' => true,
            'message' => 'Interests updated successfully',
            'data' => InterestResource::collection($interests),
        ]);
    }

    /**
     * Add interests to user (keeps existing)
     *
     * @group Interests
     * @authenticated
     */
    public function addInterests(SelectInterestsRequest $request): JsonResponse
    {
        $interests = $this->interestService->addUserInterests(
            $request->validated('interest_ids')
        );

        return response()->json([
            'success' => true,
            'message' => 'Interests added successfully',
            'data' => InterestResource::collection($interests),
        ]);
    }

    /**
     * Remove interests from user
     *
     * @group Interests
     * @authenticated
     */
    public function removeInterests(SelectInterestsRequest $request): JsonResponse
    {
        $interests = $this->interestService->removeUserInterests(
            $request->validated('interest_ids')
        );

        return response()->json([
            'success' => true,
            'message' => 'Interests removed successfully',
            'data' => InterestResource::collection($interests),
        ]);
    }
}
