<?php

namespace Modules\Category\Repositories;

use Modules\Category\Models\Category;
use Modules\Shop\Models\Shop;

class CategoryRepository
{

    public function getAllCategories()
    {
        return Category::all();
    }

    public function getCategoryById($id)
    {
        return Category::find($id);
    }

    public function create($data)
    {
        return Category::create($data);
    }

    public function update($id, $data): bool
    {
        $category = Category::find($id);
        $category->fill($data);
        return $category->save($data);
    }

    public function delete($ids)
    {
        return Category::whereIn('id', $ids['ids'])->delete();
    }
}
