<?php

namespace FluentForm\App\Services\Settings;

use Exception;
use FluentForm\App\Models\FormMeta;
use FluentForm\Framework\Support\Arr;

class Customizer
{
    public function get($formId, $metaKeys = ['_custom_form_css', '_custom_form_js'])
    {
        return FormMeta::where('form_id', $formId)
            ->whereIn('meta_key', $metaKeys)
            ->get()
            ->keyBy(function ($item) {
                if ($item->meta_key === '_custom_form_css') {
                    return 'css';
                } elseif ($item->meta_key === '_custom_form_js') {
                    return 'js';
                } else {
                    return $item->meta_key;
                }
            })
            ->transform(function ($item) {
                return $item->value;
            })->toArray();
    }

    public function store($attributes = [])
    {
        if (!fluentformCanUnfilteredHTML()) {
            throw new Exception(
                __('You need unfiltered_html permission to save Custom CSS & JS', 'fluentform')
            );
        }

        $formId = absint(Arr::get($attributes, 'form_id'));

        $css = fluentformSanitizeCSS(Arr::get($attributes, 'css'));
        $js = fluentform_kses_js(Arr::get($attributes, 'js'));

        FormMeta::persist($formId, '_custom_form_css', $css);
        FormMeta::persist($formId, '_custom_form_js', $js);
    }
}
