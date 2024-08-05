<?php

use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Component\BaseComponent;
use FluentForm\App\Services\FormBuilder\EditorShortCode;

/**
 ***** DO NOT CALL ANY FUNCTIONS DIRECTLY FROM THIS FILE ******
 *
 * This file will be loaded even before the framework is loaded
 * so the $app is not available here, only declare functions here.
 */

//if ('dev' == $app->config->get('app.env')) {
//    $globalsDevFile = __DIR__ . '/globals_dev.php';
//
//    is_readable($globalsDevFile) && include $globalsDevFile;
//}

if (!function_exists('dd')) {
    // function dd()
    // {
    //     foreach (func_get_args() as $arg) {
    //         echo '<pre>';
    //         print_r($arg); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $value is only used for debugging in development.
    //         echo '</pre>';
    //     }
    //     exit();
    // }
}

/**
 * Get fluentform instance or other core modules
 *
 * @param string $key
 *
 * @return mixed
 */
function wpFluentForm($key = null)
{
    return \FluentForm\App\App::make($key);
}

/**
 * Generate URL for static assets
 *
 * @param string $path
 *
 * @return string
 */
function fluentFormMix($path = '')
{
    return wpFluentForm('url.assets') . ltrim($path, '/');
}

if (! function_exists('wpFluent')) {
    /**
     * @return \FluentForm\Framework\Database\Query\Builder|\FluentForm\Framework\Database\Query\WPDBConnection
     */
    function wpFluent()
    {
        return wpFluentForm('db');
    }
}


function wpFluentFormAddComponent(BaseComponent $component)
{
    return $component->_init();
}

/**
 * Sanitize form inputs recursively.
 *
 * @param $input
 *
 * @return mixed $input
 */
function fluentFormSanitizer($input, $attribute = null, $fields = [])
{
    if (is_string($input)) {
        $element = ArrayHelper::get($fields, $attribute . '.element');

        if (in_array($element, ['post_content', 'rich_text_input'])) {
            return wp_kses_post($input);
        } elseif ('textarea' === $element) {
            $input = sanitize_textarea_field($input);
        } elseif ('input_email' === $element) {
            $input = strtolower(sanitize_text_field($input));
        } elseif ('input_url' === $element) {
            $input = sanitize_url($input);
        } else {
            $input = sanitize_text_field($input);
        }
    } elseif (is_array($input)) {
        foreach ($input as $key => &$value) {
            $attribute = $attribute ? $attribute . '[' . $key . ']' : $key;

            $value = fluentFormSanitizer($value, $attribute, $fields);

            $attribute = null;
        }
    }

    return $input;
}

function fluentFormEditorShortCodes()
{
    $generalShortCodes = [EditorShortCode::getGeneralShortCodes()];
    /* This filter is  deprecated, will be removed soon. */
    $generalShortCodes = apply_filters('fluentform_editor_shortcodes', $generalShortCodes);

    return apply_filters('fluentform/editor_shortcodes', $generalShortCodes);
}

function fluentFormGetAllEditorShortCodes($form)
{
    $editorShortCodes = EditorShortCode::getShortCodes($form);
    /* This filter is deprecated and will be removed soon */
    $editorShortCodes = apply_filters(
        'fluentform_all_editor_shortcodes',
        $editorShortCodes,
        $form
    );
    return apply_filters(
        'fluentform/all_editor_shortcodes',
        $editorShortCodes,
        $form
    );
}

/**
 * Recursively implode a multi-dimentional array
 *
 * @param string $glue
 * @param array  $array
 *
 * @return string
 */
function fluentImplodeRecursive($glue, array $array)
{
    $fn = function ($glue, array $array) use (&$fn) {
        $result = '';
        foreach ($array as $item) {
            if (is_array($item)) {
                $result .= $fn($glue, $item);
            } else {
                $result .= $glue . $item;
            }
        }

        return $result;
    };

    return ltrim($fn($glue, $array), $glue);
}

