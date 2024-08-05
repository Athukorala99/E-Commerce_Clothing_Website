<div class="ff_form_entries" id="ff_form_entries_app">
    <router-view
        :form_id="<?php echo esc_attr($form_id); ?>"
        :has_pdf="<?php echo esc_attr($has_pdf); ?>"></router-view>
    <global-search></global-search>
</div>
