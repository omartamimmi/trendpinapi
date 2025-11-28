<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class WishlistRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer'],
        ];
    }

     /**
     * Get the validation messages that apply to the rule.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'shop_id.required' => __('validation.shop_id.required'),
            'shop_id.integer' => __('validation.shop_id.integer'),
        ];
    }


}
