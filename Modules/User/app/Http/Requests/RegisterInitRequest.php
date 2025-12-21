<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterInitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
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
                'required',
                'string',
                'phone:AUTO',
                'unique:users,phone',
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the rule.
     */
    public function messages(): array
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
            'phone_number.required' => __('otp::messages.phone_required'),
            'phone_number.phone' => __('otp::messages.phone_invalid'),
            'phone_number.unique' => __('validation.phone_already_registered'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
