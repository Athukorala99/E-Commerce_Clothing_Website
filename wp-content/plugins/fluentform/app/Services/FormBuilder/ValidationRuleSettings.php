<?php

/**
 * validation_rule_settings
 *
 * Returns an array of countries and codes.
 *
 * @author      WooThemes
 *
 * @category    i18n
 * @package     fluentform/i18n
 *
 * @version     2.5.0
 */
if (! defined('ABSPATH')) {
    exit;
}

$fileTypeOptions = [
    [
        'label' => __('Images (jpg, jpeg, gif, png, bmp)', 'fluentform'),
        'value' => 'jpg|jpeg|gif|png|bmp',
    ],
    [
        'label' => __('Audio (mp3, wav, ogg, oga, wma, mka, m4a, ra, mid, midi)', 'fluentform'),
        'value' => 'mp3|wav|ogg|oga|wma|mka|m4a|ra|mid|midi|mpga',
    ],
    [
        'label' => __('Video (avi, divx, flv, mov, ogv, mkv, mp4, m4v, divx, mpg, mpeg, mpe)', 'fluentform'),
        'value' => 'avi|divx|flv|mov|ogv|mkv|mp4|m4v|divx|mpg|mpeg|mpe|video/quicktime|qt',
    ],
    [
        'label' => __('PDF (pdf)', 'fluentform'),
        'value' => 'pdf',
    ],
    [
        'label' => __('Docs (doc, ppt, pps, xls, mdb, docx, xlsx, pptx, odt, odp, ods, odg, odc, odb, odf, rtf, txt)', 'fluentform'),
        'value' => 'doc|ppt|pps|xls|mdb|docx|xlsx|pptx|odt|odp|ods|odg|odc|odb|odf|rtf|txt',
    ],
    [
        'label' => __('Zip Archives (zip, gz, gzip, rar, 7z)', 'fluentform'),
        'value' => 'zip|gz|gzip|rar|7z',
    ],
    [
        'label' => __('Executable Files (exe)', 'fluentform'),
        'value' => 'exe',
    ],
    [
        'label' => __('CSV (csv)', 'fluentform'),
        'value' => 'csv',
    ],
];

