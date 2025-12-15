<?php

namespace Modules\Log\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LogCollection extends ResourceCollection
{
    public $collects = LogResource::class;

    public function toArray(Request $request): array
    {
        // Get the underlying paginator
        $paginator = $this->resource;

        return [
            'data' => $this->collection,
            'links' => $this->buildLinks($paginator),
            'current_page' => $paginator->currentPage(),
            'first_page_url' => $paginator->url(1),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'next_page_url' => $paginator->nextPageUrl(),
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];
    }

    protected function buildLinks($paginator): array
    {
        $links = [];

        // Previous link
        $links[] = [
            'url' => $paginator->previousPageUrl(),
            'label' => '&laquo; Previous',
            'active' => false,
        ];

        // Page number links - limit to avoid too many links
        $lastPage = $paginator->lastPage();
        $currentPage = $paginator->currentPage();

        for ($i = 1; $i <= $lastPage; $i++) {
            $links[] = [
                'url' => $paginator->url($i),
                'label' => (string) $i,
                'active' => $i === $currentPage,
            ];
        }

        // Next link
        $links[] = [
            'url' => $paginator->nextPageUrl(),
            'label' => 'Next &raquo;',
            'active' => false,
        ];

        return $links;
    }
}
