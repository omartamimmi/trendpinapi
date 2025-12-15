<?php
use Illuminate\Validation\Rule;

return [
    'by_all' => [
        'stage_id' => 'required|string|max:255',
        'step' => 'required|integer',
        'stage_type' => 'required|string|max:255',
    ],
];
  