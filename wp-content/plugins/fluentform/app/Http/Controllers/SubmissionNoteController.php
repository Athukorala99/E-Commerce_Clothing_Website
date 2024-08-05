<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Submission\SubmissionService;

class SubmissionNoteController extends Controller
{
    public function get(SubmissionService $submissionService, $submissionId)
    {
        try {
            return $this->sendSuccess(
                $submissionService->getNotes($submissionId, $this->request->all())
            );
        } catch (Exception $exception) {
            return $this->sendError([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function store(SubmissionService $submissionService, $submissionId)
    {
        try {
            return $this->sendSuccess(
                $submissionService->storeNote($submissionId, $this->request->all())
            );
        } catch (Exception $exception) {
            return $this->sendError([
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
