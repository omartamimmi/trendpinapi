<?php

namespace Modules\BankOffer\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'logo_id' => ['nullable', 'exists:media,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
