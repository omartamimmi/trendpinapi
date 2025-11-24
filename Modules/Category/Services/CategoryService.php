<?php

namespace Modules\Category\Services;

use App\Abstractions\Service;
use Modules\Category\Repositories\CategoryRepository;

class CategoryService extends Service
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories():static
    {
        $categories = $this->categoryRepository->getAllCategories();
        $this->setOutput('categories', $categories ?? []);
        return $this;
    }

    public function createCategory():static
    {
        $category = $this->categoryRepository->create($this->getInputs());
        $this->setOutput('category', $category);
        return $this;
    }

    public function updateCategory():static
    {
        $this->categoryRepository->update($this->getInput('id'), $this->getInputs());
        $category = $this->categoryRepository->getCategoryById($this->getInput('id'));
        $this->setOutput('category', $category);
        return $this;
    }

    public function getCategory():static
    {
        $category = $this->categoryRepository->getCategoryById($this->getInput('id'));

        $this->setOutput('category', $category);
        return $this;
    }

    public function bulkDeleteCategories():static
    {
        $ids = $this->getInput('ids');
        $this->categoryRepository->delete($ids);
        return $this;
    }

}
