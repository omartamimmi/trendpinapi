<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminDeleteShopRequest extends FormRequest
{
     /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => [
                'array'
            ]
        ];
    }

    public function bodyParameters()
    {
        return [
            'ids' => [
                'description' => 'User\'s ids',
                'example' => '[1,2,3]'
            ],
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
            'ids.array' => __('The User id field is required'),
        ];
    }

}
