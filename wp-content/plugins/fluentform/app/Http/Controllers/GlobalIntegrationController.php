<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\App\Services\Integrations\GlobalIntegrationService;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;
use Exception;

class GlobalIntegrationController extends Controller
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;
    
  
    public function index(GlobalIntegrationService $globalIntegrationService)
    {
        try {
            $returnData = $globalIntegrationService->get($this->request->all());
            if (Arr::isTrue($returnData, 'status')) {
                return $this->sendSuccess($returnData);
            }
            return $this->sendError($returnData);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function update()
    {
        try {
            $settingsKey = sanitize_text_field($this->request->get('settings_key'));
            $integration = wp_unslash($this->request->get('integration'));

            do_action_deprecated(
                'fluentform_save_global_integration_settings_' . $settingsKey,
                [
                    $integration
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/save_global_integration_settings_' . $settingsKey,
                'Use fluentform/save_global_integration_settings_' . $settingsKey . ' instead of fluentform_save_global_integration_settings_' . $settingsKey
            );

            do_action('fluentform/save_global_integration_settings_' . $settingsKey, $integration);
            
            // Someone should catch that above action and send response
            return $this->sendError([
                'message' => __('Sorry, no Integration found. Please make sure that latest version of Fluent Forms pro installed',
                    'fluentform'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateModuleStatus(GlobalIntegrationService $globalIntegrationService)
    {
        try {
            $globalIntegrationService->updateModuleStatus($this->request->get());
            return $this->sendSuccess([
                'message' => __('Status successfully updated', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    
}
