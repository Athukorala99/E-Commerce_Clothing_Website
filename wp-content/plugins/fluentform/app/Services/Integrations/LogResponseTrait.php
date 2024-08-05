<?php

namespace FluentForm\App\Services\Integrations;


/**
 * @deprecated deprecated use FluentForm\App\Http\Controllers\GlobalIntegrationController;
 */


trait LogResponseTrait
{
    protected function logResponse($response, $feed, $data, $form, $entryId, $status)
    {
        if (! $response) {
            return;
        }

        $prefix = 'fluentform/after_submission_api_response_';
        $action = $prefix . $status;

        do_action_deprecated(
            'fluentform_after_submission_api_response_'. $status,
            [
                $form,
                $entryId,
                $data,
                $feed,
                $response,
                $this->getApiResponseMessage($response, $status)
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            $action,
            'Use ' . $action . ' instead of fluentform_after_submission_api_response_'. $status
        );

        do_action(
            $action,
            $form,
            $entryId,
            $data,
            $feed,
            $response,
            $this->getApiResponseMessage($response, $status)
        );
    }

    protected function getApiResponseMessage($response, $status)
    {
        if (is_array($response) && isset($response['message'])) {
            return $response['message'];
        }

        return $status;
    }
}
