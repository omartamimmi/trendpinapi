<?php

namespace Modules\User\Services;

use App\Abstractions\Service;
use Modules\User\Repositories\UserRepository;
use Exception;

class LogoutUserService extends Service
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function setAuthUser(int $id): static
    {
        $user = $this->userRepository->getUserById($id);
        //TODO check with Ahmad
        // if (empty($user)) {
        //     throw new Exception(__('User not found'), 404);
        // }
        $this->setOutput('user', $user);
        return $this;
    }

    public function collectUser(&$user): static
    {
        $this->collectOutput('user', $user);
        return $this;
    }

    public function revokeToken(): static
    {
        $this->collectUser($user);
        if (!empty($user)) {
            $user->tokens()->delete();
        }
        return $this;
    }
}
