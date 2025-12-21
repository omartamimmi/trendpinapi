<?php

namespace Modules\BankOffer\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferParticipationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'all_branches' => ['required', 'boolean'],
            'branch_ids' => ['required_if:all_branches,false', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_ids.required_if' => 'You must select specific branches when not applying to all branches.',
        ];
    }
}
