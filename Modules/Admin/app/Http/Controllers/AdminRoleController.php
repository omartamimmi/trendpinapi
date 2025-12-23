<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $query = Role::with('permissions');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->latest()->paginate(20);
        $permissions = Permission::all();

        return Inertia::render('Admin/Roles', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->back();
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->back();
    }

    public function destroy(int $id)
    {
        Role::findOrFail($id)->delete();
        return redirect()->back();
    }
}
