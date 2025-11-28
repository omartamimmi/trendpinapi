<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class ChangePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|string|min:8|same:password_confirmation',
            'password_confirmation'=>'required|string|min:8'
        ];
    }

    public function bodyParameters()
    {
        return [

            'password' => [
                'description' => 'User\'s password',
                'example' => 'aDD87.778'
            ],

            'password_confirm' => [
                'description' => 'User\'s password',
                'example' => 'aDD87.778'
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

            'password.required' => __('The Password field is required'),
            'password.string' => __('The Password field must be a valid input'),
            'password.min' => __('The Password field cannot be less than :min characters'),
            'password_confirm.required' => __('The Confirm Password field is required'),
            'password_confirm.string' => __('The Confirm Password field must be a valid input'),
            'password_confirm.min' => __('The Confirm Password field cannot be less than :min characters')

        ];
    }

}
