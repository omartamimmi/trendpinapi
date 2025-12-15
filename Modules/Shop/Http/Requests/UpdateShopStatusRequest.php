<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => [
                'required',
                Rule::in(['publish', 'draft']),
            ]
        ];
    }

    public function bodyParameters()
    {
        return [
            'status' => [
                'description' => 'User\'s name',
                'example' => 'John Doe'
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
            'status.required' => __('The status field is required'),
        ];
    }

}
