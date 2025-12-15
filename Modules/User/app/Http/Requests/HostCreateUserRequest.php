<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class HostCreateUserRequest extends FormRequest
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
            'phone' => [
                'required',
                // 'integer',
                'max:15',
            ],
            'location_id' => [
                'required',
                'string',
                'max:255',
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
            'phone_number' => [
                'description' => 'User\'s phone number',
                'example' => '0795953832'
            ],
            'city' => [
                'description' => 'User\'s city',
                'example' => 'Amman'
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
            'name.required' =>__('user::word.The Name field is required'),
            'name.max' => __('user::word.The Name field cannot be more than :max characters'),
            'name.string' => __('user::word.The Name field must be a valid input'),

            'phone.required' => __('user::word.The Phone field is required'),
            'phone.max' => __('user::word.The Phone field cannot be more than :max characters'),

            'location_id.required' => __('user::word.The location field is required'),
            'location_id.max' => __('user::word.The location field cannot be more than :max characters'),
            'location_id.string' => __('user::word.The location field must be a valid input'),
            'image_id.required' => __('user::word.The Image field is required'),

        ];
    }

}