function fluentform_get_active_theme_slug()
{
    $ins = get_option('_ff_ins_by');

    if ($ins) {
        return sanitize_text_field($ins);
    }

    if (defined('TEMPLATELY_FILE')) {
        return 'templately';
    }

    return get_option('template');
}

function getFluentFormCountryList()
{
    static $countries = null;

    if (is_null($countries)) {
        $countries = fluentformLoadFile('/Services/FormBuilder/CountryNames.php');
    }

    return $countries;
}

function fluentFormWasSubmitted($action = 'fluentform_submit')
{
    return wpFluentForm('request')->get('action') == $action;
}

if (!function_exists('isWpAsyncRequest')) {
    function isWpAsyncRequest($action)
    {
        return false !== strpos(wpFluentForm('request')->get('action'), $action);
    }
}

function fluentFormIsHandlingSubmission()
{
    $status = fluentFormWasSubmitted() || isWpAsyncRequest('fluentform_async_request');
    
    $status = apply_filters_deprecated(
        'fluentform_is_handling_submission',
        [
            $status
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/is_handling_submission',
        'Use fluentform/is_handling_submission instead of fluentform_is_handling_submission'
    );
    return apply_filters('fluentform/is_handling_submission', $status);
}

function fluentform_mb_strpos($haystack, $needle)
{
    if (function_exists('mb_strpos')) {
        return mb_strpos($haystack, $needle);
    }

    return strpos($haystack, $needle);
}

function fluentFormHandleScheduledTasks()
{
    $failedActions = wpFluent()->table('ff_scheduled_actions')->where('status', 'failed')->where('retry_count', '<', 4)->get();

    if ($failedActions) {
        $scheduler = wpFluentForm('fluentFormAsyncRequest');

        foreach ($failedActions as $action) {
            $scheduler->process($action);
        }
    }

    $rand = mt_rand(1, 10);
    if ($rand >= 5) {
        do_action('fluentform/maybe_scheduled_jobs');
    }
}

function fluentFormHandleScheduledEmailReport()
{
    \FluentForm\App\Services\Scheduler\Scheduler::processEmailReport();
}

function fluentform_upgrade_url()
{
    return 'https://fluentforms.com/pricing/?utm_source=plugin&utm_medium=wp_install&utm_campaign=ff_upgrade&theme_style=' . fluentform_get_active_theme_slug();
}

function fluentform_integrations_url()
{
    return 'https://fluentforms.com/integration/?utm_source=plugin&utm_medium=wp_install&utm_campaign=ff_upgrade&theme_style=' . fluentform_get_active_theme_slug();
}

function fluentFormApi($module = 'forms')
{
    if ('forms' == $module) {
        return new \FluentForm\App\Api\Form();
    } elseif ('submissions' == $module) {
        return new \FluentForm\App\Api\Submission();
    }

    throw new \Exception('No Module found with name ' . $module);
}

function fluentFormGetRandomPhoto()
{
    $photos = [
        'demo_1.jpg',
        'demo_2.jpg',
        'demo_3.jpg',
        'demo_4.jpg',
        'demo_5.jpg',
    ];

    $selected = array_rand($photos, 1);

    $photoName = $photos[$selected];

    return fluentformMix('img/conversational/' . $photoName);
}

function fluentFormRender($atts)
{
    $shortcodeDefaults = [
        'id'                 => null,
        'title'              => null,
        'css_classes'        => '',
        'permission'         => '',
        'type'               => 'classic',
        'permission_message' => __('Sorry, You do not have permission to view this form', 'fluentform'),
    ];
    $atts = shortcode_atts($shortcodeDefaults, $atts);

    return (new \FluentForm\App\Modules\Component\Component(wpFluentForm()))->renderForm($atts);
}

/**
 * Print internal content (not user input) without escaping.
 */
function fluentFormPrintUnescapedInternalString($string)
{
    echo $string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- deprecated function, should remove it later.
}

function fluentform_options_sanitize($options)
{
    $maps = [
        'label'      => 'wp_kses_post',
        'value'      => 'sanitize_text_field',
        'image'      => 'sanitize_url',
        'calc_value' => 'sanitize_text_field',
    ];

    $mapKeys = array_keys($maps);

    foreach ($options as $optionIndex => $option) {
        $attributes = array_filter(ArrayHelper::only($option, $mapKeys));
        foreach ($attributes as $key => $value) {
            $options[$optionIndex][$key] = call_user_func($maps[$key], $value);
        }
    }

    return $options;
}

function fluentform_sanitize_html($html)
{
    if (!$html) {
        return $html;
    }

    // Return $html if it's just a plain text
    if (!preg_match('/<[^>]*>/', $html)) {
        return $html;
    }

    $tags = wp_kses_allowed_html('post');
    $tags['style'] = [
        'types' => [],
    ];
    // iframe
    $tags['iframe'] = [
        'width'           => [],
        'height'          => [],
        'src'             => [],
        'srcdoc'          => [],
        'title'           => [],
        'frameborder'     => [],
        'allow'           => [],
        'class'           => [],
        'id'              => [],
        'allowfullscreen' => [],
        'style'           => [],
    ];
    //button
    $tags['button']['onclick'] = [];

    //svg
    if (empty($tags['svg'])) {
        $svg_args = [
            'svg' => [
                'class'           => true,
                'aria-hidden'     => true,
                'aria-labelledby' => true,
                'role'            => true,
                'xmlns'           => true,
                'width'           => true,
                'height'          => true,
                'viewbox'         => true,
                'fill'            => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linecap'  => true,
                'stroke-linejoin' => true
            ],
            'g'     => ['fill' => true],
            'title' => ['title' => true],
            'path'  => [
                'd'         => true,
                'fill'      => true,
                'transform' => true,
            ],
            'polyline' => [
                'points' => true
            ]
        ];
        $tags = array_merge($tags, $svg_args);
    }
    
    $tags = apply_filters_deprecated(
        'fluentform_allowed_html_tags',
        [
            $tags
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/allowed_html_tags',
        'Use fluentform/allowed_html_tags instead of fluentform_allowed_html_tags'
    );

    $tags = apply_filters('fluentform/allowed_html_tags', $tags);

    return wp_kses($html, $tags);
}

function fluentform_kses_js($content)
{
    return $content ? preg_replace('/<script.*?>[\s\S]*<\/script>/is', '', $content) : '';
}

/**
 * Sanitize inputs recursively.
 *
 * @param array $input
 * @param array $sanitizeMap
 *
 * @return array $input
 */
function fluentform_backend_sanitizer($inputs, $sanitizeMap = [])
{
    $originalValues = $inputs;
    foreach ($inputs as $key => &$value) {
        if (is_array($value)) {
            $value = fluentform_backend_sanitizer($value, $sanitizeMap);
        } else {
            $method = ArrayHelper::get($sanitizeMap, $key);
            if (is_callable($method)) {
                $value = call_user_func($method, $value);
            }
        }
    }

    return apply_filters('fluentform/backend_sanitized_values', $inputs, $originalValues);
}

/**
 * Sanitizes CSS.
 *
 * @return mixed $css
 */
function fluentformSanitizeCSS($css)
{
    return preg_match('#</?\w+#', $css) ? '' : $css;
}

function fluentformCanUnfilteredHTML()
{
    return current_user_can('unfiltered_html') || apply_filters('fluentform/disable_fields_sanitize', false);
}

function fluentformLoadFile($path)
{
    return require wpFluentForm('path.app') . '/' . ltrim($path, '/');
}

if (!function_exists('fluentValidator')) {
    function fluentValidator($data = [], $rules = [], $messages = [])
    {
        return wpFluentForm('validator')->make($data, $rules, $messages);
    }
}

function fluentformGetPages()
{
    $pages = get_pages();
    $formattedPages = [];

    foreach ($pages as $page) {
        $formattedPages[] = [
            'ID'         => $page->ID,
            'post_title' => $page->post_title,
            'guid'       => $page->guid,
        ];
    }

    return $formattedPages;
}
