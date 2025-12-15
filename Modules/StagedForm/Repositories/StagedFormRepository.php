<?php

namespace Modules\StagedForm\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Modules\StagedForm\Models\StagedForm;
use Modules\StagedForm\Models\StagedFormStep;

class StagedFormRepository
{
    public function getByStageIdTypeAndStep(string $stageId, string $type, string $step): ?StagedForm
    {
        return $this->castingSubmittedForm($this->getQueryBuilder()
            ->where('staged_forms.stage_id', $stageId)
            ->where('staged_forms.stage_type', $type)
            ->where('staged_form_steps.step', $step)
            ->first());
    }

    private function getQueryBuilder()
    {
        return  StagedForm::query()
            ->join('staged_form_steps', 'staged_forms.id', 'staged_form_steps.staged_form_id')
            ->select(
                'staged_forms.id',
                'staged_forms.stage_id',
                'staged_forms.stage_type',
                'staged_forms.user_id',
                'staged_form_steps.step',
                'staged_form_steps.submitted_form',
                'staged_form_steps.staged_form_id'
            );
    }

    private function castingSubmittedForm($stagedForms)
    {
        if ($stagedForms instanceof Collection) {
            foreach ($stagedForms as &$stagedForm) {
                $stagedForm->submitted_form = json_decode($stagedForm->submitted_form, true);
            }
        } else {
            if ($stagedForms != null) {
                $stagedForms->submitted_form = json_decode($stagedForms->submitted_form, true);
            }
        }
        return $stagedForms;
    }

    public function getStageSubmittedFormsCount(string $stageId): int
    {
        return $this->getQueryBuilder()
            ->where('staged_forms.stage_id', $stageId)
            ->count();
    }

    public function getAllByStageId(string $stageId)
    {
        return $this->castingSubmittedForm($this->getQueryBuilder()
            ->where('staged_forms.stage_id', $stageId)
            ->get());
    }

    public function getStepByUserId(string $type, string $step, int $userId): ?object
    {
        return $this->castingSubmittedForm($this->getQueryBuilder()
            ->where('staged_form_steps.step', $step)
            ->where('staged_forms.stage_type', $type)
            ->where('staged_forms.user_id', $userId)
            ->orderByDesc('staged_forms.created_at')
            ->first());
    }

    public function getAllByUserId(int $userId, string $type): ?object
    {
        return $this->castingSubmittedForm($this->getQueryBuilder()
            ->where('staged_forms.user_id', $userId)
            ->where('staged_forms.stage_type', $type)
            ->get());
    }

    public function getByUserId(int $userId): ?StagedForm
    {
        return $this->castingSubmittedForm($this->getQueryBuilder()
            ->where('staged_forms.user_id', $userId)
            ->first());
    }

    public function validateStageIdExists(?string $stageId, ?int $userId, ?string $stageType): bool
    {
        return StagedForm::whereStageId($stageId)
            ->whereStageType($stageType)
            ->whereUserId($userId)
            ->exists();
    }

    public function updateOrCreate(string $step, string $type, $userId, array $data)
    {
        $matchingArr = [
            'stage_type' => $type,
            'stage_id' => $data["stage_id"]
        ];
        if (!empty($userId)) {
            $matchingArr['user_id'] = $userId;
        }
        $stagedForm = StagedForm::updateOrCreate($matchingArr, Arr::only(array: $data, keys: ['stage_type', 'stage_id', 'user_id']));

        StagedFormStep::updateOrCreate([
            'staged_form_id' => $stagedForm->getKey(),
            'step' => $step,
        ], Arr::only(array: $data, keys: ['step', 'submitted_form']));

        return $this->castingSubmittedForm($this->getQueryBuilder()
            ->where('staged_forms.stage_id', $stagedForm->stage_id)
            ->where('staged_forms.user_id', $userId)
            ->where('staged_forms.stage_type', $type)
            ->where('staged_form_steps.step', $step)
            ->first());
    }

    public function updateStagedFormStep(int $stagedFormId, string $step, array $data): bool
    {
        return StagedFormStep::whereStagedFormId($stagedFormId)
            ->whereStep($step)
            ->update($data);
    }

    public function getAllByStageIdTypeUserId(string $stageId, string $type, int $userId)
    {
        return $this->castingSubmittedForm($this->getQueryBuilder()
            ->where('staged_forms.stage_id', $stageId)
            ->where('staged_forms.stage_type', $type)
            ->where('staged_forms.user_id', $userId)

            ->get());
    }
}
