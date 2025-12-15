<?php
namespace Modules\StagedForm\Services;

use App\Abstractions\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\StagedForm\Models\StagedForm;
use Modules\StagedForm\Repositories\StagedFormRepository;
use Ramsey\Uuid\Type\Integer;
use Ramsey\Uuid\Uuid;

class StagedFormService extends Service
{

   public function __construct(
        private StagedFormRepository $repository,
        private ?User $user = null
    ) {}

    /**
     * Set the authenticated user.
     */
    public function setAuthUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get staged forms for the authenticated user by type and step.
     */
    public function getFormsByUser(string $stageType, int $step): ?StagedForm
    {
        return $this->repository->getStepByUserId($stageType, $step, $this->user->id);
    }

    /**
     * Get a specific staged form and verify ownership.
     */
    public function getUserStagedForm(string $stageId, string $stageType, int $step): ?StagedForm
    {
        $stagedForm = $this->repository->getByStageIdTypeAndStep($stageId, $stageType, $step);

        if (!$stagedForm || $stagedForm->user_id !== $this->user->id) {
            return null;
        }

        return $stagedForm;
    }

    /**
     * Save the staged form for the current user and step.
     *
     * This method retrieves the staged form object, then persists it
     * using the StagedFormRepository. It finally sets the saved staged form
     * as output for further use.
     *
     * @return static Returns the current instance for method chaining.
     */
    public function saveForm(): static
    {
        $data = $this->getStagedFormObject();
        $stagedForm = $this->repository->updateOrCreate($data['step'], $data['stage_type'], $data['user_id'], $data);
        $this->setOutput('stagedForm', $stagedForm);
        return $this;
    }

    /**
     * Build the staged form data array for persistence.
     *
     * @return array{
     *     stage_id: string,
     *     stage_type: mixed,
     *     step: mixed,
     *     user_id: mixed,
     *     submitted_form: array
     * }
     */
    private function getStagedFormObject(): array
    {
        $stageId = $this->getInput('stage_id');
        $stageType = $this->getInput('stage_type');
        $step = $this->getInput('step');
        $userId = $this->user->id;
        $submittedForm = $this->getInput('submitted_form', []);

        // Ensure defaults
        if (empty($submittedForm['time_zone'])) {
            $submittedForm['time_zone'] = 'Asia/Amman';
        }

        // Resolve existing or new staged form
        $stagedForm = $this->resolveStagedForm($stageId, $stageType, $step, $userId);
        $stagedForm->stage_type = $stageType;

        return [
            'stage_id'       => $stagedForm->stage_id,
            'stage_type'     => $stageType,
            'step'           => $step,
            'user_id'        => $stagedForm->user_id,
            'submitted_form' => json_encode($submittedForm),
        ];
    }

    /**
     * Resolve an existing staged form by stage ID, type, and step,
     * or create a new one if none is found.
     *
     * @param string|null $stageId
     * @param mixed       $stageType
     * @param mixed       $step
     * @param mixed       $userId
     * @return StagedForm
     */
    private function resolveStagedForm(?string $stageId, $stageType, $step, $userId): StagedForm
    {
        // dd($stageId);
        if ($stageId) {
            $stagedForm = $this->repository->getByStageIdTypeAndStep(
                $stageId,
                $stageType,
                $step
            );

            if (
                !$stagedForm &&
                $this->repository->getStageSubmittedFormsCount($stageId)
            ) {
                $stagedForm = new StagedForm();
                $stagedForm->stage_id = $stageId;
                $stagedForm->user_id  = $userId;
            }
        }

        if (!isset($stagedForm)) {
            $stagedForm = new StagedForm();
            $stagedForm->stage_id = Uuid::uuid4()->toString();
            $stagedForm->user_id  = $userId;
        }

        return $stagedForm;
    }

    public function getUserStagedFormByStageIdTypeAndStep(): static
    {
        $stageType = $this->getInput('stage_type');
        $step = $this->getInput('step');
        $stageId = $this->getInput('stage_id');
        $stagedForm = $this->repository->getByStageIdTypeAndStep($stageId, $stageType, $step);
        if ($stagedForm && $stagedForm->user_id == $this->user->id) {
            $submittedForm = $stagedForm->submitted_form;
            $stagedForm->submitted_form = $submittedForm;
            $this->setOutput('stagedForm', $stagedForm);
        }
        return $this;
    }

