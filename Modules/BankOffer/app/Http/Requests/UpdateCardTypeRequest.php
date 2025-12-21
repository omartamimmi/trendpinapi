<?php

namespace Modules\BankOffer\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCardTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_id' => ['nullable', 'exists:banks,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'logo_id' => ['nullable', 'exists:media,id'],
            'card_network' => ['sometimes', 'in:visa,mastercard,amex,other'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }
}
