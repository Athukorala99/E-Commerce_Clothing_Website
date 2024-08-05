<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

class HoneyPot
{
    private $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    public function renderHoneyPot($form)
    {
        if (!$this->isEnabled($form->id)) {
            return;
        }
        ?>
        <span style="display: none !important;"><input type="checkbox"
                name="<?php echo esc_attr($this->getFieldName($form->id)); ?>"
                value="1" style="display:none !important;" tabindex="-1" aria-hidden="true"></span>
        <?php
    }

    public function verify($insertData, $requestData, $formId)
    {
        if (!$this->isEnabled($formId)) {
            return;
        }

        // Now verify
        if (ArrayHelper::get($requestData, $this->getFieldName($formId))) {
            // It's a bot! Block him
            wp_send_json(
                [
                    'errors' => 'Sorry! You can not submit this form at this moment!',
                ],
                422
            );
        }

        return;
    }

    public function isEnabled($formId = false)
    {
        $option = get_option('_fluentform_global_form_settings');
        $status = 'yes' == ArrayHelper::get($option, 'misc.honeypotStatus');
    
        $status = apply_filters_deprecated(
            'fluentform_honeypot_status',
            [
                $status,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/honeypot_status',
            'Use fluentform/honeypot_status instead of fluentform_honeypot_status.'
        );

        return apply_filters('fluentform/honeypot_status', $status, $formId);
    }

    private function getFieldName($formId)
    {
        $honeyPotName = 'item__' . $formId . '__fluent_checkme_';
        $honeyPotName =  apply_filters_deprecated(
            'fluentform_honeypot_name',
            [
                $honeyPotName,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/honeypot_name',
            'Use fluentform/honeypot_name instead of fluentform_honeypot_name.'
        );

        return apply_filters('fluentform/honeypot_name', $honeyPotName, $formId);
    }
}
