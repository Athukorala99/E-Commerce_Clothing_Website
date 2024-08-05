<?php

namespace FluentForm\App\Services\Form;

use Exception;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\FormMeta;
use FluentForm\Framework\Foundation\App;
use FluentForm\Framework\Request\File;
use FluentForm\Framework\Support\Arr;
use FluentForm\Framework\Foundation\Application;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\FluentConversational\Classes\Converter\Converter;

class FormService
{
    /**
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app;

    /**
     * @var \FluentForm\App\Models\Form|\FluentForm\Framework\Database\Query\Builder
     */
    protected $model;

    /**
     * @var \FluentForm\App\Services\Form\Updater
     */
    protected $updater;

    /**
     * @var \FluentForm\App\Services\Form\Duplicator
     */
    protected $duplicator;

    /**
     * @var \FluentForm\App\Services\Form\Fields
     */
    protected $fields;
    
    public function __construct()
    {
        $this->model = new Form();
        $this->fields = new Fields();
        $this->app = App::getInstance();
        $this->updater = new Updater();
        $this->duplicator = new Duplicator();
    }

    /**
     * Get the paginated forms matching search criteria.
     *
     * @param  array $attributes
     * @return array
     */
    public function get($attributes = [])
    {
        return fluentFormApi('forms')->forms([
            'search'      => Arr::get($attributes, 'search'),
            'status'      => Arr::get($attributes, 'status'),
            'filter_by'   => Arr::get($attributes, 'filter_by', 'all'),
            'date_range'  => Arr::get($attributes, 'date_range', []),
            'sort_column' => Arr::get($attributes, 'sort_column', 'id'),
            'sort_by'     => Arr::get($attributes, 'sort_by', 'DESC'),
            'per_page'    => Arr::get($attributes, 'per_page', 10),
            'page'        => Arr::get($attributes, 'page', 1),
        ]);
    }

