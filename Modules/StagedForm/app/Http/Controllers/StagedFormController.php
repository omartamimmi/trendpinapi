<?php

namespace Modules\StagedForm\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Modules\StagedForm\app\Http\Requests\StepGetRequest;
use Modules\StagedForm\app\Http\Requests\StepStoreRequest;
use Modules\StagedForm\Models\StagedForm;
use Modules\StagedForm\Services\StagedFormService;
use Throwable;

class StagedFormController extends Controller
{
    public function __construct(private StagedFormService $service) {}

    /**
     * Retrieve a staged form for the authenticated user based on stage ID, type, and step.
     *
     * This method accepts request parameters (`stage_id`, `stage_type`, and `step`),
     * passes them to the StagedFormService, and fetches the corresponding staged form
     * if it belongs to the authenticated user. Returns the staged form as a JSON response
     * or an error response if an exception occurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable If any error or exception occurs during staged form retrieval.
     */
    public function get(StepGetRequest $request)
    {
        try{
            $this->service
                ->setInputs($request->validated())
                ->setAuthUser(Auth::user())
                ->getUserStagedFormByStageIdTypeAndStep()
                ->collectOutput('stagedForm', $stagedForm);

            return response()->json($stagedForm);
        }catch(Throwable $e){
            return $this->errorResponse($e);
        }
    }

    /**
     * Store a staged form submission for a given step.
     *
     * This method validates the request using StepStoreRequest, sets up the service inputs,
     * associates the authenticated user, and persists the staged form data.
     * Returns a JSON response with the saved staged form or an error response if an exception occurs.
     *
     * @param  \Modules\StagedForm\Http\Requests\StepStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable If any error or exception occurs during processing.
     */
    public function store(StepStoreRequest $request)
    {
        try{
            $this->service
                ->setInput('submitted_form', $request->validated())
                ->setInput('stage_id', $request->get('stage_id'))
                ->setInput('stage_type', $request->input('stage_type'))
                ->setInput('step', $request->input('step'))
                ->setAuthUser(Auth::user())
                ->saveForm()
                ->isLastStep()
                // ->finalizeProcess()
                ->collectOutput('stagedForm', $stagedForm);
            // dd($this->service->isLastStep($stageType, $step));
           
        
            return response()->json($stagedForm);
        }catch(Throwable $e){
            dd($e);
            return $this->errorResponse($e);
        }
    }

    /**
     * Handle error response
     * 
     * @param  Throwable $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function errorResponse($e)
    {
        $code = ($e->getCode() != 0) ? $e->getCode() : 500;
        return response()->json([
            'error' => [
                'message' => $e->getMessage(),
                'code' => $code,
            ]
        ])->setStatusCode($code);
    }
}