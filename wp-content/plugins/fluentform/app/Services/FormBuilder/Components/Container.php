<?php

namespace FluentForm\App\Services\FormBuilder\Components;

use FluentForm\Framework\Helpers\ArrayHelper;

class Container extends BaseComponent
{
    /**
     * Max columns for container
     *
     * @var integer
     */
    protected $maxColumns = 12;

    /**
     * Container column class
     *
     * @var string
     */
    protected $columnClass = 'ff-t-cell';

    /**
     * Container wrapper class
     *
     * @var string
     */
    protected $wrapperClass = 'ff-t-container ff-column-container';

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

        $containerClass = ArrayHelper::get($data, 'settings.container_class');

        $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';
        $containerClass .= ' ' . $hasConditions;

        $container_css_class = $this->wrapperClass . ' ff_columns_total_' . count($data['columns']);
        if ($containerClass) {
            $container_css_class = $container_css_class . ' ' . strip_tags($containerClass);
        }

        $atts = $this->buildAttributes(
            ArrayHelper::except($data['attributes'], 'name')
        );

        $columnClass = $this->columnClass;
        echo '<div ' . $atts . " class='" . esc_attr($container_css_class) . "'>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $atts is escaped before being passed in.
        if (isset($data['settings']['label'])) {
            echo '<strong>' . fluentform_sanitize_html($data['settings']['label']) . '</strong>';
        }
        foreach ($data['columns'] as $columnIndex => $column) {
            if (! isset($column['width'])) {
                $column['width'] = ceil(100 / count($data['columns']));
            }

            $newColumnClass = $columnClass . ' ff-t-column-' . ($columnIndex + 1);
            echo "<div class='" . esc_attr($newColumnClass) . "' style='flex-basis: " . esc_attr($column['width']) . "%;'>";

            foreach ($column['fields'] as $item) {
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
            }
            echo '</div>';
        }
        echo '</div>';
    }
}
