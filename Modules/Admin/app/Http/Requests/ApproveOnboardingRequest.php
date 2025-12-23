<?php

namespace Modules\Admin\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }
}
