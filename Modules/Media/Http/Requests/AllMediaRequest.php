<?php

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllMediaRequest extends FormRequest
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
               'required',
               'array'
            ]
        ];
    }

    public function bodyParameters()
    {
        return [
            'ids' => [
                'description' => 'Media ids',
                'example' => '[1,2,4]'
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
            'ids.required' => __('The ids field is required'),
        ];
    }

}