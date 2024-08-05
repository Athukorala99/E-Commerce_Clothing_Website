<?php

namespace FluentForm\App\Helpers;

use FluentForm\App\Models\EntryDetails;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\App\Models\SubmissionMeta;
use FluentForm\App\Services\FormBuilder\Components\SelectCountry;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Helpers\Traits\GlobalDefaultMessages;

class Helper
{
    use GlobalDefaultMessages;

    public static $tabIndex = 0;

    public static $formInstance = 0;

    public static $loadedForms = [];

    public static $tabIndexStatus = 'na';

    /**
     * Sanitize form inputs recursively.
     *
     * @param $input
     *
     * @return string $input
     */
    public static function sanitizer($input, $attribute = null, $fields = [])
    {
        if (is_string($input)) {
            if ('textarea' === ArrayHelper::get($fields, $attribute . '.element')) {
                $input = sanitize_textarea_field($input);
            } else {
                $input = sanitize_text_field($input);
            }
        } elseif (is_array($input)) {
            foreach ($input as $key => &$value) {
                $attribute = $attribute ? $attribute . '[' . $key . ']' : $key;

                $value = static::sanitizer($value, $attribute, $fields);

                $attribute = null;
            }
        }

        return $input;
    }

    public static function makeMenuUrl($page = 'fluent_forms_settings', $component = null)
    {
        $baseUrl = admin_url('admin.php?page=' . $page);

        $hash = ArrayHelper::get($component, 'hash', '');
        if ($hash) {
            $baseUrl = $baseUrl . '#' . $hash;
        }

        $query = ArrayHelper::get($component, 'query');

        if ($query) {
            $paramString = http_build_query($query);
            if ($hash) {
                $baseUrl .= '?' . $paramString;
            } else {
                $baseUrl .= '&' . $paramString;
            }
        }

        return $baseUrl;
    }

    public static function getHtmlElementClass($value1, $value2, $class = 'active', $default = '')
    {
        return $value1 === $value2 ? $class : $default;
    }

