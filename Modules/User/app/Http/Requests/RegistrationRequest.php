<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class RegistrationRequest extends FormRequest
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
            'phone_number' => [
                'nullable',
                'string',
                'phone:AUTO',
            ],
            'code' => [
                'required_with:phone_number',
                'string',
                'size:6',
            ],
            'profile_image' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpeg,jpg,png,gif,webp',
            ]
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
            'name.required' => __('validation.name required'),
            'name.max' => __('validation.name max'),
            'name.string' => __('validation.name string'),
            'email.required' => __('validation.email required'),
            'email.email' => __('validation.email email'),
            'email.string' => __('validation.email string'),
            'email.max' => __('validation.email max'),
            'email.unique' => __('validation.email.unique'),
            'password.required' => __('validation.password required'),
            'password.string' => __('validation.password string'),
            'password.min' => __('validation.password.min'),
            'term.required' => __('validation.term.required'),
            'term.boolean' => __('validation.term.boolean'),
            'phone_number.phone' => __('otp::messages.phone_invalid'),
            'code.required_with' => __('otp::messages.code_required'),
            'code.size' => __('otp::messages.code_invalid_length'),
        ];
    }

}
