<?php

namespace Modules\Admin\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $planTypes = implode(',', config('admin.plan_types', ['user', 'retailer', 'bank']));

        return [
            'name' => 'required|string|max:255',
            'type' => "required|in:{$planTypes}",
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'offers_count' => 'required|integer|min:1',
            'duration_months' => 'integer|min:1',
            'billing_period' => 'in:monthly,yearly',
            'trial_days' => 'integer|min:0',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ];
    }
}
