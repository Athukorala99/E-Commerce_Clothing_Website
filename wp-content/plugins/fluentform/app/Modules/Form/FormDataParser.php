<?php

namespace FluentForm\App\Modules\Form;

use FluentForm\Framework\Helpers\ArrayHelper;

class FormDataParser
{
    protected static $data = null;

    public static function parseFormEntries($entries, $form, $fields = null)
    {
        $fields = $fields ? $fields : FormFieldsParser::getEntryInputs($form);

        foreach ($entries as $entry) {
            static::parseFormEntry($entry, $form, $fields);
        }

        return $entries;
    }

    public static function parseFormEntry($entry, $form, $fields = null, $isHtml = false)
    {
        $fields = $fields ? $fields : FormFieldsParser::getEntryInputs($form);

        $entry->user_inputs = static::parseData(
            json_decode($entry->response),
            $fields,
            $form->id,
            $isHtml
        );

        return $entry;
    }

    public static function parseFormSubmission($submission, $form, $fields, $isHtml = false)
    {
        if (is_null(static::$data)) {
            static::$data = static::parseData(
                json_decode($submission->response),
                $fields,
                $form->id,
                $isHtml
            );
        }

        $submission->user_inputs = static::$data;

        return $submission;
    }

    public static function parseData($response, $fields, $formId, $isHtml = false)
    {
        $trans = [];
        foreach ($fields as $field_key => $field) {
            if (isset($response->{$field_key})) {
                $value = $response->{$field_key};
                
                $value = apply_filters_deprecated(
                    'fluentform_response_render_' . $field['element'],
                    [
                        $value,
                        $field,
                        $formId,
                        $isHtml
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/response_render_' . $field['element'],
                    'Use fluentform/response_render_' . $field['element'] . ' instead of fluentform_response_render_' . $field['element']
                );

                $value = apply_filters(
                    'fluentform/response_render_' . $field['element'],
                    $value,
                    $field,
                    $formId,
                    $isHtml
                );
                $trans[$field_key] = $value;
            } else {
                $trans[$field_key] = '';
            }
        }

        return $trans;
    }

    public static function formatValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return fluentImplodeRecursive(', ', array_filter(array_values((array) $value)));
        }

