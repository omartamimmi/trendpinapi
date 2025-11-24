<?php

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => [
                'required',
                'max:5120',
                'mimes:jpeg,png,bmp,gif,svg,heic,heif',
                // Rule::dimensions()->maxWidth(5000)->maxHeight(5000)
            ]
        ];
    }

    public function bodyParameters()
    {
        return [
            'file' => [
                'description' => 'Media file',
                'example' => 'img.png'
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
            'file.required' => __('validation.file.required'),
            'file.mimes' => __('validation.file.mimes'),
            'file.max' => __('validation.file.max'),
        ];
    }
}
