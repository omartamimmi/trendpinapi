<?php
namespace Modules\StagedForm\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepStoreRequest extends FormRequest
{
    public function rules()
    {
        $step = $this->get('step');
        return config("stagedform.retailer_onboarding.steps.$step", []);
    }
}