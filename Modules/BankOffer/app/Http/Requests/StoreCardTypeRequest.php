<?php

namespace Modules\BankOffer\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCardTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_id' => ['nullable', 'exists:banks,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'logo_id' => ['nullable', 'exists:media,id'],
            'card_network' => ['required', 'in:visa,mastercard,amex,other'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
