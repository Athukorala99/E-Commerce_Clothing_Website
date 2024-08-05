<?php

namespace FluentForm\App\Models;

use FluentForm\Framework\Support\Arr;
use FluentForm\App\Models\Traits\PredefinedForms;

class Form extends Model
{
    use PredefinedForms;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_forms';

    /**
     * A form has many form meta.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function formMeta()
    {
        return $this->hasMany(FormMeta::class, 'form_id', 'id');
    }

    /**
     * A form may have one form meta to determine if
     * the form is a regular or conversational one.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasOne
     */
    public function conversationalMeta()
    {
        return $this->hasOne(FormMeta::class, 'form_id', 'id')->where('meta_key', 'is_conversion_form');
    }

    /**
     * A form has many submissions.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'form_id', 'id');
    }

    /**
     * A form has many submission meta.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function submissionMeta()
    {
        return $this->hasMany(SubmissionMeta::class, 'form_id', 'id');
    }

    /**
     * A form has many entry details.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function entryDetails()
    {
        return $this->hasMany(EntryDetails::class, 'form_id', 'id');
    }

    /**
     * A form has many form analytics.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function formAnalytics()
    {
        return $this->hasMany(FormAnalytics::class, 'form_id', 'id');
    }

    /**
     * A form has many logs.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'form_id', 'id');
    }

    public static function prepare($attributes = [])
    {
        $now = current_time('mysql');

        $data = [
            'title'               => Arr::get($attributes, 'title', 'My New Form'),
            'status'              => Arr::get($attributes, 'status', 'published'),
            'appearance_settings' => Arr::get($attributes, 'appearance_settings'),
            'form_fields'         => Arr::get($attributes, 'form_fields'),
            'has_payment'         => Arr::get($attributes, 'has_payment', 0),
            'type'                => Arr::get($attributes, 'type', 'form'),
            'conditions'          => Arr::get($attributes, 'conditions'),
            'created_by'          => get_current_user_id(),
            'created_at'          => $now,
            'updated_at'          => $now,
        ];

        return apply_filters(
            'fluentform/form_store_attributes',
            array_filter($data)
        );
    }

    public static function getFormMeta($metaKey, $formId = null)
    {
        return FormMeta::retrieve($metaKey, $formId);
    }

    public static function getFormsDefaultSettings($formId = false)
    {
        $defaultSettings = [
            'confirmation' => [
                'redirectTo'           => 'samePage',
                'messageToShow'        => __('Thank you for your message. We will get in touch with you shortly', 'fluentform'),
                'customPage'           => null,
                'samePageFormBehavior' => 'hide_form',
                'customUrl'            => null,
            ],
            'restrictions' => [
                'limitNumberOfEntries' => [
                    'enabled'         => false,
                    'numberOfEntries' => null,
                    'period'          => 'total',
                    'limitReachedMsg' => __('Maximum number of entries exceeded.', 'fluentform'),
                ],
                'scheduleForm' => [
                    'enabled'      => false,
                    'start'        => null,
                    'end'          => null,
                    'selectedDays' => null,
                    'pendingMsg'   => __('Form submission is not started yet.', 'fluentform'),
                    'expiredMsg'   => __('Form submission is now closed.', 'fluentform'),
                ],
                'requireLogin' => [
                    'enabled'         => false,
                    'requireLoginMsg' => __('You must be logged in to submit the form.', 'flunetform'),
                ],
                'denyEmptySubmission' => [
                    'enabled' => false,
                    'message' => __('Sorry, you cannot submit an empty form. Let\'s hear what you wanna say.', 'fluentform'),
                ],
                'restrictForm' => [
                    'enabled' => false,
                    'fields' =>  [
                        'ip' => [
                            'status' => false,
                            'values' => '',
                            'message' => __('Sorry! You can\'t submit a form from your IP address.', 'fluentform'),
                            'validation_type' => 'fail_on_condition_met'
                        ],
                        'country' => [
                            'status' => false,
                            'values' => [],
                            'message' => __('Sorry! You can\'t submit a form the country you are residing.', 'fluentform'),
                            'validation_type' => 'fail_on_condition_met'
                        ],
                        'keywords' => [
                            'status' => false,
                            'values' => '',
                            'message' => __('Sorry! Your submission contains some restricted keywords.', 'fluentform')
                        ],
                    ]
                ]
            ],
            'layout' => [
                'labelPlacement'        => 'top',
                'helpMessagePlacement'  => 'with_label',
                'errorMessagePlacement' => 'inline',
                'cssClassName'          => '',
                'asteriskPlacement'     => 'asterisk-right',
            ],
            'delete_entry_on_submission' => 'no',
        ];

        if ($formId) {
            $value = static::getFormMeta('formSettings', $formId);

            if ($value) {
                $defaultSettings = wp_parse_args($value, $defaultSettings);
            }
        } else {
            $globalSettings = get_option('_fluentform_global_form_settings');

            if (isset($globalSettings['layout'])) {
                $defaultSettings['layout'] = $globalSettings['layout'];
            }
        }

        $defaultSettings = apply_filters(
            'fluentform/forms_default_settings',
            $defaultSettings
        );
    
        $defaultSettings = apply_filters_deprecated(
            'fluentform_create_default_settings',
            [
                $defaultSettings
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/create_default_settings',
            'Use fluentform/create_default_settings instead of fluentform_create_default_settings.'
        );

        return apply_filters(
            'fluentform/create_default_settings',
            $defaultSettings
        );
    }

    public static function getAdvancedValidationSettings($formId)
    {
        $settings = [
            'status'     => false,
            'type'       => 'all',
            'conditions' => [
                [
                    'field'    => '',
                    'operator' => '=',
                    'value'    => '',
                ],
            ],
            'error_message'   => '',
            'validation_type' => 'fail_on_condition_met',
        ];

        $metaSettings = static::getFormMeta('advancedValidationSettings', $formId);

        if ($metaSettings && is_array($metaSettings)) {
            $settings = wp_parse_args($metaSettings, $settings);
        }

        return apply_filters(
            'fluentform/advanced_validation_settings',
            $settings
        );
    }

    public static function remove($formId)
    {
        static::where('id', $formId)->delete();

        Submission::where('form_id', $formId)->delete();
        SubmissionMeta::where('form_id', $formId)->delete();
        EntryDetails::where('form_id', $formId)->delete();
        FormMeta::where('form_id', $formId)->delete();
        FormAnalytics::where('form_id', $formId)->delete();
        Log::where('parent_source_id', $formId)
            ->whereIn('source_type', [
                'submission_item', 'form_item', 'draft_submission_meta',
            ])
            ->delete();

        if (defined('FLUENTFORMPRO')) {
            wpFluent()->table('fluentform_order_items')
                ->where('form_id', $formId)
                ->delete();

            wpFluent()->table('fluentform_transactions')
                ->where('form_id', $formId)
                ->delete();

            wpFluent()->table('fluentform_subscriptions')
                ->where('form_id', $formId)
                ->delete();
        }
    }
}
