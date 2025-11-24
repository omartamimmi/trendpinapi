<?php

namespace Modules\StagedForm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\StagedForm\Database\factories\StagedFormStepFactory;

class StagedFormStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'staged_form_id', // session id
        'step', // step1, step2
        'submitted_form', // you form data
    ];

    protected $casts = [
        'submitted_form' => 'array'
    ];
    
    /**
     * @return BelongsTo
     */
    public function stagedForm(): BelongsTo
    {
        return $this->belongsTo(StagedForm::class);
    }
}