        return $value;
    }

    public static function formatFileValues($values, $isHtml, $form_id = null)
    {
        if (!$values) {
            return $values;
        }

        if (is_string($values)) {
            return $values;
        }

        if (!$isHtml) {
            return fluentImplodeRecursive(', ', array_filter(array_values((array) $values)));
        }
        if ($form_id && \FluentForm\App\Helpers\Helper::isEntryAutoDeleteEnabled($form_id)) {
            return '';
        }

        $html = '<ul class="ff_entry_list">';
        foreach ($values as $value) {
            if (!$value) {
                continue;
            }
            $html .= '<li><a href="' . $value . '" target="_blank">' . basename($value) . '</a></li>';
        }

        $html .= '</ul>';
        return $html;
    }

    public static function formatImageValues($values, $isHtml, $form_id = null)
    {
        if (!$values) {
            return $values;
        }

        if (is_string($values)) {
            return $values;
        }
    
        $isHtml = apply_filters('fluentform/render_field_as_html', $isHtml, $values, $form_id);


        if (!$isHtml) {
            return fluentImplodeRecursive(', ', array_filter(array_values((array) $values)));
        }
        if ($form_id && \FluentForm\App\Helpers\Helper::isEntryAutoDeleteEnabled($form_id)) {
            return '';
        }
        if (1 == count($values)) {
            $value = $values[0];
            if (!$value) {
                return '';
            }
            return '<a href="' . $value . '" target="_blank"><img style="max-width:180px" src="' . $value . '" /></a>';
        }

        $html = '<ul class="ff_entry_list ff_entry_images">';
        foreach ($values as $value) {
            if (!$value) {
                continue;
            }
            $html .= '<li style="margin: 20px 20px 20px 0px; display: inline-block; margin-right: 20px;"><a href="' . $value . '" target="_blank"><img style="max-width:180px" src="' . $value . '" /></a></li>';
        }

        $html .= '</ul>';
        return $html;
    }

    public static function formatRepeatFieldValue($value, $field, $form_id)
    {

        if (defined('FLUENTFORM_RENDERING_ENTRIES')) {
            return __('....', 'fluentform');
        }

        if (is_string($value)) {
            return $value;
        }

        try {
            $repeatColumns = ArrayHelper::get($field, 'raw.fields');
            $rows = count($value[0]);
            $columns = count($value);

            ob_start();
            if ($repeatColumns) {
                ?>
                <div class="ff_entry_table_wrapper">
                    <table class="ff_entry_table_field ff-table">
                        <thead>
                            <tr>
                                <?php foreach ($repeatColumns as $repeatColumn) : ?>
                                <th><?php echo fluentform_sanitize_html(ArrayHelper::get($repeatColumn, 'settings.label')); ?>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>

                        <tbody>
                            <?php for ($i = 0; $i < $rows; $i++) : ?>
                            <tr>
                                <?php for ($j = 0; $j < $columns; $j++) : ?>
                                <td>
                                    <?php echo fluentform_sanitize_html($value[$j][$i]); ?>
                                </td>
                                <?php endfor; ?>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            return ob_get_clean();
        } catch (\Exception $e) {
        }

        return $value;
    }

    public static function formatTabularGridFieldValue($value, $field, $form_id, $isHtml = false)
    {
        if (defined('FLUENTFORM_RENDERING_ENTRIES')) {
            return __('....', 'fluentform');
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $value = (object) $value;
        }
        try {
            if (empty($field['raw'])) {
                return $value;
            }
            $columnLabels = $field['raw']['settings']['grid_columns'];
            $fieldType = $field['raw']['settings']['tabular_field_type'];
            $columnHeaders = implode('</th><th  style="text-align: center;">', array_values($columnLabels));

            $elMarkup = "<table class='ff-table'><thead><tr><th></th><th   style='text-align: center;'>{$columnHeaders}</th></tr></thead><tbody>";

            foreach (static::makeTabularData($field['raw']) as $row) {
                $elMarkup .= '<tr>';
                $elMarkup .= "<td>{$row['label']}</td>";
                foreach ($row['columns'] as $column) {
                    $isChecked = '';
                    if ('radio' == $fieldType) {
                        if (isset($value->{$row['name']})) {
                            $isChecked = $value->{$row['name']} == $column['name'] ? 'checked' : '';
                        }
                    } else {
                        if (isset($value->{$row['name']})) {
                            $isChecked = in_array($column['name'], $value->{$row['name']}) ? 'checked' : '';
                        }
                    }
                    $icon = "<input disabled type='{$fieldType}' {$isChecked}>";
                    if ($isChecked) {
                        $icon = '✔';
                    }
                    $elMarkup .= "<td style='text-align: center;'>" . $icon . '</td>';
                }
                $elMarkup .= '</tr>';
            }

            $elMarkup .= '</tbody></table>';

            return $elMarkup;
        } catch (\Exception $e) {
        }
        return '';
    }

    public static function makeTabularData($data)
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

    /**
     * Format input_name field value by concatenating all name fields.
     *
     * @param array|object $value
     *
     * @return string $value
     */
    public static function formatName($value)
    {
        if (is_array($value) || is_object($value)) {
            return fluentImplodeRecursive(' ', array_filter(array_values((array) $value)));
        }

        return $value;
    }

    public static function formatCheckBoxValues($values, $field, $isHtml = false)
    {
        if (!$isHtml) {
            return self::formatValue($values);
        }

        if (!is_array($values) || empty($values)) {
            return $values;
        }

        if (!isset($field['options'])) {
            $field['options'] = [];
            foreach (ArrayHelper::get($field, 'raw.settings.advanced_options', []) as $option) {
                $field['options'][$option['value']] = $option['label'];
            }
        }

        $html = '<ul style="white-space: normal;">';
        foreach ($values as $value) {
            $item = $value;
            if ($itemLabel = ArrayHelper::get($field, 'options.' . $item)) {
                $item = $itemLabel;
            }
            $html .= '<li>' . $item . '</li>';
        }

        return $html . '</ul>';
    }

    public static function resetData()
    {
        static::$data = null;
    }
}
