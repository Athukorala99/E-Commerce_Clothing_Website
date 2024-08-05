<?php

namespace FluentForm\App\Services\Integrations\MailChimp;

use FluentForm\App\Services\ConditionAssesor;
use FluentForm\App\Services\Integrations\LogResponseTrait;
use FluentForm\Framework\Helpers\ArrayHelper;

trait MailChimpSubscriber
{
    use LogResponseTrait;

    /**
     * Enabled MailChimp feed settings.
     *
     * @var array $feeds
     */
    protected $feeds = [];

    /**
     * Required for api response logging
     *
     * @var string
     */
    protected $metaKey = 'fluentform_mailchimp_feed';

    /**
     * Form input data.
     *
     * @param array $formData
     */
    public function setApplicableFeeds($formData)
    {
        $feeds = $this->getAll();

        foreach ($feeds as $feed) {
            if ($this->isApplicable($feed, $formData)) {
                $email = ArrayHelper::get(
                    $formData,
                    ArrayHelper::get($feed->formattedValue, 'fieldEmailAddress')
                );

                if (is_string($email) && is_email($email)) {
                    $feed->formattedValue['fieldEmailAddress'] = $email;

                    $this->feeds[] = $feed;
                }
            }
        }
    }

    /**
     * Determine if the feed is eligible to be applied.
     *
     * @param $feed
     * @param $formData
     *
     * @return bool
     */
    public function isApplicable(&$feed, &$formData)
    {
        return ArrayHelper::get($feed->formattedValue, 'enabled') &&
            ArrayHelper::get($feed->formattedValue, 'list_id') &&
            ConditionAssesor::evaluate($feed->formattedValue, $formData);
    }