    /**
     * Store a form with its associated meta.
     *
     * @param  array                       $attributes
     * @throws Exception
     * @return \FluentForm\App\Models\Form $form
     */
    public function store($attributes = [])
    {
        try {
            $predefinedForm = Form::resolvePredefinedForm($attributes);

            $data = Form::prepare($predefinedForm);

            $form = $this->model->create($data);

            $form->title = $form->title . ' (#' . $form->id . ')';

            $form->save();

            $formMeta = FormMeta::prepare($attributes, $predefinedForm);

            FormMeta::store($form, $formMeta);

            do_action_deprecated(
                'fluentform_inserted_new_form',
                [
                    $form->id,
                    $data
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/inserted_new_form',
                'Use fluentform/inserted_new_form instead of fluentform_inserted_new_form.'
            );

            do_action('fluentform/inserted_new_form', $form->id, $data);

            return $form;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Duplicate a form with its associated meta.
     *
     * @param  array                       $attributes
     * @throws Exception
     * @return \FluentForm\App\Models\Form $form
     */
    public function duplicate($attributes = [])
    {
        $formId = Arr::get($attributes, 'form_id');

        $existingForm = $this->model->with([
            'formMeta' => function ($formMeta) {
                return $formMeta->whereNotIn('meta_key', ['_total_views']);
            },
        ])->find($formId);

        if (!$existingForm) {
            throw new Exception(
                __("The form couldn't be found.", 'fluentform')
            );
        }

        $data = Form::prepare($existingForm->toArray());

        $form = $this->model->create($data);

        // Rename the form name here
        $form->title = $form->title . ' (#' . $form->id . ')';
        $form->save();

        $this->duplicator->duplicateFormMeta($form, $existingForm);
        $this->duplicator->maybeDuplicateFiles($form, $existingForm, $data);

        do_action_deprecated(
            'fluentform_form_duplicated',
            [
                $form->id
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/form_duplicated',
            'Use fluentform/form_duplicated instead of fluentform_form_duplicated.'
        );
        do_action('fluentform/form_duplicated', $form->id);

        return $form;
    }

    public function find($id)
    {
        try {
            return $this->model->with('formMeta')->findOrFail($id);
        } catch (Exception $e) {
            throw new Exception(
                __("The form couldn't be found.", 'fluentform')
            );
        }
    }

    public function delete($id)
    {
        Form::remove($id);
    }

    /**
     * Update a form with its relevant fields.
     *
     * @param  array                       $attributes
     * @throws Exception
     * @return \FluentForm\App\Models\Form $form
     */
    public function update($attributes = [])
    {
        return $this->updater->update($attributes);
    }

    /**
     * Duplicate a form with its associated meta.
     *
     * @param  int                         $id
     * @throws Exception
     * @return \FluentForm\App\Models\Form $form
     */
    public function convert($id)
    {
        try {
            $form = Form::with('conversationalMeta')->findOrFail($id);
        } catch (Exception $e) {
            throw new Exception(
                __("The form couldn't be found.", 'fluentform')
            );
        }

        $isConversationalForm = $form->conversationalMeta && 'yes' === $form->conversationalMeta->value;

        if ($isConversationalForm) {
            $conversationalMetaValue = 'no';
        } else {
            $form->fill([
                'form_fields' => Converter::convertExistingForm($form),
            ])->save();

            $conversationalMetaValue = 'yes';
        }

        FormMeta::persist($form->id, 'is_conversion_form', $conversationalMetaValue);

        return $form;
    }

    public function templates()
    {
        $forms = [
            'Basic' => [],
        ];

        $predefinedForms = $this->model::getPredefinedForms();

        foreach ($predefinedForms as $key => $item) {
            if (!$item['category']) {
                $item['category'] = 'Other';
            }

            if (!isset($forms[$item['category']])) {
                $forms[$item['category']] = [];
            }

            $itemClass = 'item_' . str_replace([' ', '&', '/'], '_', strtolower($item['category']));

            if (empty($item['screenshot'])) {
                $itemClass .= ' item_no_image';
            } else {
                $itemClass .= ' item_has_image';
            }

            $forms[$item['category']][$key] = [
                'class'      => $itemClass,
                'tags'       => Arr::get($item, 'tag', ''),
                'title'      => $item['title'],
                'brief'      => $item['brief'],
                'category'   => $item['category'],
                'screenshot' => $item['screenshot'],
                'createable' => $item['createable'],
                'is_pro'     => Arr::get($item, 'is_pro'),
                'type'       => isset($item['type']) ? $item['type'] : 'form',
            ];
        }
        $dropDownForms = [
            'post' => [
                'title' => 'Post Form',
            ],
        ];
        $dropDownForms = apply_filters_deprecated(
            'fluentform-predefined-dropDown-forms',
            [
                $dropDownForms
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/predefined_dropdown_forms',
            'Use fluentform/predefined_dropdown_forms instead of fluentform-predefined-dropDown-forms.'
        );

        return [
            'forms'                     => $forms,
            'categories'                => array_keys($forms),
            'predefined_dropDown_forms' => apply_filters('fluentform/predefined_dropdown_forms', $dropDownForms),
        ];
    }

    public function components($formId)
    {
        /**
         * @var \FluentForm\App\Services\FormBuilder\Components
         */
        $components = $this->app->make('components');

        do_action_deprecated(
            'fluent_editor_init',
            [
                $components
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/editor_init',
            'Use fluentform/editor_init instead of fluent_editor_init.'
        );

        $this->app->doAction('fluentform/editor_init', $components);

        $editorComponents = $components->sort()->toArray();
    
        $editorComponents = apply_filters_deprecated(
            'fluent_editor_components',
            [
                $editorComponents,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/editor_components',
            'Use fluentform/editor_components instead of fluent_editor_components.'
        );

        return apply_filters('fluentform/editor_components', $editorComponents, $formId);
    }

    public function getDisabledComponents()
    {
        $isReCaptchaDisabled = !get_option('_fluentform_reCaptcha_keys_status', false);
        $isHCaptchaDisabled = !get_option('_fluentform_hCaptcha_keys_status', false);
        $isTurnstileDisabled = !get_option('_fluentform_turnstile_keys_status', false);

        $disabled = [
            'recaptcha' => [
                'disabled'    => $isReCaptchaDisabled,
                'title'       => __('reCaptcha', 'fluentform'),
                'description' => __('Please enter a valid API key on FluentForms->Settings->reCaptcha', 'fluentform'),
                'hidePro'     => true,
            ],
            'hcaptcha' => [
                'disabled'    => $isHCaptchaDisabled,
                'title'       => __('hCaptcha', 'fluentform'),
                'description' => __('Please enter a valid API key on FluentForms->Settings->hCaptcha', 'fluentform'),
                'hidePro'     => true,
            ],
            'turnstile' => [
                'disabled'    => $isTurnstileDisabled,
                'title'       => __('Turnstile', 'fluentform'),
                'description' => __('Please enter a valid API key on FluentForms->Settings->Turnstile', 'fluentform'),
                'hidePro'     => true,
            ],
            'input_image' => [
                'disabled'    => true,
                'title'       => __('Image Upload', 'fluentform'),
                'description' => __('Image Upload is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/Yb3FSoZl9Zg',
            ],
            'input_file' => [
                'disabled'    => true,
                'title'       => __('File Upload', 'fluentform'),
                'description' => __('File Upload is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/bXbTbNPM_4k',
            ],
            'shortcode' => [
                'disabled'    => true,
                'title'       => __('Shortcode', 'fluentform'),
                'description' => __('Shortcode is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/op3mEQxX1MM',
            ],
            'action_hook' => [
                'disabled'    => true,
                'title'       => __('Action Hook', 'fluentform'),
                'description' => __('Action Hook is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/action-hook.png'),
                'video'       => '',
            ],
            'form_step' => [
                'disabled'    => true,
                'title'       => __('Form Step', 'fluentform'),
                'description' => __('Form Step is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/VQTWnM6BbRU',
            ],
        ];

        if (!defined('FLUENTFORMPRO')) {
            $disabled['ratings'] = [
                'disabled'    => true,
                'title'       => __('Ratings', 'fluentform'),
                'description' => __('Ratings is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/YGdkNspMaEs',
            ];
            $disabled['tabular_grid'] = [
                'disabled'    => true,
                'title'       => __('Checkable Grid', 'fluentform'),
                'description' => __('Checkable Grid is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/ayI3TzXXANA',
            ];
            $disabled['chained_select'] = [
                'disabled'    => true,
                'title'       => __('Chained Select Field', 'fluentform'),
                'description' => __('Chained Select Field is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/chained-select-field.png'),
                'video'       => '',
            ];
            $disabled['phone'] = [
                'disabled'    => true,
                'title'       => 'Phone Field',
                'description' => __('Phone Field is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/phone-field.png'),
                'video'       => '',
            ];
            $disabled['rich_text_input'] = [
                'disabled'    => true,
                'title'       => __('Rich Text Input', 'fluentform'),
                'description' => __('Rich Text Input is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/rich-text-input.png'),
                'video'       => '',
            ];
            $disabled['save_progress_button'] = [
                'disabled'    => true,
                'title'       => __('Save & Resume', 'fluentform'),
                'description' => __('Save & Resume is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/save-progress-button.png'),
                'video'       => '',
            ];
            $disabled['cpt_selection'] = [
                'disabled'    => true,
                'title'       => __('Post/CPT Selection', 'fluentform'),
                'description' => __('Post/CPT Selection is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/post-cpt-selection.png'),
                'video'       => '',
            ];
            $disabled['quiz_score'] = [
                'disabled'    => true,
                'title'       => __('Quiz Score', 'fluentform'),
                'description' => __('Quiz Score is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/bPjDXR0y_Oo',
            ];
            $disabled['net_promoter_score'] = [
                'disabled'    => true,
                'title'       => __('Net Promoter Score', 'fluentform'),
                'description' => __('Net Promoter Score is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/net-promoter-score.png'),
                'video'       => '',
            ];
            $disabled['repeater_field'] = [
                'disabled'    => true,
                'title'       => __('Repeat Field', 'fluentform'),
                'description' => __('Repeat Field is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/BXo9Sk-OLnQ',
            ];
            $disabled['rangeslider'] = [
                'disabled'    => true,
                'title'       => __('Range Slider', 'fluentform'),
                'description' => __('Range Slider is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => '',
                'video'       => 'https://www.youtube.com/embed/RaY2VcPWk6I',
            ];
            $disabled['color-picker'] = [
                'disabled'    => true,
                'title'       => __('Color Picker', 'fluentform'),
                'description' => __('Color Picker is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/color-picker.png'),
                'video'       => '',
            ];
            $disabled['multi_payment_component'] = [
                'disabled'    => true,
                'is_payment'  => true,
                'title'       => __('Payment Field', 'fluentform'),
                'description' => __('Payment Field is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/payment-field.png'),
                'video'       => '',
            ];
            $disabled['custom_payment_component'] = [
                'disabled'    => true,
                'is_payment'  => true,
                'title'       => 'Custom Payment Amount',
                'description' => __('Custom Payment Amount is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/custom-payment-amount.png'),
                'video'       => '',
            ];
            $disabled['subscription_payment_component'] = [
                'disabled'    => true,
                'is_payment'  => true,
                'title'       => __('Subscription Field', 'fluentform'),
                'description' => __('Subscription Field is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/subscription-field.png'),
                'video'       => '',
            ];
            $disabled['item_quantity_component'] = [
                'disabled'    => true,
                'is_payment'  => true,
                'title'       => __('Item Quantity', 'fluentform'),
                'description' => __('Item Quantity is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/item-quantity.png'),
                'video'       => '',
            ];
            $disabled['payment_method'] = [
                'disabled'    => true,
                'is_payment'  => true,
                'title'       => __('Payment Method', 'fluentform'),
                'description' => __('Payment Method is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/payment-method.png'),
                'video'       => '',
            ];
            $disabled['payment_summary_component'] = [
                'disabled'    => true,
                'is_payment'  => true,
                'title'       => __('Payment Summary', 'fluentform'),
                'description' => __('Payment Summary is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/payment-summary.png'),
                'video'       => '',
            ];
            $disabled['payment_coupon'] = [
                'disabled'    => true,
                'title'       => __('Coupon', 'fluentform'),
                'description' => __('Coupon is not available with the free version. Please upgrade to pro to get all the advanced features.', 'fluentform'),
                'image'       => fluentformMix('img/pro-fields/coupon.png'),
                'video'       => '',
            ];
        }
    
        $disabled = apply_filters_deprecated(
            'fluentform_disabled_components',
            [
                $disabled
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/disabled_components',
            'Use fluentform/disabled_components instead of fluentform_disabled_components.'
        );

        return $this->app->applyFilters('fluentform/disabled_components', $disabled);
    }

    public function fields($id)
    {
        return $this->fields->get($id);
    }

    public function shortcodes($id)
    {
        return fluentFormGetAllEditorShortCodes($id);
    }

    public function pages()
    {
        return fluentformGetPages();
    }

    public function getInputsAndLabels($formId, $with = ['admin_label', 'raw'])
    {
        try {
            $form = $this->model->findOrFail($formId);

            $inputs = FormFieldsParser::getEntryInputs($form, $with);
            $labels = FormFieldsParser::getAdminLabels($form, $inputs);
    
            $labels = apply_filters_deprecated(
                'fluentfoform_entry_lists_labels',
                [
                    $labels,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/entry_lists_labels',
                'Use fluentform/entry_lists_labels instead of fluentfoform_entry_lists_labels.'
            );
            $labels = apply_filters('fluentform/entry_lists_labels', $labels, $form);
    
            $labels = apply_filters_deprecated(
                'fluentform_all_entry_labels',
                [
                    $labels,
                    $formId
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/all_entry_labels',
                'Use fluentform/all_entry_labels instead of fluentform_all_entry_labels.'
            );
            $labels = apply_filters('fluentform/all_entry_labels', $labels, $formId);

            if ($form->has_payment) {
                $labels = apply_filters_deprecated(
                    'fluentform_all_entry_labels_with_payment',
                    [
                        $labels,
                        false,
                        $form
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/all_entry_labels_with_payment',
                    'Use fluentform/all_entry_labels_with_payment instead of fluentform_all_entry_labels_with_payment.'
                );

                $labels = apply_filters('fluentform/all_entry_labels_with_payment', $labels, false, $form);
            }

            return [
                'inputs' => $inputs,
                'labels' => $labels,
            ];
        } catch (Exception $e) {
            throw new Exception(
                __("The form couldn't be found.", 'fluentform')
            );
        }
    }

    public function findShortCodePage($formId)
    {
        $excluded = ['attachment'];
        $post_types = get_post_types(['show_in_menu' => true], 'objects', 'or');
        $postTypes = [];
        foreach($post_types as $post_type) {
            $postTypeName = $post_type->name;
            if (in_array($postTypeName, $excluded)) {
                continue;
            }
            $postTypes[] = $postTypeName;
        }
    
        $params = array(
            'post_type'      => $postTypes,
            'posts_per_page' => -1
        );
    
        $params = apply_filters_deprecated(
            'fluentform_find_shortcode_params',
            [
                $params
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/find_shortcode_params',
            'Use fluentform/find_shortcode_params instead of fluentform_find_shortcode_params.'
        );
        $params = apply_filters('fluentform/find_shortcode_params', $params);

        $formLocations = [];
        $posts = get_posts($params);
        foreach($posts as $post) {
            $formIds = self::getShortCodeId($post->post_content);
            if(!empty($formIds) && in_array($formId,$formIds)) {
                $postType = get_post_type_object($post->post_type);
                $formLocations[] = [
                    'id'        => $post->ID,
                    'name'      => $postType->labels->singular_name,
                    'title'     => (empty($post->post_title) ? $post->ID : $post->post_title),
                    'edit_link' => sprintf("%spost.php?post=%s&action=edit", admin_url(), $post->ID),
                ];
            }
        }
        return [
            'locations' => $formLocations,
            'status'    => !empty($formLocations),
        ];
    }
    
    public static function getShortCodeId($content, $shortcodeTag = 'fluentform')
    {
        $ids = [];
        $selector = 'id';
        $formId = '';
        if (!function_exists('parse_blocks')) {
            return $ids;
        }
        $parsedBlocks = parse_blocks($content);

        foreach ($parsedBlocks as $block) {
            if (!array_key_exists('blockName', $block) || !array_key_exists('attrs', $block) || !array_key_exists('formId', $block['attrs'])) {
                continue;
            }
            $hasBlock = strpos($block['blockName'], 'fluentfom/guten-block') === 0;
            if (!$hasBlock) {
                continue;
            }
            $ids[] = (int) $block['attrs']['formId'];
        }
        // Define the regex pattern with a placeholder for any number
        $hasFormWidgets = false;
        $pattern = '/<form data-form_id="(\d+)" id="fluentform_(\d+)" data-form_instance="ff_form_instance_(\d+)_(\d+)" method="POST" ><fieldset /';
        // Perform the regex match
        if (preg_match($pattern, $content, $matches)) {
            $hasFormWidgets = isset($matches[0]);
            $ids[] = isset($matches[1]) ? $matches[1] : '';
        }

        if (!has_shortcode($content, $shortcodeTag) && !$hasFormWidgets) {
            return $ids;
        }

        preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return $ids;
        }

        foreach ($matches as $shortcode) {
            if (count($shortcode) >= 2 && $shortcodeTag === $shortcode[2]) {
                $parsedCode = str_replace(['[', ']', '&#91;', '&#93;'], '', $shortcode[0]);
                
                $result = shortcode_parse_atts($parsedCode);
                
                if (!empty($result[$selector])) {
                    $ids[] = $result[$selector];
                }
            }
        }
        return $ids;
    }
}