    /**
     * Determines if the given string is a valid json.
     *
     * @param $string
     *
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);

        return JSON_ERROR_NONE === json_last_error();
    }

    public static function isSlackEnabled()
    {
        $globalModules = get_option('fluentform_global_modules_status');

        return $globalModules && isset($globalModules['slack']) && 'yes' == $globalModules['slack'];
    }

    public static function getEntryStatuses($form_id = false)
    {
        $statuses = [
            'unread'    => 'Unread',
            'read'      => 'Read',
            'favorites' => 'Favorites',
        ];

        $statuses = apply_filters_deprecated(
            'fluentform_entry_statuses_core',
            [
                $statuses,
                $form_id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/entry_statuses_core',
            'Use fluentform/entry_statuses_core instead of fluentform_entry_statuses_core.'
        );

        $statuses = apply_filters('fluentform/entry_statuses_core', $statuses, $form_id);

        $statuses['trashed'] = 'Trashed';

        return $statuses;
    }

    public static function getReportableInputs()
    {
        $data = [
            'select',
            'input_radio',
            'input_checkbox',
            'ratings',
            'net_promoter',
            'select_country',
            'net_promoter_score',
        ];

        $data = apply_filters_deprecated(
            'fluentform_reportable_inputs',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/reportable_inputs',
            'Use fluentform/reportable_inputs instead of fluentform_reportable_inputs.'
        );

        return apply_filters('fluentform/reportable_inputs', $data);
    }

    public static function getSubFieldReportableInputs()
    {
        $grid = apply_filters_deprecated(
            'fluentform_subfield_reportable_inputs',
            [
                ['tabular_grid']
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/subfield_reportable_inputs',
            'Use fluentform/subfield_reportable_inputs instead of fluentform_subfield_reportable_inputs.'
        );

        return apply_filters('fluentform/subfield_reportable_inputs', $grid);
    }

    public static function getFormMeta($formId, $metaKey, $default = '')
    {
        return FormMeta::retrieve($metaKey, $formId, $default);
    }

    public static function setFormMeta($formId, $metaKey, $value)
    {
        if ($meta = FormMeta::persist($formId, $metaKey, $value)) {
            return $meta->id;
        }
        return null;
    }

    public static function getSubmissionMeta($submissionId, $metaKey, $default = false)
    {
        return SubmissionMeta::retrieve($metaKey, $submissionId, $default);
    }

    public static function setSubmissionMeta($submissionId, $metaKey, $value, $formId = false)
    {
        if ($meta = SubmissionMeta::persist($submissionId, $metaKey, $value, $formId)) {
            return $meta->id;
        }
        return null;
    }

    public static function isEntryAutoDeleteEnabled($formId)
    {
        if (
            'yes' == ArrayHelper::get(static::getFormMeta($formId, 'formSettings', []), 'delete_entry_on_submission', '')
        ) {
            return true;
        }
        return false;
    }

    public static function formExtraCssClass($form)
    {
        if (!$form->settings) {
            $formSettings = static::getFormMeta($form->id, 'formSettings');
        } else {
            $formSettings = $form->settings;
        }

        if (!$formSettings) {
            return '';
        }

        if ($extraClass = ArrayHelper::get($formSettings, 'form_extra_css_class')) {
            return esc_attr($extraClass);
        }

        return '';
    }

    public static function getNextTabIndex($increment = 1)
    {
        if (static::isTabIndexEnabled()) {
            static::$tabIndex += $increment;

            return static::$tabIndex;
        }

        return '';
    }

    public static function getFormInstaceClass($formId)
    {
        static::$formInstance += 1;

        return 'ff_form_instance_' . $formId . '_' . static::$formInstance;
    }

    public static function resetTabIndex()
    {
        static::$tabIndex = 0;
    }

    public static function isFluentAdminPage()
    {
        $fluentPages = [
            'fluent_forms',
            'fluent_forms_all_entries',
            'fluent_forms_transfer',
            'fluent_forms_settings',
            'fluent_forms_add_ons',
            'fluent_forms_docs',
            'fluent_forms_payment_entries',
            'fluent_forms_smtp'
        ];

        $status = true;

        $page = wpFluentForm('request')->get('page');

        if (!$page || !in_array($page, $fluentPages)) {
            $status = false;
        }

        $status = apply_filters_deprecated(
            'fluentform_is_admin_page',
            [
                $status
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/is_admin_page',
            'Use fluentform/is_admin_page instead of fluentform_is_admin_page.'
        );

        return apply_filters('fluentform/is_admin_page', $status);
    }

    public static function getShortCodeIds($content, $tag = 'fluentform', $selector = 'id')
    {
        if (false === strpos($content, '[')) {
            return [];
        }

        preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return [];
        }

        $ids = [];
        $attributes = [];

        foreach ($matches as $shortcode) {
            if (count($shortcode) >= 2 && $tag === $shortcode[2]) {
                // Replace braces with empty string.
                $parsedCode = str_replace(['[', ']', '&#91;', '&#93;'], '', $shortcode[0]);

                $result = shortcode_parse_atts($parsedCode);

                if (!empty($result[$selector])) {

                    if($tag == 'fluentform' && !empty($result['type']) && $result['type'] == 'conversational') {
                        continue;
                    }

                    $ids[$result[$selector]] = $result[$selector];

                    $theme = ArrayHelper::get($result, 'theme');

                    if ($theme) {
                        $attributes[] = [
                            'formId' => $result[$selector],
                            'theme'  => $theme
                        ];
                    }
                }
            }
        }

        if ($attributes) {
            $ids['attributes'] = $attributes;
        }

        return $ids;
    }

    public static function getFormsIdsFromBlocks($content)
    {
        $ids = [];
        $attributes = [];

        if (!function_exists('parse_blocks')) {
            return $ids;
        }

        $has_block = false !== strpos($content, '<!-- wp:fluentfom/guten-block' . ' ');

        if (!$has_block) {
            return $ids;
        }

        $parsedBlocks = parse_blocks($content);
        foreach ($parsedBlocks as $block) {
            if (!ArrayHelper::exists($block, 'blockName') || !ArrayHelper::get($block, 'attrs.formId')) {
                continue;
            }

            $hasBlock = strpos($block['blockName'], 'fluentfom/guten-block') === 0;
            if ($hasBlock) {
                $formId = (int) $block['attrs']['formId'];
                
                $ids[] = $formId;

                $theme = ArrayHelper::get($block, 'attrs.themeStyle');

                if ($theme) {
                    $attributes[] = [
                        'formId' => $formId,
                        'theme'  => $theme
                    ];
                }
            }
        }

        if ($attributes) {
            $ids['attributes'] = $attributes;
        }

        return $ids;
    }

    public static function isTabIndexEnabled()
    {
        if ('na' == static::$tabIndexStatus) {
            $globalSettings = get_option('_fluentform_global_form_settings');
            static::$tabIndexStatus = 'yes' == ArrayHelper::get($globalSettings, 'misc.tabIndex');
        }

        return static::$tabIndexStatus;
    }

    public static function isMultiStepForm($formId)
    {
        $form = Form::find($formId);
        $fields = json_decode($form->form_fields, true);

        if (ArrayHelper::get($fields, 'stepsWrapper')) {
            return true;
        }

        return false;
    }

    public static function hasFormElement($formId, $elementName)
    {
        $form = Form::find($formId);
        $fieldsJson = $form->form_fields;

        return false != strpos($fieldsJson, '"element":"' . $elementName . '"');
    }

    public static function isUniqueValidation($validation, $field, $formData, $fields, $form)
    {
        if ('yes' == ArrayHelper::get($field, 'raw.settings.is_unique')) {
            $fieldName = ArrayHelper::get($field, 'name');
            if ($inputValue = ArrayHelper::get($formData, $fieldName)) {
                $exist = EntryDetails::where('form_id', $form->id)
                    ->where('field_name', $fieldName)
                    ->where('field_value', $inputValue)
                    ->exists();

                // if form has pending payment then the value doesn't exist in EntryDetails table
                // further checking on Submission table if the value exists
                if (!$exist && $form->has_payment) {
                    $allSubmission = Submission::where('form_id', $form->id)->get()->toArray();

                    foreach($allSubmission as $submission) {
                        $response = json_decode(ArrayHelper::get($submission, 'response'), true);
                        $exist = $inputValue == ArrayHelper::get($response, $fieldName);
                    }
                }

                if ($exist) {
                    $typeName = ArrayHelper::get($field, 'element', 'input_text');
                    return [
                        'unique' => apply_filters('fluentform/validation_message_unique_'. $typeName, ArrayHelper::get($field, 'raw.settings.unique_validation_message'), $field),
                    ];
                }
            }
        }

        return $validation;
    }
    

    
    public static function hasPartialEntries($formId)
    {
        static $cache = [];
        if (isset($cache[$formId])) {
            return $cache[$formId];
        }

        $cache[$formId] = 'yes' == static::getFormMeta($formId, 'form_save_state_status');

        return $cache[$formId];
    }

    public static function getNumericFormatters()
    {
        $data = [
            'none'                 => [
                'value' => '',
                'label' => 'None',
            ],
            'comma_dot_style'      => [
                'value'    => 'comma_dot_style',
                'label'    => __('US Style with Decimal (EX: 123,456.00)', 'fluentform'),
                'settings' => [
                    'decimal'   => '.',
                    'separator' => ',',
                    'precision' => 2,
                    'symbol'    => '',
                ],
            ],
            'dot_comma_style_zero' => [
                'value'    => 'dot_comma_style_zero',
                'label'    => __('US Style without Decimal (Ex: 123,456,789)', 'fluentform'),
                'settings' => [
                    'decimal'   => '.',
                    'separator' => ',',
                    'precision' => 0,
                    'symbol'    => '',
                ],
            ],
            'dot_comma_style'      => [
                'value'    => 'dot_comma_style',
                'label'    => __('EU Style with Decimal (Ex: 123.456,00)', 'fluentform'),
                'settings' => [
                    'decimal'   => ',',
                    'separator' => '.',
                    'precision' => 2,
                    'symbol'    => '',
                ],
            ],
            'comma_dot_style_zero' => [
                'value'    => 'comma_dot_style_zero',
                'label'    => __('EU Style without Decimal (EX: 123.456.789)', 'fluentform'),
                'settings' => [
                    'decimal'   => ',',
                    'separator' => '.',
                    'precision' => 0,
                    'symbol'    => '',
                ],
            ],
        ];

        $data = apply_filters_deprecated(
            'fluentform_numeric_styles',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/numeric_styles',
            'Use fluentform/numeric_styles instead of fluentform_numeric_styles.'
        );

        return apply_filters('fluentform/numeric_styles', $data);
    }

    public static function getNumericValue($input, $formatterName)
    {
        $formatters = static::getNumericFormatters();
        if (empty($formatters[$formatterName]['settings'])) {
            return $input;
        }
        $settings = $formatters[$formatterName]['settings'];
        $number = floatval(str_replace($settings['decimal'], '.', preg_replace('/[^-?\d' . preg_quote($settings['decimal']) . ']/', '', $input)));

        return number_format($number, $settings['precision'], '.', '');
    }

    public static function getNumericFormatted($input, $formatterName)
    {
        if (!is_numeric($input)) {
            return $input;
        }
        $formatters = static::getNumericFormatters();
        if (empty($formatters[$formatterName]['settings'])) {
            return $input;
        }
        $settings = $formatters[$formatterName]['settings'];

        return number_format($input, $settings['precision'], $settings['decimal'], $settings['separator']);
    }

    public static function getDuplicateFieldNames($fields)
    {
        $fields = json_decode($fields, true);
        $items = $fields['fields'];
        $inputNames = static::getFieldNamesStatuses($items);
        $uniqueNames = array_unique($inputNames);

        if (count($inputNames) == count($uniqueNames)) {
            return [];
        }

        return array_diff_assoc($inputNames, $uniqueNames);
    }

    protected static function getFieldNamesStatuses($fields)
    {
        $names = [];

        foreach ($fields as $field) {
            if ('container' == ArrayHelper::get($field, 'element')) {
                $columns = ArrayHelper::get($field, 'columns', []);
                foreach ($columns as $column) {
                    $columnInputs = static::getFieldNamesStatuses(ArrayHelper::get($column, 'fields', []));
                    $names = array_merge($names, $columnInputs);
                }
            } else {
                if ($name = ArrayHelper::get($field, 'attributes.name')) {
                    $names[] = $name;
                }
            }
        }

        return $names;
    }

    public static function isConversionForm($formId)
    {
        static $cache = [];
        if (isset($cache[$formId])) {
            return $cache[$formId];
        }

        $cache[$formId] = 'yes' == static::getFormMeta($formId, 'is_conversion_form');

        return $cache[$formId];
    }

    public static function getPreviewUrl($formId, $type = '')
    {
        if ('conversational' == $type) {
            return static::getConversionUrl($formId);
        } elseif ('classic' == $type) {
            return site_url('?fluent_forms_pages=1&design_mode=1&preview_id=' . $formId) . '#ff_preview';
        } else {
            if (static::isConversionForm($formId)) {
                return static::getConversionUrl($formId);
            }
        }

        return site_url('?fluent_forms_pages=1&design_mode=1&preview_id=' . $formId) . '#ff_preview';
    }

    public static function getFormAdminPermalink($route, $form)
    {
        $baseUrl = admin_url('admin.php?page=fluent_forms');

        return $baseUrl . '&route=' . $route . '&form_id=' . $form->id;
    }

    public static function getFormSettingsUrl($form)
    {
        $baseUrl = admin_url('admin.php?page=fluent_forms');

        return $baseUrl . '&form_id=' . $form->id . '&route=settings&sub_route=form_settings#basic_settings';
    }

    private static function getConversionUrl($formId)
    {
        $meta = static::getFormMeta($formId, 'ffc_form_settings_meta', []);
        $key = ArrayHelper::get($meta, 'share_key', '');

        $slug = apply_filters_deprecated(
            'fluentform_conversational_url_slug',
            [
                'fluent-form'
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/conversational_url_slug',
            'Use fluentform/conversational_url_slug instead of fluentform_conversational_url_slug.'
        );

        $paramKey = apply_filters('fluentform/conversational_url_slug', $slug);

        if ('form' == $paramKey) {
            $paramKey = 'fluent-form';
        }
        if ($key) {
            return site_url('?' . $paramKey . '=' . $formId . '&form=' . $key);
        }

        return site_url('?' . $paramKey . '=' . $formId);
    }

    public static function fileUploadLocations()
    {
        $locations = [
            [
                'value' => 'default',
                'label' => __('Fluent Forms Default', 'fluentform'),
            ],
            [
                'value' => 'wp_media',
                'label' => __('Media Library', 'fluentform'),
            ],
        ];

        $locations = apply_filters_deprecated(
            'fluentform_file_upload_options',
            [
                $locations
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/file_upload_options',
            'Use fluentform/file_upload_options instead of fluentform_file_upload_options'
        );

        return apply_filters('fluentform/file_upload_options', $locations);
    }

    public static function unreadCount($formId)
    {
        return Submission::where('status', 'unread')
            ->where('form_id', $formId)
            ->count();
    }

    public static function getForms()
    {
        $ff_list = Form::select(['id', 'title'])->orderBy('id', 'DESC')->get();
        $forms = [];

        if ($ff_list) {
            $forms[0] = esc_html__('Select a Fluent Forms', 'fluentform');
            foreach ($ff_list as $form) {
                $forms[$form->id] = esc_html($form->title) . ' (' . $form->id . ')';
            }
        } else {
            $forms[0] = esc_html__('Create a Form First', 'fluentform');
        }

        return $forms;
    }

    public static function replaceBrTag($content, $with = '')
    {
        if (is_array($content)) {
            foreach ($content as $key => $value) {
                $content[$key] = static::replaceBrTag($value, $with);
            }
        } elseif (static::hasBrTag($content)) {
            $content = str_replace('<br />', $with, $content);
        }

        return $content;
    }

    public static function hasBrTag($content)
    {
        return is_string($content) && false !== strpos($content, '<br />');
    }

    public static function sanitizeForCSV($content)
    {
        $formulas = ['=', '-', '+', '@', "\t", "\r"];

        if (Str::startsWith($content, $formulas)) {
            $content = "'" . $content;
        }

        return $content;
    }

    public static function sanitizeOrderValue($orderType = '')
    {
        $orderBys = ['ASC', 'DESC'];

        $orderType = trim(strtoupper($orderType));

        return in_array($orderType, $orderBys) ? $orderType : 'DESC';
    }

    public static function getForm($id)
    {
        return Form::where('id', $id)->first();
    }

    public static function shouldHidePassword($formId)
    {
        $isTruncate = apply_filters_deprecated(
            'fluentform_truncate_password_values',
            [
                true,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/truncate_password_values',
            'Use fluentform/truncate_password_values instead of fluentform_truncate_password_values.'
        );

        return apply_filters('fluentform/truncate_password_values', $isTruncate, $formId) &&
            (
                (defined('FLUENTFORM_RENDERING_ENTRIES') && FLUENTFORM_RENDERING_ENTRIES) ||
                (defined('FLUENTFORM_RENDERING_ENTRY') && FLUENTFORM_RENDERING_ENTRY) ||
                (defined('FLUENTFORM_EXPORTING_ENTRIES') && FLUENTFORM_EXPORTING_ENTRIES)
            );
    }

    // make tabular-grid value markdown format
    public static function getTabularGridFormatValue($girdData, $field, $rowJoiner = '<br />', $colJoiner = ', ', $type = '')
    {
        if (!$girdData || !$field) {
            return '';
        }
        $girdRows = ArrayHelper::get($field, 'raw.settings.grid_rows', '');
        $girdCols = ArrayHelper::get($field, 'raw.settings.grid_columns', '');
        $value = '';
        $lastRow = key(array_slice($girdData, -1, 1, true));
        foreach ($girdData as $row => $column) {
            $_row = $row;
            if ($girdRows && isset($girdRows[$row])) {
                $row = $girdRows[$row];
            }
            if ('markdown' === $type) {
                $value .= '- *' . $row . '* :  ';
            } else {
                $value .= $row . ': ';
            }
            if (is_array($column)) {
                foreach ($column as $index => $item) {
                    $_colJoiner = $colJoiner;
                    if ($girdCols && isset($girdCols[$item])) {
                        $item = $girdCols[$item];
                    }
                    if ($index == (count($column) - 1)) {
                        $_colJoiner = '';
                    }
                    $value .= $item . $_colJoiner;
                }
            } else {
                if ($girdCols && isset($girdCols[$column])) {
                    $column = $girdCols[$column];
                }
                $value .= $column;
            }
            if ($_row != $lastRow) {
                $value .= $rowJoiner;
            }
        }

        return $value;
    }

    public static function getInputNameFromShortCode($value)
    {
        preg_match('/{+(.*?)}/', $value, $matches);
        if ($matches && false !== strpos($matches[1], 'inputs.')) {
            return substr($matches[1], strlen('inputs.'));
        }

        return '';
    }

    public static function getRestInfo()
    {
        $config = wpFluentForm('config');

        $namespace = $config->get('app.rest_namespace');
        $version = $config->get('app.rest_version');
        $restUrl = rest_url($namespace . '/' . $version);
        $restUrl = rtrim($restUrl, '/\\');

        return [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => $restUrl,
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $namespace,
            'version'   => $version,
        ];
    }

    public static function getLogInitiator($action, $type = 'log')
    {
        if ('log' === $type) {
            $title = ucwords(implode(' ', preg_split('/(?=[A-Z])/', $action)));
        } else {
            $title = ucwords(
                str_replace(
                    ['fluentform/integration_notify_', 'fluentform_', '_notification_feed', '_'],
                    ['', '', '', ' '],
                    $action
                )
            );
        }

        return $title;
    }
    
    public static function getIpinfo()
    {
        return ArrayHelper::get(get_option('_fluentform_global_form_settings'), 'misc.geo_provider_token');
    }
    
    public static function isAutoloadCaptchaEnabled()
    {
        return ArrayHelper::get(get_option('_fluentform_global_form_settings'), 'misc.autoload_captcha');
    }

    public static function maybeDecryptUrl($url)
    {
        $uploadDir = str_replace('/', '\/', FLUENTFORM_UPLOAD_DIR . '/temp');
        $pattern = "/(?<={$uploadDir}\/).*$/";
        preg_match($pattern, $url, $match);
        if (!empty($match)) {
            $url = str_replace($match[0], Protector::decrypt($match[0]), $url);
        }
        return $url;
    }
    
    public static function arrayFilterRecursive($arrayItems)
    {
        foreach ($arrayItems as $key => $item) {
            is_array($item) && $arrayItems[$key] = self::arrayFilterRecursive($item);
            if (empty($arrayItems[$key])) {
                unset($arrayItems[$key]);
            }
        }
        return $arrayItems;
    }
    
    public static function isBlockEditor()
    {
       return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit';
    }
    public static function resolveValidationRulesGlobalOption(&$field)
    {
        if (isset($field['fields']) && is_array($field['fields'])) {
            foreach ($field['fields'] as &$subField) {
                static::resolveValidationRulesGlobalOption($subField);
            }
        } else {
            if (ArrayHelper::get($field, 'settings.validation_rules')) {
                foreach ($field['settings']['validation_rules'] as $key => &$rule) {
                    if(!isset($rule['global'])) {
                        $rule['global'] = false;
                    }
                    $rule['global_message'] = static::getGlobalDefaultMessage($key);
                }
            }
        }
    }

    public static function getWhiteListedFields($formId)
    {
        $whiteListedFields = [
            '__fluent_form_embded_post_id',
            '_fluentform_' . $formId . '_fluentformnonce',
            '_wp_http_referer',
            'g-recaptcha-response',
            'h-captcha-response',
            'cf-turnstile-response',
            '__stripe_payment_method_id',
            '__ff_all_applied_coupons',
            '__entry_intermediate_hash',
        ];

        return apply_filters('fluentform/white_listed_fields', $whiteListedFields, $formId);
    }
}
