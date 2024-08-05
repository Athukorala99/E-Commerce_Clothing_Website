<?php

namespace FluentForm\App\Modules\Form\Settings;

use FluentForm\Framework\Foundation\Application;

class ExtraSettings
{
    /**
     * Request Object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;

    /**
     * Query Builder Handler Object
     *
     * @var \WpFluent\QueryBuilder\QueryBuilderHandler
     */
    protected $form_model;

    /**
     * Construct the object
     *
     * @throws \Exception
     */
    public function __construct(Application $application)
    {
        $this->request = $application->request;
        $this->form_model = wpFluent()->table('fluentform_forms');
    }

    /**
     * Get extra settig navigations
     */
    public function getExtraSettingNavs()
    {
        $formId = $this->request->get('form_id');

        $extraSettings = [
            [
                'name'   => 'web_hooks',
                'label'  => __('Web Hooks', 'fluentform'),
                'action' => 'fluentform_get_settings_page',
                'addon'  => 'Fluent Web Hooks',
            ],
            [
                'name'   => 'trello',
                'label'  => __('Trello', 'fluentform'),
                'action' => 'fluentform_get_settings_page',
                'addon'  => 'Trello',
            ],
        ];

        wp_send_json_success(['setting_navs' => $extraSettings], 200);
    }

    /**
     * Get extra settigs component
     */
    public function getExtraSettingsComponent()
    {
        $formId = $this->request->get('form_id');
        $module = $this->request->get('module');

        $component = [
            'name'     => 'fluentform_settings_' . $module,
            'props'    => ['form_id'],
            'template' => '<p>Setting Not Found</p>',
        ];
    
        $component = apply_filters_deprecated(
            'fluentform_settings_module_' . $module,
            [
                $module,
                $component,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/settings_module_' . $module,
            'Use fluentform/settings_module_' . $module . ' instead of fluentform_settings_module_' . $module
        );

        $component = apply_filters('fluentform/settings_module_' . $module, $component, $formId);

        wp_send_json_success([
            'component' => $component,
            'name'      => 'fluentform_settings_' . $module,
            'js_url'    => site_url() . '/test.js',
        ]);
    }

    /**
     * Get trello settigs
     */
    public function getTrelloSettingsComponent($component, $formId)
    {
        return $component;
    }
}
