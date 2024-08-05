<?php

namespace FluentForm\App\Services;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\Form;

class GlobalSearchService
{

    public function get()
    {
        $links = [
            [
                "title" => 'Forms',
                "icon"  => '',
                "path"  => '?page=fluent_forms',
                "tags"  => ['all', 'forms', 'dashboard']
            ],
            [
                "title" => 'Forms -> New Form',
                "icon"  => '',
                "path"  => '?page=fluent_forms#add=1',
                "tags"  => ['new forms', 'add forms', 'create']
            ],
            [
                "title" => 'Entries',
                "icon"  => '',
                "path"  => '?page=fluent_forms_all_entries',
                "tags"  => ['all', 'entries']
            ],
            [
                "title" => 'Support',
                "icon"  => '',
                "path"  => '?page=fluent_forms_docs',
                "tags"  => ['support', 'modules', 'docs']
            ],
            [
                "title" => 'Integrations -> Modules',
                "icon"  => '',
                "path"  => '?page=fluent_forms_add_ons',
                "tags"  => ['all', 'integrations', 'modules']
            ],
            [
                "title" => 'Global Settings > General',
                "icon"  => '',
                "path"  => '?page=fluent_forms_settings#settings',
                "tags"  => [
                    'global',
                    'settings',
                    'general',
                    'layout',
                    'email summaries',
                    'failure',
                    'email notification',
                    'miscellaneous'
                ]
            ],
            [
                "title" => 'Global Settings > Security > rCaptcha',
                "icon"  => '',
                "path"  => '?page=fluent_forms_settings#re_captcha',
                "tags"  => ['global', 'security', 'recaptcha']
            ],
            [
                "title" => 'Global Settings > Security > hCaptcha',
                "icon"  => '',
                "path"  => '?page=fluent_forms_settings#h_captcha',
                "tags"  => ['global', 'security', 'hcaptcha']
            ],
            [
                "title" => 'Global Settings > Security > Turnstile',
                "icon"  => '',
                "path"  => '?page=fluent_forms_settings#turnstile',
                "tags"  => ['global', 'security', 'turnstile']
            ],
            [
                "title" => 'Global Settings > Managers',
                "icon"  => '',
                "path"  => '?page=fluent_forms_settings#managers',
                "tags"  => ['global', 'permissions', 'managers']
            ],
            [
                "title" => 'Global Settings > Configure Integration -> Mailchimp',
                "icon"  => '',
                "path"  => '?page=fluent_forms_settings#general-mailchimp-settings',
                "tags"  => ['global integrations', 'mailchimp']
            ],
            [
                "title" => 'Tools > Import forms',
                "icon"  => '',
                "path"  => '?page=fluent_forms_transfer#importforms',
                "tags"  => ['tools', 'migration', 'transfer', 'import']
            ],
            [
                "title" => 'Tools > Export Forms',
                "icon"  => '',
                "path"  => '?page=fluent_forms_transfer#exportsforms',
                "tags"  => ['tools', 'migration', 'transfer', 'export']
            ],
            [
                "title" => 'Tools > Migrator',
                "icon"  => '',
                "path"  => '?page=fluent_forms_transfer#migrator',
                "tags"  => ['tools', 'migration', 'transfer', 'migrator']
            ],
            [
                "title" => 'Tools > Activity Logs',
                "icon"  => '',
                "path"  => '?page=fluent_forms_transfer#activitylogs',
                "tags"  => ['tools', 'activity logs']
            ],
            [
                "title" => 'Tools > API Logs',
                "icon"  => '',
                "path"  => '?page=fluent_forms_transfer#apilogs',
                "tags"  => ['tools', 'api logs']
            ],
        ];

        if (defined('FLUENTFORMPRO')) {
            $links = array_merge($links, [
                [
                    "title" => 'Global Settings > Double Opt-in',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#double_optin_settings',
                    "tags"  => ['global', 'security', 'double opt-in', 'optin']
                ],
                [
                    "title" => 'Payments',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_payment_entries',
                    "tags"  => ['all', 'payments', 'entries']
                ],
                [
                    "title" => 'Global Settings > Payment > Settings',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings&component=payment_settings%2F#/',
                    "tags"  => ['global', 'settings', 'payment']
                ],
                [
                    "title" => 'Global Settings > Payment > Coupons',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings&component=payment_settings%2F#/coupons',
                    "tags"  => ['global', 'settings', 'payment', 'coupons']
                ],
                [
                    "title" => 'Global Settings > Payment > Payment Methods',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings&component=payment_settings%2F#/payment_methods',
                    "tags"  => ['global', 'settings', 'payment', 'method', 'stripe']
                ],
                [
                    "title" => 'Global Settings > License',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings&component=license_page',
                    "tags"  => ['global', 'license']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Google Map Integration',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#google_maps_autocomplete',
                    "tags"  => ['global', 'integrations', 'google map integration']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Activecampaign',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-activecampaign-settings',
                    "tags"  => ['global integrations', 'activecampaign']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Campaign Monitor',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-campaign_monitor-settings',
                    "tags"  => ['global integrations', 'campaign monitor']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Constatant Contact',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-constatantcontact-settings',
                    "tags"  => ['global integrations', 'constatantcontact', 'constatant contact']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> ConvertKit',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-convertkit-settings',
                    "tags"  => ['global integrations', 'convertkit']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> GetResponse',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-getresponse-settings',
                    "tags"  => ['global integrations', 'getresponse']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Hubspot',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-hubspot-settings',
                    "tags"  => ['global integrations', 'hubspot']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> iContact',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-icontact-settings',
                    "tags"  => ['global integrations', 'icontact']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> MooSend',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-moosend-settings',
                    "tags"  => ['global integrations', 'moosend']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Platformly',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-platformly-settings',
                    "tags"  => ['global integrations', 'platformly']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> SendFox',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-sendfox-settings',
                    "tags"  => ['global integrations', 'sendfox']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> MailerLite',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-mailerlite-settings',
                    "tags"  => ['global integrations', 'mailerlite']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> MooSend',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-moosend-settings',
                    "tags"  => ['global integrations', 'moosend']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Twilio',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-sms_notification-settings',
                    "tags"  => ['global integrations', 'sms notification', 'twilio']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> GetGist',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-getgist-settings',
                    "tags"  => ['global integrations', 'getgist']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Google Sheet',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-google_sheet-settings',
                    "tags"  => ['global integrations', 'google sheet']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Trello',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-trello-settings',
                    "tags"  => ['global integrations', 'trello']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Drip',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-drip-settings',
                    "tags"  => ['global integrations', 'drip']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Sendinblue',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-sendinblue-settings',
                    "tags"  => ['global integrations', 'sendinblue']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Automizy',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-automizy-settings',
                    "tags"  => ['global integrations', 'automizy']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Telegram Messenger',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-telegram-settings',
                    "tags"  => ['global integrations', 'telegram messenger']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Salesflare',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-salesflare-settings',
                    "tags"  => ['global integrations', 'salesflare']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> CleverReach',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-cleverreach-settings',
                    "tags"  => ['global integrations', 'cleverreach']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> ClickSend',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-clicksend_sms_notification-settings',
                    "tags"  => ['global integrations', 'clicksend', 'sms_notification']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Zoho CRM',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-zohocrm-settings',
                    "tags"  => ['global integrations', 'zoho crm']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Pipedrive',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-pipedrive-settings',
                    "tags"  => ['global integrations', 'pipedrive']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Salesforce',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-salesforce-settings',
                    "tags"  => ['global integrations', 'salesforce']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Amocrm',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-amocrm-settings',
                    "tags"  => ['global integrations', 'amocrm']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> OnePageCrm',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-onepagecrm-settings',
                    "tags"  => ['global integrations', 'onepagecrm']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Airtable',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-airtable-settings',
                    "tags"  => ['global integrations', 'airtable']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Mailjet',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-mailjet-settings',
                    "tags"  => ['global integrations', 'mailjet']
                ],
                [
                    "title" => 'Global Settings > Configure Integration -> Insightly',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#general-insightly-settings',
                    "tags"  => ['global integrations', 'insightly']
                ]
            ]);
        }

        if (defined('FLUENTFORM_PDF_VERSION')) {
            $links = array_merge($links, [
                [
                    "title" => 'Global Settings > Configure Integration -> PDF Settings',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_settings#pdf_settings',
                    "tags"  => ['global integrations', 'pdf settings']
                ],
                [
                    "title" => 'Integrations -> Fluent Forms PDF',
                    "icon"  => '',
                    "path"  => '?page=fluent_forms_add_ons&sub_page=fluentform_pdf',
                    "tags"  => ['pdf', 'modules']
                ]
            ]);
        }


        $forms = Form::where('status', 'published')
            ->select(['id', 'title', 'type'])->get();
        if ($forms) {
            foreach ($forms as $form) {
                $formSpecificLinks = [
                    [
                        "title" => "Forms > $form->title > Editor",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=editor",
                        "tags"  => ['editor', "$form->id", $form->title]
                    ],
                    [
                        "title" => "Forms > $form->title > Entries",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=entries#/?sort_by=DESC&type=&page=1",
                        "tags"  => ['entries', "$form->id", $form->title]
                    ],
                    [
                        "title" => "Forms > $form->title > Entries > Visual Reports",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=entries#/visual_reports",
                        "tags"  => ['entries', 'visual reports', "$form->id", $form->title]
                    ],
                    [
                        "title" => "Forms > $form->title > Settings & Integrations > Settings",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/",
                        "tags"  => ['settings and integrations', "$form->id", $form->title]
                    ],
                    [
                        "title" => "Forms > $form->title > Settings & Integrations > Slack",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=2&route=settings&sub_route=form_settings#/slack",
                        "tags"  => ['slack', "$form->id", $form->title]
                    ],
                    [
                        "title" => "Forms > $form->title > Settings & Integrations > Custom CSS/JS",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/custom-css-js",
                        "tags"  => ['custom', 'CSS/JS', 'css javascript', "$form->id", $form->title]
                    ],
                    [
                        "title" => "Forms > $form->title > Settings & Integrations > Configure Integrations",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/all-integrations",
                        "tags"  => ['configure integrations', "$form->id", $form->title]
                    ],
                    [
                        "title" => "Forms > $form->title > Preview",
                        "icon"  => '',
                        "type"  => 'preview',
                        "path"  => "?fluent_forms_pages=1&design_mode=1&preview_id=$form->id#ff_preview",
                        "tags"  => ['preview ', "$form->id", $form->title]
                    ]
                ];
                if (defined('FLUENTFORMPRO')) {
                    $formSpecificLinks = array_merge($formSpecificLinks, [
                        [
                            "title" => "Forms > $form->title > Settings & Integrations > Landing Page",
                            "icon"  => '',
                            "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/landing_pages",
                            "tags"  => ['landing pages', "$form->id", $form->title]
                        ],
                        [
                            "title" => "Forms > $form->title > Settings & Integrations > Conditional Confirmations",
                            "icon"  => '',
                            "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/conditional-confirmations",
                            "tags"  => ["conditional confirmations", "$form->id", $form->title]
                        ],
                        [
                            "title" => "Forms > $form->title > Settings & Integrations > Email Notifications",
                            "icon"  => '',
                            "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/email-settings",
                            "tags"  => ["email notifications", "$form->id", $form->title]
                        ],
                        [
                            "title" => "Forms > $form->title > Settings & Integrations > Zapier",
                            "icon"  => '',
                            "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/zapier",
                            "tags"  => ['zapier', "$form->id", $form->title]
                        ],
                        [
                            "title" => "Forms > $form->title > Settings & Integrations > Webhook",
                            "icon"  => '',
                            "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/webhook",
                            "tags"  => ['webhook', "$form->id", $form->title]
                        ],
                        [
                            "title" => "Forms > $form->title > Settings & Integrations > Quiz Settings",
                            "icon"  => '',
                            "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/quiz_settings",
                            "tags"  => ["quiz", "$form->id", $form->title]
                        ]
                    ]);
                }
                if (defined('FLUENTFORM_PDF_VERSION')) {
                    $formSpecificLinks[] = [
                        "title" => "Forms > $form->title > Settings & Integrations > PDF Feeds",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=settings&sub_route=form_settings#/pdf-feeds",
                        "tags"  => ['pdf feeds', "$form->id", $form->title]
                    ];
                }

                if (defined('FLUENTFORMPRO') && 'post' === $form->type) {
                    $formSpecificLinks[] = [
                        "title" => "Forms > $form->title > Settings & Integrations > Post Feeds",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=104&route=settings&sub_route=form_settings#/post-feeds",
                        "tags"  => ['post feeds', "$form->id", $form->title]
                    ];
                }
                if (Helper::isConversionForm($form->id)) {
                    $formSpecificLinks[] = [
                        "title" => "Forms > $form->title > Design",
                        "icon"  => '',
                        "path"  => "?page=fluent_forms&form_id=$form->id&route=conversational_design",
                        "tags"  => ['conversational design', "$form->id", $form->title]
                    ];
                    $formSpecificLinks[] = [
                        "title" => "Forms > $form->title > Conversational Preview",
                        "icon"  => '',
                        "type"  => 'preview',
                        "path"  => "?fluent-form=$form->id",
                        "tags"  => ['preview', 'conversational', "$form->id", $form->title]
                    ];
                }
                $links = array_merge($links, $formSpecificLinks);
            }
        }
        return [
            "links" => apply_filters('fluentform/global_search_links', $links),
            "admin_url" => get_admin_url(null, 'admin.php'),
            "site_url" => site_url(),
        ];
    }
}
