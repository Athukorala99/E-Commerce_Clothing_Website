<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\Framework\Helpers\ArrayHelper;

class TabularGrid extends BaseComponent
{
    /**
     * Compile and echo the html element
     *
     * @param array     $data [element data]
     * @param \stdClass $form [Form Object]
     *
     * @return void
     */
    public function compile($data, $form)
    {
        $elementName = $data['element'];
        $data = apply_filters_deprecated(
            'fluentform_rendering_field_data_' . $elementName,
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_data_' . $elementName,
            'Use fluentform/rendering_field_data_' . $elementName . ' instead of fluentform_rendering_field_data_' . $elementName
        );
        $data = apply_filters('fluentform/rendering_field_data_' . $elementName, $data, $form);

        $checked = $data['settings']['selected_grids'];
        $columnLabels = $data['settings']['grid_columns'];

        $fieldType = $data['settings']['tabular_field_type'];
        $columnHeaders = implode('</th><th>', array_values($columnLabels));
        $elementHelpMessage = $this->getElementHelpMessage($data, $form);
        $elementLabel = $this->setClasses($data)->buildElementLabel($data, $form);

        $elMarkup = "<table class='ff-table ff-checkable-grids ff_flexible_table' role='table'><thead><tr><th></th><th>" . fluentform_sanitize_html($columnHeaders) . '</th></tr></thead><tbody>';

        $tabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex();
        foreach ($this->makeTabularData($data) as $index => $row) {
            $elMarkup .= '<tr role="row"">';
            $elMarkup .= "<td class='ff_grid_header' role='cell'>" . fluentform_sanitize_html($row['label']) . '</td>';
            $isRowChecked = in_array($row['name'], $checked) ? 'checked' : '';
            foreach ($row['columns'] as $column) {
                $name = $data['attributes']['name'] . '[' . $row['name'] . ']';
                $name = 'checkbox' == $fieldType ? ($name . '[]') : $name;
                $isColChecked = in_array($column['name'], $checked) ? 'checked' : '';
                $isChecked = $isRowChecked ? $isRowChecked : $isColChecked;

                $atts = [
                    'name'  => $name,
                    'type'  => $fieldType,
                    'value' => $column['name'],
                ];
                if ($tabIndex) {
                    $atts['tabindex'] = $tabIndex;
                }
                $attributes = $this->buildAttributes($atts, $form);

                $ariaRequired = 'false';
                if (ArrayHelper::get($data, 'settings.validation_rules.required.value')) {
                    $ariaRequired = 'true';
                }

                $input = '<input aria-label="'. $row['name'] .'-'. $column['label'] . '" ' . $attributes . " {$isChecked} aria-invalid='false' aria-required={$ariaRequired}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attributes is escaped before being passed in.
                $elMarkup .= "<td data-label='" . fluentform_sanitize_html($column['label']) . "'>{$input}</td>";
            }
            $elMarkup .= '</tr>';
        }

        $elMarkup .= '</tbody></table>';

        $elMarkup = "<div class='ff-el-input--content'>{$elMarkup}" . fluentform_sanitize_html($elementHelpMessage) . '</div>';

        $html = sprintf(
            "<div data-type='%s' data-name='%s' class='%s'>{$elementLabel}{$elMarkup}</div>",
            $data['attributes']['data-type'],
            $data['attributes']['name'],
            $data['attributes']['class']
        ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $elementLabel is escaped before being passed in.
    
        $html = apply_filters_deprecated(
            'fluentform_rendering_field_html_' . $elementName,
            [
                $html,
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/rendering_field_html_' . $elementName,
            'Use fluentform/rendering_field_html_' . $elementName . ' instead of fluentform_rendering_field_html_' . $elementName
        );
        $this->printContent('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
    }

    public function makeTabularData($data)
    {
        $table = [];
        $rows = $data['settings']['grid_rows'];
        $columns = $data['settings']['grid_columns'];

        foreach ($rows as $rowKey => $rowValue) {
            $table[$rowKey] = [
                'name'    => $rowKey,
                'label'   => $rowValue,
                'columns' => [],
            ];

            foreach ($columns as $columnKey => $columnValue) {
                $table[$rowKey]['columns'][] = [
                    'name'  => $columnKey,
                    'label' => $columnValue,
                ];
            }
        }

        return $table;
    }

    protected function getElementHelpMessage($data, $form)
    {
        $elementHelpMessage = '';
        if ('under_input' == $form->settings['layout']['helpMessagePlacement']) {
            $elementHelpMessage = $this->getInputHelpMessage($data);
        }

        return $elementHelpMessage;
    }

    protected function setClasses(&$data)
    {
        if (! isset($data['attributes']['class'])) {
            $data['attributes']['class'] = '';
        }

        $placement = $data['settings']['label_placement'];
        $placementClass = $placement ? 'ff-el-form-' . $placement : '';
        $hasConditions = $this->hasConditions($data) ? ' has-conditions' : '';
        $defaultContainerClass = $this->getDefaultContainerClass();
        $containerClass = $data['settings']['container_class'];
        $data['attributes']['class'] .= trim(implode(' ', array_map('trim', [
            $defaultContainerClass, $containerClass, $placementClass, $hasConditions,
        ])));

        return $this;
    }
}
