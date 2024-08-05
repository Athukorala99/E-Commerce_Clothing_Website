<?php

namespace FluentForm\App\Modules\Form\Settings;

class EntryColumnViewSettings
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;

    public function __construct()
    {
        $this->request = wpFluentForm('request');
    }

    /**
     * Save settings for visible entry columns
     *
     * @return void
     */
    public function saveVisibleColumnsAjax()
    {
        $formId = absint($this->request->get('form_id'));
        $columns = wp_unslash($this->request->get('visible_columns'));

        $this->store($formId, '_visible_columns', $columns);

        wp_send_json_success();
    }

    /**
     * Save settings for entry column display order
     */
    public function saveEntryColumnsOrderAjax()
    {
        $formId = absint($this->request->get('form_id'));
        $columns = wp_unslash($this->request->get('columns_order'));

        $this->store($formId, '_columns_order', $columns);

        wp_send_json_success();
    }

    /**
     * Reset column display order settings
     */
    public function resetEntryDisplaySettings()
    {
        $formId = absint($this->request->get('form_id'));

        $this->store($formId, '_columns_order', null);

        wp_send_json_success();
    }

    protected function store($formId, $metaKey, $metaValue)
    {
        $row = wpFluent()->table('fluentform_form_meta')
                         ->where('form_id', $formId)
                         ->where('meta_key', $metaKey)
                         ->first();

        if (!$row) {
            return wpFluent()->table('fluentform_form_meta')
                             ->insertGetId([
                                 'form_id'  => $formId,
                                 'meta_key' => $metaKey,
                                 'value'    => $metaValue,
                             ]);
        }

        return wpFluent()->table('fluentform_form_meta')
                         ->where('id', $row->id)
                         ->update([
                             'value' => $metaValue,
                         ]);
    }
}
