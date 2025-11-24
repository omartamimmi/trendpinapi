<?php

namespace Modules\User\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\User\Services\OnboardingService;

class RetailerController extends Controller
{
    public function __construct(protected OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    public function stepGet()
    {
        dd(323232);    
    }

    public function stepCreate()
    {
        dd(123);
    }

    public function stepUpdate()
    {
        dd(123123);
    }
    
}