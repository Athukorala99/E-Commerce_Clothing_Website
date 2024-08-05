<?php

namespace FluentForm\App\Modules\Form\Settings;

use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class FormCssJs
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

    public function addCustomCssJs($formId)
    {
        if (did_action('fluentform/adding_custom_css_js_' . $formId)) {
            return;
        }

        do_action('fluentform/adding_custom_css_js_' . $formId, $formId);

        $metaKeys = ['_custom_form_css', '_custom_form_js'];

        $metas = (new \FluentForm\App\Services\Settings\Customizer())->get($formId, $metaKeys);

        foreach ($metas as $metaKey => $metaValue) {
            if ($metaValue) {
                switch ($metaKey) {
                    case 'css':
                        $css = $metaValue;
                        $css = str_replace('{form_id}', $formId, $css);
                        $customCss = str_replace('FF_ID', $formId, $css);

                        if ($customCss) {
                            $this->addCss($formId, $customCss, 'fluentform_custom_css_' . $formId);
                        }
                        break;
                    case 'js':
                        $this->addJs($formId, $metaValue);
                        break;
                }
            }
        }
    }

    public function addStylerCSS($formId, $styles = [])
    {
        $metaKeys = array_merge(
            ['_ff_form_styler_css', '_ff_selected_style'],
            $styles
        );

        $metas = (new \FluentForm\App\Services\Settings\Customizer())->get($formId, $metaKeys);

        foreach ($styles as $style) {
            if (!$style) {
                continue;
            }

            if ('ffs_inherit_theme' === $style) {
                continue;
            }

            $loadCss = ArrayHelper::get($metas, $style);

            if (!$loadCss) {
                $loadCss = apply_filters('fluentform/build_style_from_theme', '', $formId, $style);

                // todo: remove this from next version. it's only here to support if the user updates the free version first.
                if (!$loadCss) {
                    $selectedStyle = ArrayHelper::get($metas, '_ff_selected_style');
                    $selectedStyleCSS = ArrayHelper::get($metas, '_ff_form_styler_css');

                    if ($selectedStyle == $style && $selectedStyleCSS) {
                        $loadCss = $selectedStyleCSS;
                    }
                }
            }

            if ($loadCss) {
                $this->addCss($formId, $loadCss, 'fluentform_styler_css_' . $formId . '_' . $style);
                
                do_action('fluent_form/loaded_styler_' . $formId . '_' . $style);
            }
        }
    }

    public function getCss($formId)
    {
        $cssMeta = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', '_custom_form_css')
            ->first();

        if (!$cssMeta || !$cssMeta->value) {
            return '';
        }

        $css = $cssMeta->value;
        $css = str_replace('{form_id}', $formId, $css);
        $css = str_replace('FF_ID', $formId, $css);
        return fluentformSanitizeCSS($css);
    }

    public function getJs($formId)
    {
        $jsMeta = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', '_custom_form_js')
            ->first();

        if (!$jsMeta || !$jsMeta->value) {
            return '';
        }

        return $jsMeta->value;
    }

    public function addCss($formId, $css, $cssId = 'fluentform_custom_css')
    {
        if ($css) {
            $action = false;

            if (!did_action('wp_head')) {
                $action = 'wp_head';
            } elseif (!did_action('wp_footer')) {
                $action = 'wp_footer';
            }

            if (Helper::isBlockEditor()) {
                $action = false;
            }

            if ($action) {
                add_action($action, function () use ($css, $cssId) {
                    ?>
                    <style id="<?php echo esc_attr($cssId); ?>" type="text/css">
                        <?php echo fluentformSanitizeCSS($css); ?>
                    </style>

                    <?php
                }, 99);
            } else {
                ?>
                <style id="<?php echo esc_attr($cssId); ?>" type="text/css">
                    <?php echo fluentformSanitizeCSS($css); ?>
                </style>
                <?php
            }
        }
    }

    public function addJs($formId, $customJS)
    {
        if (trim($customJS)) {
            add_action('wp_footer', function () use ($formId, $customJS) {
                ?>
                <script type="text/javascript">
                    jQuery(document.body).on('fluentform_init_<?php echo esc_attr($formId); ?>',
                        function(event, data) {
                            var $form = jQuery(data[0]);
                            var formId = "<?php echo esc_attr($formId); ?>";
                            var $ = jQuery;
                            try {
                                <?php echo fluentform_kses_js($customJS); ?>
                            } catch (e) {
                                console.warn('Error in custom JS of Fluentform ID: ' + formId);
                                console.error(e);
                            }
                        });
                </script>
                <?php
            }, 100);
        }
    }

    /**
     * Get settings for a particular form by id
     */
    public function getSettingsAjax()
    {
        $formId = absint($this->request->get('form_id'));
        wp_send_json_success([
            'custom_css' => $this->getData($formId, '_custom_form_css'),
            'custom_js'  => $this->getData($formId, '_custom_form_js'),
        ], 200);
    }

    /**
     * Save settings for a particular form by id
     */
    public function saveSettingsAjax()
    {
        if (!fluentformCanUnfilteredHTML()) {
            wp_send_json_error([
                'message' => __('You need unfiltered_html permission to save Custom CSS & JS', 'fluentform'),
            ], 423);
        }

        $formId = absint($this->request->get('form_id'));

        $css = fluentformSanitizeCSS($this->request->get('custom_css'));
        $js = fluentform_kses_js($this->request->get('custom_js'));

        $this->store($formId, '_custom_form_css', $css);
        $this->store($formId, '_custom_form_js', $js);

        wp_send_json_success([
            'message' => __('Custom CSS & JS successfully updated', 'fluentform'),
        ], 200);
    }

    protected function getData($formId, $metaKey)
    {
        $row = wpFluent()->table('fluentform_form_meta')
            ->where('form_id', $formId)
            ->where('meta_key', $metaKey)
            ->first();
        if ($row) {
            return $row->value;
        }
        return '';
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
