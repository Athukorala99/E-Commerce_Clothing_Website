<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Analytics\AnalyticsService;
use FluentForm\App\Services\Browser\Browser;
use FluentForm\App\Services\Form\FormService;

class AnalyticsController extends Controller
{
    public function reset(AnalyticsService $analyticsService,$formId)
    {
        try {
            return $this->sendSuccess(
                $analyticsService->reset(intval($formId))
            );
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __('Something went wrong, please try again!', 'fluentform'),
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
