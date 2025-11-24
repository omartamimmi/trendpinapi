<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class AdminCreateUserRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:8'
            ],
            'phone' => [
                'required',
                // 'integer',
                'max:15',
            ],
            'location_id' => [

            ],
            'birthday'=>[],

            'image_id'=>'required',
            'enable_notification'=>[]
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
            'name.required' => __('user::word.The Name field is required'),
            'name.max' => __('user::word.The Name field cannot be more than :max characters'),
            'name.string' => __('user::word.The Name field must be a valid input'),
            'email.required' => __('user::word.The Email field is required'),
            'email.email' => __('user::word.The Email is invalid'),
            'email.string' => __('user::word.The Email field must be a valid input'),
            'email.max' => __('user::word.The Email field cannot be more than :max characters'),
            'email.unique' => __('user::word.The Email has already been taken'),
            'password.required' => __('user::word.The Password field is required'),
            'password.string' => __('user::word.The Password field must be a valid input'),
            'password.min' => __('user::word.The Password field cannot be less than :min characters'),
            // 'term.required' => __('The Terms & Conditions field is required'),
            // 'term.boolean' => __('The Terms & Conditions field must be 0 or 1'),
            'phone.required' => __('user::word.The Phone field is required'),
            'phone.max' => __('user::word.The Phone field cannot be more than :max characters'),
            'image_id.required' => __('user::word.The Image field is required'),
        ];
    }

}
