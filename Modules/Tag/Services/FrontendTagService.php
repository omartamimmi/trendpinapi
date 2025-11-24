<?php

namespace Modules\Tag\Services;

use App\Abstractions\Service;
use Modules\Tag\Repositories\TagRepository;

class FrontendTagService extends Service
{
    protected $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function getAllTags():static
    {
        $tags = $this->tagRepository->getAllTags();
        $this->setOutput('tags', $tags ?? []);
        return $this;
    }

    public function createTag():static
    {
        $tag = $this->tagRepository->create($this->getInputs());
        $this->setOutput('tag', $tag);
        return $this;
    }

    public function updateTag():static
    {
        $this->tagRepository->update($this->getInput('id'), $this->getInputs());
        $tag = $this->tagRepository->getTagById($this->getInput('id'));
        $this->setOutput('tag', $tag);
        return $this;
    }

    public function getTag():static
    {
        $tag = $this->tagRepository->getTagById($this->getInput('id'));
        $cats = [];
        if(!empty($tag)){
            foreach($tag->category_tags()->get() as $cat){
                array_push($cats, ['id'=>$cat->id, 'name'=>$cat->name]);
            }
        }
        $this->setOutput('cats', $cats);
        $this->setOutput('tag', $tag);
        return $this;
    }

    public function bulkDeleteTags():static
    {
        $ids = $this->getInput('ids');
        $this->tagRepository->delete($ids);
        return $this;
    }

    public function getAllTagsApi():static
    {
        $tags = $this->tagRepository->getAllTagsApi();
        $this->setOutput('tags', $tags ?? []);
        return $this;
    }

    public function getTagBasedCategory():static
    {
        $tag = $this->tagRepository->getTagBasedCategory($this->getInput('category_id'));
        $this->setOutput('tag', $tag);
        return $this;
    }

}
