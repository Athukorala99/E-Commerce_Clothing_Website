<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Report\ReportService;

class ReportController extends Controller
{
    public function form(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->form($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Submission Report
     * @return \WP_REST_Response
     */
    public function submissions(ReportService $reportService)
    {
        try {
            return $this->sendSuccess(
                $reportService->submissions($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
