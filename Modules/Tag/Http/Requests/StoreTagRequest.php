<?php

namespace Modules\Tag\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;

class StoreTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'name_ar'=>[
                'string',
                'max:255',
                'nullable'
            ],
            'description' => [
                'required',
                'string',
                'max:255',
                'nullable'
            ],
            'description_ar' => [
                'string',
                'max:255',
            ],
            'status' => [
                'integer'
            ],
            'image_id'=>[
                'required'
            ],
            'publish_date'=>[],
            'category'=>[]
        ];
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'User\'s name',
                'example' => 'John Doe'
            ],
            'email' => [
                'description' => 'User\'s email',
                'example' => 'john.doe@example.com'
            ],
            'password' => [
                'description' => 'User\'s password',
                'example' => 'aDD87.778'
            ],
            'term' => [
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
            'name.required' => __('tag::word.The Name field is required'),
            'name.max' => __('tag::word.The Name field cannot be more than :max characters'),
            'name.string' => __('tag::word.The Name field must be a valid input'),
            'description.required' => __('tag::word.The description field is required'),
            'description.string' => __('tag::word.The description field must be a valid input'),
            'description.max' => __('tag::word.The description field cannot be more than :max characters'),
            'status.required' => __('tag::word.The status field is required'),
            'status.boolean' => __('tag::word.The status field must be 0 or 1'),
            'image_id'=>__('tag::word.The Image field is required'),
        ];
    }

}
