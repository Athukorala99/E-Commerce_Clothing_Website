<?php

namespace FluentForm\App\Services\FormBuilder;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class FormBuilder
{
    /**
     * The Applivcation instance
     *
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app = null;

    protected $form = null;

    /**
     * Conditional logic for elements
     *
     * @var array
     */
    public $conditions = [];

    /**
     * Validation rules for elements
     *
     * @var array
     */
    public $validationRules = [];

    public $tabIndex = 1;

    public $fieldLists = [];

    public $containerCounter;

    /**
     * Construct the form builder instance
     *
     * @param \FluentForm\Framework\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->containerCounter = 1;
    }

    /**
     * Render the form
     *
     * @param \StdClass $form [Form entry from database]
     *
     * @return mixed
     */
    public function build($form, $extraCssClass = '', $instanceCssClass = '', $atts = [])
    {
        $this->form = $form;
        $hasStepWrapper = isset($form->fields['stepsWrapper']) && $form->fields['stepsWrapper'];

        $labelPlacement = $form->settings['layout']['labelPlacement'];

        $formClass = "frm-fluent-form fluent_form_{$form->id} ff-el-form-{$labelPlacement}";

        if ($extraCssClass) {
            $formClass .= " {$extraCssClass}";
        }

        if ($hasStepWrapper) {
            $formClass .= ' ff-form-has-steps';
        }

        if ($extraFormClass = Helper::formExtraCssClass($form)) {
            $formClass .= ' ' . $extraFormClass;
        }

        $themeStyle = ArrayHelper::get($atts, 'theme');

        if (!$themeStyle) {
            $selectedStyle = Helper::getFormMeta($form->id, '_ff_selected_style');

            $selectedStyle = $selectedStyle ? $selectedStyle : 'ffs_default';

            $themeStyle = $selectedStyle;

            $atts['theme'] = $selectedStyle;
        }

        $form->theme = $themeStyle;

        $formBody = $this->buildFormBody($form);

        if (strpos($formBody, '{dynamic.')) {
            $formClass .= ' ff_has_dynamic_smartcode';
            wp_enqueue_script('fluentform-advanced');
        }
    
        $formClass = apply_filters_deprecated(
            'fluentform_form_class',
            [
                $formClass,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_class',
            'Use fluentform/form_class instead of fluentform_form_class.'
        );

        $formClass = apply_filters('fluentform/form_class', $formClass, $form);

        if ($form->has_payment) {
            $formClass .= ' fluentform_has_payment';
        }

        if ($themeStyle) {
            $formClass .= ' ' . $themeStyle;
        }

        $data = [
            'data-form_id'       => $form->id,
            'id'                 => 'fluentform_' . $form->id,
            'class'              => $formClass,
            'data-form_instance' => $instanceCssClass,
            'method'             => 'POST',
        ];
    
        $data = apply_filters_deprecated(
            'fluent_form_html_attributes',
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/html_attributes',
            'Use fluentform/html_attributes instead of fluent_form_html_attributes.'
        );

        $formAttributes = apply_filters('fluentform/html_attributes', $data, $form);

        $formAtts = $this->buildAttributes($formAttributes);
      

        $wrapperClasses = trim('fluentform ff-default fluentform_wrapper_' . $form->id . ' ' . ArrayHelper::get($atts, 'css_classes'));

        if ($themeStyle === 'ffs_inherit_theme') {
            $wrapperClasses = str_replace("ff-default", "ff-inherit-theme-style", $wrapperClasses);
        } else if ($themeStyle) {
            $wrapperClasses .= ' ' . $themeStyle . '_wrap';
        }

        $wrapperClasses = apply_filters('fluentform/form_wrapper_classes', $wrapperClasses, $form);
        ob_start();

        echo "<div class='" . esc_attr($wrapperClasses) . "'>";

        do_action_deprecated(
            'fluentform_before_form_render',
            [
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_form_render',
            'Use fluentform/before_form_render instead of fluentform_before_form_render.'
        );
        
        do_action('fluentform/before_form_render', $form, $atts);
        echo '<form ' . $formAtts . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $formAtts is escaped before being passed in.
    
        $isAccessible = apply_filters_deprecated(
            'fluentform_disable_accessibility_fieldset',
            [
                true,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/disable_accessibility_fieldset',
            'Use fluentform/disable_accessibility_fieldset instead of fluentform_disable_accessibility_fieldset.'
        );
        $isAccessible = apply_filters('fluentform/disable_accessibility_fieldset', true, $form);

        if ($isAccessible) {
            echo $this->fieldsetHtml($form);
        }
        
        /* This hook is deprecated & will be removed soon */
        do_action('fluentform_form_element_start', $form);
        
        do_action('fluentform/form_element_start', $form);

        echo "<input type='hidden' name='__fluent_form_embded_post_id' value='" . get_the_ID() . "' />";

        wp_nonce_field(
            'fluentform-submit-form',
            '_fluentform_' . $form->id . '_fluentformnonce',
            true,
            true
        );

        echo $formBody; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $formBody is escaped before being passed in.

        if ($isAccessible) {
            echo '</fieldset>';
        }

        echo "</form><div id='fluentform_" . (int) $form->id . "_errors' class='ff-errors-in-stack ";

        echo esc_attr($extraCssClass) . '_errors ' . esc_attr($instanceCssClass) . "_errors'></div></div>";

        do_action_deprecated(
            'fluentform_after_form_render',
            [
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_form_render',
            'Use fluentform/after_form_render instead of fluentform_after_form_render.'
        );
        do_action('fluentform/after_form_render', $form);

        return ob_get_clean();
    }

    /**
     * @param \stdClass $form
     *
     * @return string form body
     */
    public function buildFormBody($form)
    {
        $hasStepWrapper = isset($form->fields['stepsWrapper']) && $form->fields['stepsWrapper'];

        ob_start();

        $stepCounter = 1;

        foreach ($form->fields['fields'] as $item) {
            if ($hasStepWrapper && 'form_step' == $item['element']) {
                $stepCounter++;
            }

            $this->setUniqueIdentifier($item);
    
            $item = apply_filters_deprecated(
                'fluentform_before_render_item',
                [
                    $item,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/before_render_item',
                'Use fluentform/before_render_item instead of fluentform_before_render_item.'
            );
            $item = apply_filters('fluentform/before_render_item', $item, $form);

            do_action_deprecated(
                'fluentform_render_item_' . $item['element'],
                [
                    $item,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/render_item_' . $item['element'],
                'Use fluentform/render_item_' . $item['element'] . ' instead of fluentform_render_item_' . $item['element']
            );

            do_action('fluentform/render_item_' . $item['element'], $item, $form);

            $this->extractValidationRules($item);

            $this->extractConditionalLogic($item);
        }

        if ($hasStepWrapper) {
            do_action_deprecated(
                'fluentform_render_item_step_end',
                [
                    $form->fields['stepsWrapper']['stepEnd'],
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/render_item_step_end',
                'Use fluentform/render_item_step_end instead of fluentform_render_item_step_end.'
            );
            do_action('fluentform/render_item_step_end', $form->fields['stepsWrapper']['stepEnd'], $form);
        } else {
            do_action_deprecated(
                'fluentform_render_item_submit_button',
                [
                    $form->fields['submitButton'],
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/render_item_submit_button',
                'Use fluentform/render_item_submit_button instead of fluentform_render_item_submit_button.'
            );
            do_action('fluentform/render_item_submit_button', $form->fields['submitButton'], $form);
        }

        $content = ob_get_clean();

        if ($hasStepWrapper) {
            $startElement = $form->fields['stepsWrapper']['stepStart'];

            $steps = ArrayHelper::get($startElement, 'settings.step_titles');

            // check if $stepCounter == count()
            if ($stepCounter > count($steps)) {
                $fillCount = $stepCounter - count($steps);
                foreach (range(1, $fillCount) as $item) {
                    $steps[] = '';
                }
                $startElement['settings']['step_titles'] = $steps;
            }

            $this->setUniqueIdentifier($startElement);

            ob_start();

            do_action_deprecated(
                'fluentform_render_item_step_start',
                [
                    $startElement,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/render_item_step_start',
                'Use fluentform/render_item_step_start instead of fluentform_render_item_step_start.'
            );
            do_action('fluentform/render_item_step_start', $startElement, $form);

            $stepStatrt = ob_get_clean();

            $content = $stepStatrt . $content;
        }

        return $content;
    }

    /**
     * Set unique name/data-name for an element
     *
     * @param array &$item
     *
     * @return void
     */
    protected function setUniqueIdentifier(&$item)
    {
        if (isset($item['columns'])) {
            $item['attributes']['data-name'] = 'ff_cn_id_' . $this->containerCounter;
            $this->containerCounter++;
            foreach ($item['columns'] as &$column) {
                foreach ($column['fields'] as &$field) {
                    $this->setUniqueIdentifier($field);
                }
            }
        } else {
            if (! isset($item['attributes']['name'])) {
                if ($this->form) {
                    if (empty($this->form->attr_name_index)) {
                        $this->form->attr_name_index = 1;
                    } else {
                        $this->form->attr_name_index += 1;
                    }
                    $uniqueId = $this->form->id . '_' . $this->form->attr_name_index;
                } else {
                    $uniqueId = uniqid(rand(), true);
                }

                $item['attributes']['name'] = $item['element'] . '-' . $uniqueId;
            }
            $item['attributes']['data-name'] = $item['attributes']['name'];
            $this->fieldLists[] = $item['element'];
        }
    }

    /**
     * Recursively extract validation rules from a given element
     *
     * @param array $item
     *
     * @return void
     */
    protected function extractValidationRules($item)
    {
        if (isset($item['columns'])) {
            foreach ($item['columns'] as $column) {
                foreach ($column['fields'] as $field) {
                    $this->extractValidationRules($field);
                }
            }
        } elseif (isset($item['fields'])) {
            $rootName = $item['attributes']['name'];
            foreach ($item['fields'] as $key => $innerItem) {
                if ('address' == $item['element'] || 'input_name' == $item['element']) {
                    $itemName = $innerItem['attributes']['name'];
                    $innerItem['attributes']['name'] = $rootName . '[' . $itemName . ']';
                } else {
                    if ('input_repeat' == $item['element'] || 'repeater_field' == $item['element']) {
                        if (empty($innerItem['settings']['validation_rules']['email'])) {
                            unset($innerItem['settings']['validation_rules']['email']);
                        }
                        $innerItem['attributes']['name'] = $rootName . '[' . $key . ']';
                    } else {
                        $innerItem['attributes']['name'] = $rootName;
                    }
                }
                $this->extractValidationRule($innerItem);
            }
        } elseif ('tabular_grid' == $item['element']) {
            $gridName = $item['attributes']['name'];
            $gridRows = $item['settings']['grid_rows'];
            $gridType = $item['settings']['tabular_field_type'];
            foreach ($gridRows as $rowKey => $rowValue) {
                if ('radio' == $gridType) {
                    $item['attributes']['name'] = $gridName . '[' . $rowKey . ']';
                    $this->extractValidationRule($item);
                } else {
                    $item['attributes']['name'] = $gridName . '[' . $rowKey . ']';
                    $this->extractValidationRule($item);
                }
            }
        } elseif ('chained_select' == $item['element']) {
            $chainedSelectName = $item['attributes']['name'];
            foreach ($item['settings']['data_source']['headers'] as $select) {
                $item['attributes']['name'] = $chainedSelectName . '[' . $select . ']';
                $this->extractValidationRule($item);
            }
        } else {
            $this->extractValidationRule($item);
        }
    }

    /**
     * Extract validation rules from a given element
     *
     * @param array $item
     *
     * @return void
     */
    protected function extractValidationRule($item)
    {
        if (isset($item['settings']['validation_rules'])) {
            $rules = $item['settings']['validation_rules'];
            foreach ($rules as $ruleName => $rule) {
                if (isset($rule['message'])) {
                    if (isset($rule['global']) && $rule['global']) {
                        $rule['message'] = apply_filters('fluentform/get_global_message_' . $ruleName, $rule['message']);
                    }
                    $rules[$ruleName]['message'] = apply_filters_deprecated(
                        'fluentform_validation_message_' . $ruleName,
                        [
                            $rule['message'],
                            $item
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/validation_message_' . $ruleName,
                        'Use fluentform/validation_message_' . $ruleName . ' instead of fluentform_validation_message_' . $ruleName
                    );
                    $rules[$ruleName]['message'] = apply_filters('fluentform/validation_message_' . $ruleName, $rule['message'], $item);

                    $rules[$ruleName]['message'] = apply_filters_deprecated(
                        'fluentform_validation_message_' . $item['element'] . '_' . $ruleName,
                        [
                            $rules[$ruleName]['message'],
                            $item
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/validation_message_' . $item['element'] . '_' . $ruleName,
                        'Use fluentform/validation_message_' . $item['element'] . '_' . $ruleName . ' instead of fluentform_validation_message_' . $item['element'] . '_' . $ruleName
                    );
                    $rules[$ruleName]['message'] = apply_filters('fluentform/validation_message_' . $item['element'] . '_' . $ruleName, $rules[$ruleName]['message'], $item);
                }
            }
    
            $rules= apply_filters_deprecated(
                'fluentform_item_rules_' . $item['element'],
                [
                    $rules,
                    $item
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/item_rules_' . $item['element'],
                'Use fluentform/item_rules_' . $item['element'] . ' instead of fluentform_item_rules_' . $item['element']
            );

            $rules = apply_filters('fluentform/item_rules_' . $item['element'], $rules, $item);
            $this->validationRules[$item['attributes']['name']] = $rules;
        }
    }

    /**
     * Extract conditional logic from a given element
     *
     * @param array $item
     *
     * @return void
     */
    protected function extractConditionalLogic($item)
    {
        // If container like element, then recurse
        if (isset($item['columns'])) {
            $containerConditions = false;
            if (isset($item['settings']['conditional_logics'])) {
                $conditionals = $item['settings']['conditional_logics'];
                if (isset($conditionals['status'])) {
                    if ($conditionals['status'] && $conditionals['conditions']) {
                        $containerConditions = $item['settings']['conditional_logics'];
                        $this->conditions[$item['attributes']['data-name']] = $containerConditions;
                    }
                }
            }

            foreach ($item['columns'] as $column) {
                foreach ($column['fields'] as $field) {
                    if ($containerConditions) {
                        $field['container_conditions'] = $containerConditions;
                    }
                    $this->extractConditionalLogic($field);
                }
            }
        } elseif (isset($item['settings']['conditional_logics'])) {
            $conditionals = $item['settings']['conditional_logics'];
            if (isset($conditionals['status'])) {
                if ($conditionals['status'] && $conditionals['conditions']) {
                    $this->conditions[$item['attributes']['data-name']] = $item['settings']['conditional_logics'];
                }
            }
            if (isset($item['container_conditions'])) {
                if (! isset($this->conditions[$item['attributes']['data-name']])) {
                    $this->conditions[$item['attributes']['data-name']] = [
                        'conditions' => [],
                        'status'     => false,
                        'type'       => 'any',
                    ];
                }
                $this->conditions[$item['attributes']['data-name']]['container_condition'] = $item['container_conditions'];
            }
        }
    }

    /**
     * Build attributes for any html element
     *
     * @param array $attributes
     *
     * @return string [Compiled key='value' attributes]
     */
    protected function buildAttributes($attributes, $form = null)
    {
        $atts = '';
        foreach ($attributes as $key => $value) {
            if ($value || 0 === $value || '0' === $value) {
                $value = htmlspecialchars($value);
                $atts .= $key . '="' . $value . '" ';
            }
        }
        return $atts;
    }
    
    /**
     * Get hidden fieldset html
     *
     * @param $form
     *
     * @return string
     */
    private function fieldsetHtml($form)
    {
        return '<fieldset  style="border: none!important;margin: 0!important;padding: 0!important;background-color: transparent!important;box-shadow: none!important;outline: none!important; min-inline-size: 100%;">
                    <legend class="ff_screen_reader_title" style="display: block; margin: 0!important;padding: 0!important;height: 0!important;text-indent: -999999px;width: 0!important;overflow:hidden;">'
                            .$form->title.
                    '</legend>';
    }
}
