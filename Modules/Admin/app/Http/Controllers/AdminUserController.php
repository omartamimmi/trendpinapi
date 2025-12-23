<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\app\Http\Requests\CreateUserRequest;
use Modules\Admin\app\Http\Requests\UpdateUserRequest;
use Modules\Admin\app\Services\Contracts\UserServiceInterface;

class AdminUserController extends Controller
{
    public function __construct(
        protected UserServiceInterface $userService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $users = $this->userService->getUsers($search);

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $this->userService->updateUser($id, $request->validated());

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->userService->deleteUser($id);

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
