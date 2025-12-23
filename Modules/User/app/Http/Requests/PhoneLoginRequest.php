<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PhoneLoginRequest extends FormRequest
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
            'phone_number' => [
                'required',
                'string',
                'phone:AUTO',
                'exists:users,phone',
            ],
            'code' => [
                'required',
                'string',
                'size:6',
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the rule.
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => __('otp::messages.phone_required'),
            'phone_number.phone' => __('otp::messages.phone_invalid'),
            'phone_number.exists' => __('validation.phone_not_registered'),
            'code.required' => __('otp::messages.code_required'),
            'code.size' => __('otp::messages.code_invalid_length'),
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