    /**
     * Subscribe a user to the list on form submission.
     *
     * @param $feed
     * @param $formData
     * @param $entry
     * @param $form
     *
     * @return array|bool|false
     *
     * @throws \Exception
     */
    public function subscribe($feed, $formData, $entry, $form)
    {
        $feedData = $feed['processedValues'];

        if (! is_email($feedData['fieldEmailAddress'])) {
            $feedData['fieldEmailAddress'] = ArrayHelper::get($formData, $feedData['fieldEmailAddress']);
        }

        if (! is_email($feedData['fieldEmailAddress'])) {
            return false;
        }

        $mergeFields = array_filter(ArrayHelper::get($feedData, 'merge_fields'));
        $status = ArrayHelper::isTrue($feedData, 'doubleOptIn') ? 'pending' : 'subscribed';

        $listId = $feedData['list_id'];

        $arguments = [
            'email_address' => $feedData['fieldEmailAddress'],
            'status_if_new' => $status,
            'double_optin'  => ArrayHelper::isTrue($feedData, 'doubleOptIn'),
            'vip'           => ArrayHelper::isTrue($feedData, 'markAsVIP'),
        ];

        if ($mergeFields) {
            // Process merge field for address
            $mergeFieldsSettings = $this->findMergeFields(
                ArrayHelper::get($feed, 'settings.list_id')
            );

            foreach ($mergeFieldsSettings as $fieldSettings) {
                if ('address' == $fieldSettings['type'] || 'birthday' == $fieldSettings['type']) {
                    $fieldName = $fieldSettings['tag'];

                    $formFieldName = ArrayHelper::get($feed, 'settings.merge_fields.' . $fieldName);

                    if ($formFieldName) {
                        preg_match('/{+(.*?)}/', $formFieldName, $matches);

                        $formFieldName = substr($matches[1], strlen('inputs.'));

                        $formFieldValue = ArrayHelper::get($formData, $formFieldName);

                        if ($formFieldValue) {
                            if('address' == $fieldSettings['type']) {
                                $mergeFields[$fieldName] = [
                                    'addr1'   => ArrayHelper::get($formFieldValue, 'address_line_1'),
                                    'city'    => ArrayHelper::get($formFieldValue, 'city'),
                                    'state'   => ArrayHelper::get($formFieldValue, 'state'),
                                    'zip'     => ArrayHelper::get($formFieldValue, 'zip'),
                                    'country' => ArrayHelper::get($formFieldValue, 'country'),
                                ];

                                if (ArrayHelper::exists($formFieldValue, 'address_line_2')) {
                                    $mergeFields[$fieldName]['addr2'] = ArrayHelper::get($formFieldValue, 'address_line_2');
                                }
                            }
                            else {
                                $mergeFields[$fieldName] = date('d/m', strtotime($formFieldValue));
                            }
                        }
                    }
                }
            }

            $arguments['merge_fields'] = (object) $mergeFields;
        }

        if ($entry->ip) {
            $arguments['ip_signup'] = $entry->ip;
        }

        $tags = $this->getSelectedTagIds($feedData, $formData, 'tags');
        if (! is_array($tags)) {
            $tags = explode(',', $tags);
        }

        $tags = array_map('trim', $tags);
        $tags = array_filter($tags);

        //explode dynamic commas values to array
        $tagsFormatted = [];
        foreach ($tags as $tag) {
            if (false !== strpos($tag, ',')) {
                $innerTags = explode(',', $tag);
                foreach ($innerTags as $t) {
                    $tagsFormatted[] = $t;
                }
            } else {
                $tagsFormatted[] = $tag;
            }
        }
        if ($tags) {
            $arguments['tags'] = array_map('trim', $tagsFormatted);
        }

        $note = '';
        if ($feedData['note']) {
            $note = esc_attr($feedData['note']);
        }

        $arguments['interests'] = [];

        $contactHash = md5(strtolower($arguments['email_address']));

        $existingMember = $this->getMemberByEmail($listId, $arguments['email_address']);

        $isNew = true;
        if (! empty($existingMember['id'])) {
            $isNew = false;
            if (ArrayHelper::isTrue($feedData, 'resubscribe')) {
                //for resubscribing unsubscribed contact ; status = subscribed || pending
                $arguments['status'] = $status;
            }
            // We have members so we can merge the values
            $status = apply_filters_deprecated(
                'fluentform_mailchimp_keep_existing_interests',
                [
                    true,
                    $form->id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/mailchimp_keep_existing_interests',
                'Use fluentform/mailchimp_keep_existing_interests instead of fluentform_mailchimp_keep_existing_interests.'
            );
            if (apply_filters('fluentform/mailchimp_keep_existing_interests', $status, $form->id)) {
                $arguments['interests'] = ArrayHelper::get($existingMember, 'interests', []);
            }

            if ($arguments['tags']) {
                $isExistingTags = apply_filters_deprecated(
                    'fluentform_mailchimp_keep_existing_tags',
                    [
                        true,
                        $form->id
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/mailchimp_keep_existing_tags',
                    'Use fluentform/mailchimp_keep_existing_tags instead of fluentform_mailchimp_keep_existing_tags.'
                );
                if (apply_filters('fluentform/mailchimp_keep_existing_tags', $isExistingTags, $form->id)) {
                    $tags = ArrayHelper::get($existingMember, 'tags', []);
                    $tagNames = [];
                    foreach ($tags as $tag) {
                        $tagNames[] = $tag['name'];
                    }
                    $allTags = wp_parse_args($arguments['tags'], $tagNames);
                    $arguments['tags'] = array_unique($allTags);
                }
            }
        }

        if (
            ArrayHelper::get($feedData, 'interest_group.sub_category') &&
            ArrayHelper::get($feedData, 'interest_group.category')
        ) {
            $interestGroup = ArrayHelper::get($feedData, 'interest_group.sub_category');
            $arguments['interests'][$interestGroup] = true;
        }

        $arguments = array_filter($arguments);
        $settings = get_option('_fluentform_mailchimp_details');
        $MailChimp = new MailChimp($settings['apiKey']);
        $endPoint = 'lists/' . $listId . '/members/' . $contactHash;

        $result = $MailChimp->put($endPoint, $arguments);

        if (400 == $result['status']) {
            return new \WP_Error(423, $result['detail']);
        }

        if ($result && ! is_wp_error($result) && isset($result['id'])) {
            $noteEnpoint = 'lists/' . $listId . '/members/' . $contactHash . '/notes';
            if ($note) {
                $MailChimp->post($noteEnpoint, [
                    'note' => $note,
                ]);
            }

            // Let's sync the tags
            if (! $isNew && $arguments['tags']) {
                $currentTags = [];
                foreach ($result['tags'] as $tag) {
                    $currentTags[] = $tag['name'];
                }
                $newTags = $arguments['tags'];
                sort($newTags);
                sort($currentTags);

                if ($newTags != $currentTags) {
                    $tagEnpoint = 'lists/' . $listId . '/members/' . $contactHash . '/tags';
                    if ($newTags) {
                        $formattedtags = [];
                        foreach ($newTags as $tag) {
                            $formattedtags[] = [
                                'name'   => $tag,
                                'status' => 'active',
                            ];
                        }
                        $MailChimp->post($tagEnpoint, [
                            'tags' => $formattedtags,
                        ]);
                    }
                }
            }
            return true;
        }

        return $result;
    }

    /**
     * Get a specific MailChimp list member.
     *
     */
    public function getMemberByEmail($list_id, $email_address)
    {
        $settings = get_option('_fluentform_mailchimp_details');
        $MailChimp = new MailChimp($settings['apiKey']);
        // Prepare subscriber hash.
        $subscriber_hash = md5(strtolower($email_address));
        return $MailChimp->get('lists/' . $list_id . '/members/' . $subscriber_hash);
    }
}
