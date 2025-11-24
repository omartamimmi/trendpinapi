<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiltersShopRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() :array
    {
        return [
            'query'=>[

            ],
            'category_ids' => [
                "array"
            ],
            'tag_ids' => [

            ],
           'open_status'=>[

           ],
           'near_by'=>[

           ],
           'best_offer'=>[

           ],
           'lat'=>[],
           'lng'=>[],
           'sort_lat'=>[],
           'sort_lng'=>[],
           'explore'=>[
            'nullable',
            'integer'
           ]

        ];
    }

    public function bodyParameters()
    {
        return [
            'title' => [
                'description' => 'User\'s name',
                'example' => 'John Doe'
            ],
            'description' => [
                'description' => 'User\'s email',
                'example' => 'john.doe@example.com'
            ],
            'status' => [
                'description' => 'User\'s password',
                'example' => 'aDD87.778'
            ],
            'days' => [
                'description' => 'Confirm that the user accepts the terms & conditions',
                'example' => 1
            ]
        ];
    }

    /**
     * Get the validation messages that apply to the rule.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => __('validation.title.required'),
            'title.max' => __('validation.title.max'),
            'title.string' => __('validation.title.string'),
            'description.required' => __('validation.description.required'),
            'description.string' => __('validation.description.string'),
            'description.max' => __('validation.description.max'),
            'status.required' => __('validation.status.required'),
            'status.sting' => __('validation.status.sting'),
            'days.required' => __('validation.days.required'),
            'days.boolean' => __('validation.days.boolean'),
        ];
    }

}