    public function isLastStep(): static
    {
        $stageType = $this->getInput('stage_type');
        $currentStep = $this->getInput('step');

        $steps = config("stagedform.$stageType.steps", []);
        $this->setInput('isLastStep', $currentStep === count($steps));
        return $this;
    }

    private function getAllSteps(): Collection
    {
        $stageId = $this->getInput('stage_id');
        $stageType = $this->getInput('stage_type');
        $userId = $this->user->id;
        return $this->repository->getAllByStageIdTypeUserId($stageId, $stageType, $userId);
    }

    // public function finalizeProcess(): static
    // {
    //     if(!empty($this->getInput('stage_id'))){
    //         $allSteps = $this->getAllSteps();
    //         $merged = [];
    //         foreach ($allSteps as $step) {
    //             $merged = array_merge($merged, json_decode($step->submitted_form, true));
    //         }    
    //         $stageType = $this->getInput('stage_type');
    //         $modules = config("stagedform.$stageType.modules", []);
    //         foreach($modules as $module){
    //             $filtered = array_intersect_key($merged, array_flip($module['fillable']));
    //             $modelClass = $module['class'];
                
    //             // Define unique keys (should come from config for each module)
    //             $uniqueKeys = $module['unique'] ?? ['email'];  
    //             $attributes = array_intersect_key($filtered, array_flip($uniqueKeys));
    //             $values     = array_diff_key($filtered, array_flip($uniqueKeys));
    //             $result = $modelClass::updateOrCreate($attributes, $values);
                
    //             dd($result);
    //         }
    //     }
        
    //     return $this;
    // }

    /**
     * Finalize the staged form process by merging steps and saving data
     * into the configured modules dynamically.
     *
     * @return static
     */
    public function finalizeProcess(): static
    {
        if (!empty($this->getInput('stage_id'))) {
            $allSteps = $this->getAllSteps();

            // Merge all submitted step data
            $merged = [];
            foreach ($allSteps as $step) {
                $merged = array_merge($merged, json_decode($step->submitted_form, true));
            }

            $stageType = $this->getInput('stage_type');
            $modules   = config("stagedform.$stageType.modules", []);

            foreach ($modules as $key => $module) {
                $filtered   = array_intersect_key($merged, array_flip($module['fillable']));
                $modelClass = $module['class'];

                // Decide if we want updateOrCreate or always create
                if ($key === 'business') {
                    // Business should be unique (e.g. by email or retailer_name)
                    $uniqueKeys = $module['unique'] ?? ['email'];  
                    $attributes = array_intersect_key($filtered, array_flip($uniqueKeys));
                    $values     = array_diff_key($filtered, array_flip($uniqueKeys));
                    $record     = $modelClass::updateOrCreate($attributes, $values);
                } else {
                    // Always create new for groups, brands, branches
                    $record = $modelClass::create($filtered);
                }

                // Store created IDs back into $merged so next module can use them
                $merged[$key . '_id'] = $record->id;
                // dd($merged);
            }
        }

        return $this;
    }


    /**
     * Merge all steps into one array.
     *
     * @return array
     */
    protected function mergeAllSteps(): array
    {
        $allSteps = $this->getAllSteps();
        $merged   = [];

        foreach ($allSteps as $step) {
            $merged = array_merge($merged, json_decode($step->submitted_form, true));
        }

        return $merged;
    }

    /**
     * Inject dynamic foreign keys into filtered data.
     *
     * @param string $key
     * @param array $filtered
     * @param array $context
     * @return array
     */
    protected function injectForeignKeys(string $key, array $filtered, array $context): array
    {
        if ($key === 'group' && isset($context['business_id'])) {
            $filtered['business_id'] = $context['business_id'];
        }

        if ($key === 'brand' && isset($context['business_id'])) {
            $filtered['business_id'] = $context['business_id'];
        }

        if ($key === 'branch' && isset($context['brand_id'])) {
            $filtered['brand_id'] = $context['brand_id'];
        }

        return $filtered;
    }

    /**
     * Store context IDs after model creation for chaining relations.
     *
     * @param string $key
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $context
     * @return void
     */
    protected function storeContext(string $key, $model, array &$context): void
    {
        if ($key === 'business') {
            $context['business_id'] = $model->id;
        }

        if ($key === 'brand') {
            $context['brand_id'] = $model->id;
        }
    }


}