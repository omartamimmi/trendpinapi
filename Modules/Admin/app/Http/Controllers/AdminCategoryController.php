<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Category\Models\Category;

class AdminCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $categories = $query->latest()->paginate(20);

        return Inertia::render('Admin/Categories', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CategoryCreate');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        Category::create($validated);

        return redirect('/admin/categories')->with('success', 'Category created successfully');
    }

    public function edit(int $id): Response
    {
        $category = Category::findOrFail($id);

        return Inertia::render('Admin/CategoryEdit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        Category::findOrFail($id)->update($validated);

        return redirect('/admin/categories')->with('success', 'Category updated successfully');
    }

    public function destroy(int $id)
    {
        Category::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Category deleted successfully');
    }
}
