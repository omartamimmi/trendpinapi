<?php

namespace Modules\Admin\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_notes' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_notes.required' => 'Please provide a reason for rejection.',
        ];
    }
}
