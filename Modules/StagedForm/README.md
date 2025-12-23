# StagedForm Module

Multi-step form workflow management for TrendPin.

## Overview

The StagedForm module provides a framework for managing multi-step forms with progress persistence, validation, and state management.

## Architecture

```
StagedForm/
├── app/
│   └── Http/
│       └── Controllers/
│           └── StagedFormController.php
├── Models/
│   ├── StagedForm.php
│   └── StagedFormStep.php
├── Repositories/
│   └── StagedFormRepository.php
├── Services/
│   └── StagedFormService.php
├── Config/
│   ├── get_step.php
│   └── retailer_onboarding.php
├── Requests/
│   ├── StepGetRequest.php
│   └── StepStoreRequest.php
└── routes/
    ├── api.php
    └── web.php
```

## Models

### StagedForm
Tracks form progress and state.

**Fields:**
- `user_id` - Form owner
- `form_type` - Type identifier
- `current_step` - Current step number
- `status` - in_progress, completed
- `data` - JSON form data

### StagedFormStep
Individual step configuration.

**Fields:**
- `staged_form_id` - Parent form
- `step_number` - Step order
- `step_name` - Step identifier
- `data` - Step-specific data
- `is_completed` - Completion flag

## Configuration

Steps are defined in config files:

```php
// Config/retailer_onboarding.php
return [
    'steps' => [
        1 => [
            'name' => 'retailer_details',
            'validation' => [...],
            'fields' => [...],
        ],
        2 => [
            'name' => 'payment_details',
            // ...
        ],
    ],
];
```

## API Endpoints

### Get Form State
```
GET /api/v1/staged-form/{type}
```

### Submit Step
```
POST /api/v1/staged-form/{type}/step/{step}
{
    "field1": "value1",
    "field2": "value2"
}
```

### Get Step Data
```
GET /api/v1/staged-form/{type}/step/{step}
```

## Service Layer

```php
$stagedFormService
    ->setInputs($request->validated())
    ->setUser($user)
    ->setFormType('retailer_onboarding')
    ->processStep(1)
    ->collectOutput('stagedForm', $form);
```

**Key Methods:**
- `initializeForm($type)` - Create new form
- `processStep($step)` - Validate and save step
- `getFormState()` - Get current form state
- `isLastStep($type, $step)` - Check if last step
- `finalizeForm()` - Complete the form

## Step Processing

1. **Validation** - Validate step data against config
2. **Merge** - Merge with existing form data
3. **Save** - Persist step data
4. **Progress** - Update current step

## Usage

The StagedForm module is used by:
- `Modules\RetailerOnboarding` - Retailer onboarding wizard
- Can be extended for other multi-step flows

## Dependencies

- `Ramsey\Uuid` - UUID generation
- Used by `Modules\RetailerOnboarding`
