<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Settings\Customizer;
use FluentForm\App\Services\Settings\SettingsService;
use FluentForm\Framework\Validator\ValidationException;
use FluentForm\App\Services\Submission\SubmissionService;

class FormSettingsController extends Controller
{
    public function index(SettingsService $settingsService)
    {
        $result = $settingsService->get($this->request->all());

        return $this->sendSuccess($result);
    }

    public function general(SettingsService $settingsService, $formId)
    {
        $result = $settingsService->general($formId);

        return $this->sendSuccess($result);
    }

    public function saveGeneral(SettingsService $settingsService)
    {
        try {
            $settingsService->saveGeneral($this->request->all());

            return $this->sendSuccess([
                'message' => __('Settings has been saved.', 'fluentform'),
            ]);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }

    public function store(SettingsService $settingsService)
    {
        try {
            [$settingsId, $settings] = $settingsService->store($this->request->all());

            return $this->sendSuccess([
                'message'  => __('Settings has been saved.', 'fluentform'),
                'id'       => $settingsId,
                'settings' => $settings,
            ]);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }

    public function remove(SettingsService $settingsService)
    {
        $settingsService->remove($this->request->all());

        return $this->sendSuccess([]);
    }

    public function customizer(Customizer $customizer, $id)
    {
        return $this->sendSuccess($customizer->get($id));
    }

    public function storeCustomizer(Customizer $customizer, $id)
    {
        try {
            $customizer->store($this->request->all());

            return $this->sendSuccess([
                'message' => __('Custom CSS & JS successfully saved.', 'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function storeEntryColumns(SubmissionService $submissionService, $id)
    {
        try {
            $submissionService->storeColumnSettings($this->request->all());

            return $this->sendSuccess([
                'message' => __('The column display order has been saved.', 'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function conversationalDesign(SettingsService $settingsService, $formId)
    {
        try {
            return $this->sendSuccess($settingsService->conversationalDesign($formId));
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }

    public function storeConversationalDesign(SettingsService $settingsService, $formId)
    {
        try {
            return $this->sendSuccess($settingsService->storeConversationalDesign($this->request->all(), $formId));
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 423);
        }
    }
}
