<?php

namespace Modules\Otp\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            ],
            'code' => [
                'required',
                'string',
                'size:' . config('otp.code_length', 6),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => __('otp::messages.phone_required'),
            'phone_number.phone' => __('otp::messages.phone_invalid'),
            'code.required' => __('otp::messages.code_required'),
            'code.size' => __('otp::messages.code_invalid_length'),
        ];
    }
}
