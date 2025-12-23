<?php

namespace Modules\Geofence\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RadarWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Signature verification is done in middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'events' => 'sometimes|array',
            'events.*.type' => 'sometimes|string',
            'events.*.user' => 'sometimes|array',
            'events.*.geofence' => 'sometimes|array',
            // Single event format
            'event' => 'sometimes|array',
            'event.type' => 'sometimes|string',
            'event.user' => 'sometimes|array',
            'event.geofence' => 'sometimes|array',
        ];
    }

    /**
     * Get events from the webhook payload
     */
    public function getEvents(): array
    {
        // Radar can send multiple events or a single event
        if ($this->has('events')) {
            return $this->input('events');
        }

        if ($this->has('event')) {
            return [$this->input('event')];
        }

        // Fallback to treating entire payload as an event
        return [$this->all()];
    }
}
