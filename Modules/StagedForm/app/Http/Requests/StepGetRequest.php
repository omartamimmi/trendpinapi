<?php
namespace Modules\StagedForm\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepGetRequest extends FormRequest
{
    public function rules()
    {
        return config("stagedform.get_step.by_all", []);
    }
}