<?php

namespace FluentForm\App\Modules\Component;

trait ComponentInitTrait
{
    /**
     * Initialize the component and register the
     * component by registering actions/filters.
     */
    public function _init()
    {
        // Validate certain important required properties
        $this->_fluentFormValidateComponent();

        // Hook to add component in the editor's components array
        add_filter('fluentform/editor_components', [$this, '_fluentEditorComponenstCallback']);

        // Hook to add search keywords for the component
        add_filter('fluentform/editor_element_search_tags', [$this, '_fluentEditorElementSearchTagsCallback']);

        // Hook for component's editor items/options placement settings
        add_filter('fluentform/editor_element_settings_placement', [$this, '_fluentEditorElementSettingsPlacementCallback']);

        // Hook for component's customization settings
        add_filter('fluentform/editor_element_customization_settings', [$this, '_fluentEditorElementCustomizationSettingsCallback']);

        // Hook for response data preperation on form submission
        add_filter('fluentform/insert_response_data', [$this, '_fluentformInsertResponseDataCallback'], 10, 3);

        // Hook to add component type in fluentform field types to be available in FormFieldParser.
        add_filter('fluentform/form_input_types', [$this, '_fluentformFormInputTypesCallback']);

        // Component render/compile hook (for form)
        add_action('fluentform/render_item_' . $this->element(), [$this, '_elementRenderHookCallback'], 10, 2);

        // Component's used element response transformation hook (for entries)
        add_filter('fluentform/response_render_' . $this->element(), [$this, '_elementEntryFormatCallback'], 10, 3);
    }

    /**
     * Validate certain required properties
     *
     * @return void
     */
    private function _fluentFormValidateComponent()
    {
        $name = $this->name();
        if (! $name || ! is_string($name)) {
            wp_die('The name must be a valid string.');
        }

        $label = $this->label();
        if (! $label || ! is_string($label)) {
            wp_die('The label must be a valid string.');
        }

        $element = $this->element();
        if (! $element || ! is_string($element)) {
            $elements = 'text_input, text_email, textarea';
            wp_die("The element must be one of the available elements, i.e: $elements e.t.c.");
        }

        $template = $this->template();
        if (! $template || ! is_string($template)) {
            $templates = 'inputText, selectCountry. addressFields';
            wp_die("The template must be one of the available templates, i.e: $templates e.t.c.");
        }

        $group = $this->group();
        $groups = ['general', 'advanced', 'container'];
        if (! $group || ! in_array($group, $groups)) {
            wp_die('Invalid group, available groups: ' . implode(', ', $groups) . '.');
        }
    }

    /**
     * Add the component in fluentform editor's components array.
     *
     * @param array $components
     *
     * @return array $components
     */
    public function _fluentEditorComponenstCallback($components)
    {
        $name = $this->name();
        $label = $this->label();
        $group = $this->group();
        $index = $this->index();
        $element = $this->element();
        $template = $this->template();
        $iconClass = $this->elementIconClass();
        $validationRules = $this->validationRules();

        $settings = $this->settings([
            'label'              => $label,
            'container_class'    => '',
            'label_placement'    => '',
            'help_message'       => '',
            'admin_field_label'  => $label,
            'conditional_logics' => [],
            'validation_rules'   => $validationRules,
        ]);

        $attributes = $this->attributes([
            'id'          => '',
            'name'        => $name,
            'class'       => '',
            'value'       => '',
            'placeholder' => '',
        ]);

        $editorOptions = $this->options([
            'title'      => $label,
            'template'   => $template,
            'icon_class' => $iconClass,
        ]);

        if (is_null($index)) {
            $index = count($components[$group]) + 1;
        }

        $components[$group][] = [
            'index'          => $index,
            'element'        => $element,
            'settings'       => $settings,
            'attributes'     => $attributes,
            'editor_options' => $editorOptions,
        ];

        return $components;
    }

    /**
     * Add the keywords for the component to search in the editor.
     *
     * @param array $keywords
     *
     * @return array $keywords
     */
    public function _fluentEditorElementSearchTagsCallback($keywords)
    {
        $newKeywords = $this->searchBy();

        if (! $newKeywords) {
            return $keywords;
        }

        $existingKeywords = [];
        $element = $this->element();
        if (isset($keywords[$element])) {
            $existingKeywords = $keywords[$element];
        }

        $keywords[$element] = array_merge(
            $existingKeywords,
            $newKeywords
        );

        return $keywords;
    }

    /**
     * Configure placements of input customization options in editor.
     *
     * @param array $placemenSettings
     *
     * @return array $placemenSettings
     */
    public function _fluentEditorElementSettingsPlacementCallback($placementSettings)
    {
        $default = [
            'general' => [
                'label',
                'label_placement',
                'admin_field_label',
                'placeholder',
                'value',
                'validation_rules',
            ],
            'advanced' => [
                'name',
                'class',
                'help_message',
                'container_class',
                'conditional_logics',
            ],
        ];

        $placementSettings[$this->element()] = array_merge_recursive(
            $this->placementSettings($default),
            $placementSettings
        );

        return $placementSettings;
    }

    /**
     * Configure input customization options/items in the editor.
     *
     * @param array $customizationSettings
     *
     * @return array $customizationSettings
     */
    public function _fluentEditorElementCustomizationSettingsCallback($customizationSettings)
    {
        return $this->customizationSettings($customizationSettings);
    }

    /**
     * Prepare the submission data for the element on Form Submission.
     *
     * @param array $formData
     * @param int   $formId
     * @param array $inputConfigs
     *
     * @return array $formData
     */
    public function _fluentformInsertResponseDataCallback($formData, $formId, $inputConfigs)
    {
        return $this->onSubmit($formData, $formId, $inputConfigs);
    }

    /**
     * Add the component type in fluentform field
     * types to be available in FormFieldParser.
     *
     * @param array $types
     *
     * @return array $types
     */
    public function _fluentformFormInputTypesCallback($types)
    {
        return $this->addType($types);
    }

    /**
     * Render the component.
     *
     * @param array    $data
     * @param \stdClass $form
     *
     * @return void
     */
    public function _elementRenderHookCallback($item, $form)
    {
        $this->render($item, $form);
    }

    /**
     * Element's entry value transformation.
     *
     * @param mixed  $value
     * @param string $field
     * @param int    $formId
     *
     * @return mixed $value
     */
    public function _elementEntryFormatCallback($value, $field, $formId)
    {
        return $this->formatEntryValue($value, $field, $formId);
    }
}
