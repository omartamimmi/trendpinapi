<?php

namespace Modules\Log\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Log\app\Models\ActivityLog;

class GetLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'level' => 'sometimes|string|in:' . implode(',', array_keys(ActivityLog::LEVELS)),
            'min_level' => 'sometimes|string|in:' . implode(',', array_keys(ActivityLog::LEVELS)),
            'channel' => 'sometimes|string|max:50',
            'user_id' => 'sometimes|integer|exists:users,id',
            'user_type' => 'sometimes|string|in:admin,retailer,customer',
            'ip_address' => 'sometimes|ip',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'search' => 'sometimes|string|max:255',
            'has_exception' => 'sometimes|boolean',
            'request_id' => 'sometimes|string|max:36',
            'per_page' => 'sometimes|integer|min:10|max:100',
            'sort_by' => 'sometimes|string|in:logged_at,level,channel',
            'sort_direction' => 'sometimes|string|in:asc,desc',
        ];
    }

    public function filters(): array
    {
        return array_filter($this->validated(), fn($value) => $value !== null && $value !== '');
    }

    public function perPage(): int
    {
        return $this->input('per_page', 50);
    }
}
