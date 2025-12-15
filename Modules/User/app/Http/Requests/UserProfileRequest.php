<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;
use Modules\User\Rules\IsCurator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class UserProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lang' => [
                'max:10',
                'string',
            ],
        ];
    }

    public function bodyParameters()
    {
        return [
            'lang' => [
                'description' => 'Requested language',
                'example' => 'en'
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
            'lang.max' => __('The Language field cannot be more than :max characters'),
            'lang.string' => __('The Language field must be a valid input'),
        ];
    }
}
