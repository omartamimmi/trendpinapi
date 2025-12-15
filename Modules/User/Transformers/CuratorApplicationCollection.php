<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CuratorApplicationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            
            $data['id'] = $item->id;
            foreach($item->getFillable() as $fill){
                $data[$fill] = $item->$fill;
            }
            return  $data;
        });
    }
}