$fileTypeOptions = apply_filters_deprecated(
    'fluentform_file_type_options',
    [
        $fileTypeOptions
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/file_type_options',
    'Use fluentform/file_type_options instead of fluentform_file_type_options.'
);

$fileTypeOptions = apply_filters('fluentform/file_type_options', $fileTypeOptions);

$validation_rule_settings = [
    'required' => [
        'template'  => 'inputRadio',
        'label'     => __('Required', 'fluentform'),
        'help_text' => __('Select whether this field is a required field for the form or not.', 'fluentform'),
        'options'   => [
            [
                'value' => true,
                'label' => __('Yes', 'fluentform'),
            ],
            [
                'value' => false,
                'label' => __('No', 'fluentform'),
            ],
        ],
    ],
    'valid_phone_number' => [
        'template'  => 'inputRadio',
        'label'     => __('Validate Phone Number', 'fluentform'),
        'help_text' => __('Select whether the phone number should be validated or not.', 'fluentform'),
        'options'   => [
            [
                'value' => true,
                'label' => __('Yes', 'fluentform'),
            ],
            [
                'value' => false,
                'label' => __('No', 'fluentform'),
            ],
        ],
    ],
    'email' => [
        'template'  => 'inputRadio',
        'label'     => __('Validate Email', 'fluentform'),
        'help_text' => __('Select whether to validate this field as email or not', 'fluentform'),
        'options'   => [
            [
                'value' => true,
                'label' => __('Yes', 'fluentform'),
            ],
            [
                'value' => false,
                'label' => __('No', 'fluentform'),
            ],
        ],
    ],
    'url' => [
        'template'  => 'inputRadio',
        'label'     => __('Validate URL', 'fluentform'),
        'help_text' => __('Select whether to validate this field as URL or not', 'fluentform'),
        'options'   => [
            [
                'value' => true,
                'label' => __('Yes', 'fluentform'),
            ],
            [
                'value' => false,
                'label' => __('No', 'fluentform'),
            ],
        ],
    ],
    'min' => [
        'template'  => 'inputText',
        'type'      => 'number',
        'label'     => __('Min Value', 'fluentform'),
        'help_text' => __('Minimum value for the input field.', 'fluentform'),
    ],
    'digits' => [
        'template'  => 'inputText',
        'type'      => 'number',
        'label'     => __('Digits', 'fluentform'),
        'help_text' => __('Number of digits for the input field.', 'fluentform'),
    ],
    'max' => [
        'template'  => 'inputText',
        'type'      => 'number',
        'label'     => __('Max Value', 'fluentform'),
        'help_text' => __('Maximum value for the input field.', 'fluentform'),
    ],
    'max_file_size' => [
        'template'  => 'maxFileSize',
        'label'     => __('Max File Size', 'fluentform'),
        'help_text' => __('The file size limit uploaded by the user.', 'fluentform'),
    ],
    'max_file_count' => [
        'template'  => 'inputText',
        'type'      => 'number',
        'label'     => __('Max Files Count', 'fluentform'),
        'help_text' => __('Maximum user file upload number.', 'fluentform'),
    ],
    'allowed_file_types' => [
        'template'  => 'inputCheckbox',
        'label'     => __('Allowed Files', 'fluentform'),
        'help_text' => __('Allowed Files', 'fluentform'),
        'fileTypes' => [
            [
                'title' => __('Images', 'fluentform'),
                'types' => ['jpg', 'jpeg', 'gif', 'png', 'bmp'],
            ],
            [
                'title' => __('Audio', 'fluentform'),
                'types' => ['mp3', 'wav', 'ogg', 'oga', 'wma', 'mka', 'm4a', 'ra', 'mid', 'midi'],
            ],
            [
                'title' => __('Video', 'fluentform'),
                'types' => [
                    'avi',
                    'divx',
                    'flv',
                    'mov',
                    'ogv',
                    'mkv',
                    'mp4',
                    'm4v',
                    'divx',
                    'mpg',
                    'mpeg',
                    'mpe',
                ],
            ],
            [
                'title' => __('PDF', 'fluentform'),
                'types' => ['pdf'],
            ],
            [
                'title' => __('Docs', 'fluentform'),
                'types' => [
                    'doc',
                    'ppt',
                    'pps',
                    'xls',
                    'mdb',
                    'docx',
                    'xlsx',
                    'pptx',
                    'odt',
                    'odp',
                    'ods',
                    'odg',
                    'odc',
                    'odb',
                    'odf',
                    'rtf',
                    'txt',
                ],
            ],
            [
                'title' => __('Zip Archives', 'fluentform'),
                'types' => ['zip', 'gz', 'gzip', 'rar', '7z', ],
            ],
            [
                'title' => __('Executable Files', 'fluentform'),
                'types' => ['exe'],
            ],
            [
                'title' => __('CSV', 'fluentform'),
                'types' => ['csv'],
            ],
        ],
        'options' => $fileTypeOptions,
    ],
    'allowed_image_types' => [
        'template'  => 'inputCheckbox',
        'label'     => __('Allowed Images', 'fluentform'),
        'help_text' => __('Allowed Images', 'fluentform'),
        'fileTypes' => [
            [
                'title' => __('JPG', 'fluentform'),
                'types' => ['jpg|jpeg', ],
            ],
            [
                'title' => __('PNG', 'fluentform'),
                'types' => ['png'],
            ],
            [
                'title' => __('GIF', 'fluentform'),
                'types' => ['gif'],
            ],
        ],
        'options' => [
            [
                'label' => __('JPG', 'fluentform'),
                'value' => 'jpg|jpeg',
            ],
            [
                'label' => __('PNG', 'fluentform'),
                'value' => 'png',
            ],
            [
                'label' => __('GIF', 'fluentform'),
                'value' => 'gif',
            ],
        ],
    ],
];

$validation_rule_settings = apply_filters_deprecated(
    'fluent_editor_validation_rule_settings',
    [
        $validation_rule_settings
    ],
    FLUENTFORM_FRAMEWORK_UPGRADE,
    'fluentform/editor_validation_rule_settings',
    'Use fluentform/editor_validation_rule_settings instead of fluent_editor_validation_rule_settings.'
);

return apply_filters('fluentform/editor_validation_rule_settings', $validation_rule_settings);
