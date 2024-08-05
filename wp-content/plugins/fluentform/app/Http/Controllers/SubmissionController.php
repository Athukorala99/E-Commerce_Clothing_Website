<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Models\Submission;
use FluentForm\App\Services\Submission\SubmissionService;

class SubmissionController extends Controller
{
    public function index(SubmissionService $submissionService)
    {
        try {
            return $this->sendSuccess(
                $submissionService->get($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function find(SubmissionService $submissionService, $submissionId)
    {
        try {
            return $this->sendSuccess(
                $submissionService->find($submissionId)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function resources(SubmissionService $submissionService)
    {
        try {
            return $this->sendSuccess(
                $submissionService->resources($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateStatus(SubmissionService $submissionService)
    {
        try {
            $status = $submissionService->updateStatus($this->request->all());

            return $this->sendSuccess([
                'message' => __('The submission has been marked as ' . $status, 'fluentform'),
                'status'  => $status,
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toggleIsFavorite(SubmissionService $submissionService)
    {
        try {
            [$message, $isFavourite] = $submissionService->toggleIsFavorite(
                $this->request->get('entry_id')
            );

            return $this->sendSuccess([
                'message'      => $message,
                'is_favourite' => $isFavourite,
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function handleBulkActions(SubmissionService $submissionService)
    {
        try {
            $message = $submissionService->handleBulkActions($this->request->all());

            return $this->sendSuccess(['message' => $message]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    public function remove(Submission $submission, $submissionId)
    {
        try {
            $submission::remove([$submissionId]);
            return $this->sendSuccess([
                'message' => __('Selected submission successfully deleted Permanently', 'fluentform'),
            ]);
    
        } catch (Exception $e){
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get user list for submission page
     * @return \WP_REST_Response
     */
    public function submissionUsers()
    {
        $search = sanitize_text_field($this->request->get('search'));
        $users = get_users([
            'search' => "*{$search}*",
            'number' => 50,
        ]);
    
        $formattedUsers = [];
        foreach ($users as $user) {
            $formattedUsers[] = [
                'ID'    => $user->ID,
                'label' => $user->display_name . ' - ' . $user->user_email,
            ];
        }
    
        return $this->sendSuccess([
            'users' => $formattedUsers,
        ]);
    }

    /**
     * Update User of a submission
     * @param SubmissionService $submissionService
     * @return \WP_REST_Response
     */
    public function updateSubmissionUser(SubmissionService $submissionService)
    {
        try {
            $userId = intval($this->request->get('user_id'));
            $submissionId = intval($this->request->get('submission_id'));
            $response = $submissionService->updateSubmissionUser($userId, $submissionId);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get All Submissions
     * @param Submission $submission
     * @return \WP_REST_Response
     */
    public function all(Submission $submission)
    {
        try {
            return $this->sendSuccess(
                $submission->allSubmissions($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ]);
        }
    }
}
