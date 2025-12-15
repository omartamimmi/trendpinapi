<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * List all roles
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Create a new role
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        try {
            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role->load('permissions')
            ], 201);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific role
     */
    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    /**
     * Update a role
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        try {
            $role = Role::findOrFail($id);

            if (isset($validated['name'])) {
                $role->update(['name' => $validated['name']]);
            }

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role->load('permissions')
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a role
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all permissions
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Create a new permission
     */
    public function createPermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        try {
            $permission = Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
