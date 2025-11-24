<?php

namespace Modules\Tag\Services;

use App\Abstractions\Service;
use Modules\Tag\Repositories\TagRepository;

class TagService extends Service
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
    

}
