<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Category\Models\Category;

class AdminInterestController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $query = Interest::with('categories');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $interests = $query->latest()->paginate(20);

        return Inertia::render('Admin/Interests', [
            'interests' => $interests,
        ]);
    }

    public function create(): Response
    {
        $categories = Category::where('status', 'published')->get();

        return Inertia::render('Admin/InterestCreate', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:draft,published',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $interest = Interest::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        if (!empty($validated['category_ids'])) {
            $interest->categories()->attach($validated['category_ids']);
        }

        return redirect('/admin/interests')->with('success', 'Interest created successfully');
    }

    public function edit(int $id): Response
    {
        $interest = Interest::with('categories')->findOrFail($id);
        $categories = Category::where('status', 'published')->get();

        return Inertia::render('Admin/InterestEdit', [
            'interest' => $interest,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:draft,published',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $interest = Interest::findOrFail($id);
        $interest->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        if (isset($validated['category_ids'])) {
            $interest->categories()->sync($validated['category_ids']);
        } else {
            $interest->categories()->detach();
        }

        return redirect('/admin/interests')->with('success', 'Interest updated successfully');
    }

    public function destroy(int $id)
    {
        $interest = Interest::findOrFail($id);
        $interest->categories()->detach();
        $interest->delete();

        return redirect()->back()->with('success', 'Interest deleted successfully');
    }
}
