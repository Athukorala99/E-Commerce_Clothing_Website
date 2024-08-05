<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Logger\Logger;

class SubmissionLogController extends Controller
{
    public function get(Logger $logger, $submissionId)
    {
        try {
            return $this->sendSuccess(
                $logger->getSubmissionLogs($submissionId, $this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function remove(Logger $logger)
    {
        try {
            return $this->sendSuccess(
                $logger->remove($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
