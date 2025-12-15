<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectInterestsRequest extends FormRequest
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
            'interest_ids' => ['required', 'array', 'min:1'],
            'interest_ids.*' => ['required', 'integer', 'exists:interests,id'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'interest_ids.required' => 'Please select at least one interest',
            'interest_ids.array' => 'Interest IDs must be an array',
            'interest_ids.min' => 'Please select at least one interest',
            'interest_ids.*.integer' => 'Each interest ID must be a valid number',
            'interest_ids.*.exists' => 'One or more selected interests do not exist',
        ];
    }
}
