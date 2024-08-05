<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\App\Services\Integrations\FormIntegrationService;

class FormIntegrationController extends Controller
{
    public function index(FormIntegrationService $integrationService)
    {
        try {
            $formId = (int) $this->request->get('form_id');
            return $this->sendSuccess(
                $integrationService->get($formId)
            );
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function find(FormIntegrationService $integrationService)
    {
        try {
            $integration = $integrationService->find($this->request->all());
            return $this->sendSuccess($integration);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function update(FormIntegrationService $integrationService)
    {
        try {
            $integration = $integrationService->update($this->request->all());
            return $this->sendSuccess($integration);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage()
            ], 422);
        }
    }
    
    public function delete(FormIntegrationService $integrationService)
    {
        try {
            $id = $this->request->get('integration_id');
            $integrationService->delete($id);
            return $this->sendSuccess([
                'message' => __('Successfully deleted the Integration.', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function integrationListComponent()
    {
        try {
            $integrationName = $this->request->get('integration_name');
            $formId = intval($this->request->get('form_id'));
            $listId = $this->request->get('list_id');
            $merge_fields = false;
            $merge_fields = apply_filters_deprecated(
                'fluentform_get_integration_merge_fields_' . $integrationName,
                [
                    $merge_fields,
                    $listId,
                    $formId
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/get_integration_merge_fields_' . $integrationName,
                'Use fluentform/get_integration_merge_fields_' . $integrationName . ' instead of fluentform_get_integration_merge_fields_' . $integrationName
            );

            $merge_fields = apply_filters('fluentform/get_integration_merge_fields_' . $integrationName, $merge_fields, $listId, $formId);
            
            return $this->sendSuccess([
                'merge_fields' => $merge_fields,
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
}
