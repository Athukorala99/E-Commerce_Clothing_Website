<?php

namespace FluentForm\App\Services\FormBuilder;

use FluentForm\App\Services\FormBuilder\Components\BaseComponent;

abstract class BaseFieldManager extends BaseComponent
{
    protected $key = '';
    protected $title = '';
    protected $tags = [];
    protected $position = 'advanced';

    public function __construct($key, $title, $tags = [], $position = 'advanced')
    {
        $this->key = $key;
        $this->position = $position;
        $this->title = $title;
        $this->tags = $tags;
        $this->register();
    }

    public function register()
    {
        add_filter('fluentform/editor_components', [$this, 'pushComponent']);
        add_filter('fluentform/editor_element_settings_placement', [$this, 'pushEditorElementPositions']);
        add_filter('fluentform/editor_element_search_tags', [$this, 'pushTags'], 10, 2);
        add_action('fluentform/render_item_' . $this->key, [$this, 'render'], 10, 2);
        /*
         * This is internal use.
         * Push field type to the fluentform field types to be available in FormFieldParser.
         */
        add_filter('fluentform/form_input_types', [$this, 'pushFormInputType']);

        add_filter('fluentform/editor_element_customization_settings', function ($settings) {
            if ($customSettings = $this->getEditorCustomizationSettings()) {
                $settings = array_merge($settings, $customSettings);
            }

            return $settings;
        });

        add_filter('fluentform/supported_conditional_fields', [$this, 'pushConditionalSupport']);
    }

    public function pushConditionalSupport($conditonalItems)
    {
        $conditonalItems[] = $this->key;
        return $conditonalItems;
    }

    public function pushFormInputType($types)
    {
        $types[] = $this->key;
        return $types;
    }

    public function pushComponent($components)
    {
        $component = $this->getComponent();

        if ($component) {
            $components[$this->position][] = $component;
        }

        return $components;
    }

    public function pushEditorElementPositions($placement_settings)
    {
        $placement_settings[$this->key] = [
            'general'        => $this->getGeneralEditorElements(),
            'advanced'       => $this->getAdvancedEditorElements(),
            'generalExtras'  => $this->generalEditorElement(),
            'advancedExtras' => $this->advancedEditorElement(),
        ];

        return $placement_settings;
    }

    public function generalEditorElement()
    {
        return [];
    }

    public function advancedEditorElement()
    {
        return [];
    }

    public function getGeneralEditorElements()
    {
        return [
            'label',
            'admin_field_label',
            'placeholder',
            'label_placement',
            'validation_rules',
        ];
    }

    public function getAdvancedEditorElements()
    {
        return [
            'name',
            'help_message',
            'container_class',
            'class',
            'conditional_logics',
        ];
    }

    public function getEditorCustomizationSettings()
    {
        return [];
    }

    public function pushTags($tags, $form)
    {
        if ($this->tags) {
            $tags[$this->key] = $this->tags;
        }
        return $tags;
    }

    abstract public function getComponent();

    abstract public function render($element, $form);
}
