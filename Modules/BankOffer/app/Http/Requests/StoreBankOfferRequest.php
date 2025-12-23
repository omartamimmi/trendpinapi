<?php

namespace Modules\BankOffer\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_id' => ['required', 'exists:banks,id'],
            'card_type_id' => ['nullable', 'exists:card_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'offer_type' => ['required', 'in:percentage,fixed,cashback'],
            'offer_value' => ['required', 'numeric', 'min:0'],
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'terms' => ['nullable', 'string'],
            'terms_ar' => ['nullable', 'string'],
            'redemption_type' => ['required', 'in:show_only,qr_code,in_app'],
            'max_claims' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
