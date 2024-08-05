<?php

namespace FluentForm\App\Modules\Widgets;

use FluentForm\App\Models\Form;

class SidebarWidgets extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'fluentform_widget',
            esc_html__('Fluent Forms Widget', 'fluentform'),
            ['description' => esc_html__('Add your form by Fluent Forms', 'fluentform'), ]
        );
    }

    public function widget($args, $instance)
    {
        $selectedForm = empty($instance['allforms']) ? '' : intval($instance['allforms']);

        if (!$selectedForm) {
            return;
        }

        echo $args['before_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        }

        if ('' != $selectedForm) {
            $shortcode = "[fluentform id='$selectedForm']";
            echo do_shortcode($shortcode); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        }

        echo $args['after_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
    }

    public function form($instance)
    {
        $selectedForm = empty($instance['allforms']) ? '' : $instance['allforms'];

        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('', 'fluentform');
        }
        // Widget admin form
        ?>
        <p>
            <label
                for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title (optional):', 'fluentform'); ?></label>
            <input class="widefat"
                id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <?php
        $forms = Form::select(['id', 'title'])
            ->orderBy('id', 'DESC')
            ->get();
        ?>

        <label
            for="<?php echo esc_attr($this->get_field_id('allforms')); ?>">Select
            a form:
            <select style="margin-bottom: 12px;" class='widefat'
                id="<?php echo esc_attr($this->get_field_id('allforms')); ?>"
                name="<?php echo esc_attr($this->get_field_name('allforms')); ?>"
                type="text">
                <?php
                        foreach ($forms as $item) {
                            ?>
                <option <?php if ($item->id == $selectedForm) {
                    echo 'selected';
                } ?> value='<?php echo esc_attr($item->id); ?>'>
                    <?php echo esc_html($item->title); ?> (<?php echo esc_attr($item->id); ?>)
                </option>
                <?php
                        }
                ?>
            </select>
        </label>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['allforms'] = intval($new_instance['allforms']);
        return $instance;
    }
}
