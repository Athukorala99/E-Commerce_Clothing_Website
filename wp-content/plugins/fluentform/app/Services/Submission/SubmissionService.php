<?php

namespace FluentForm\App\Services\Submission;

use Exception;
use FluentForm\App\Models\EntryDetails;
use FluentForm\App\Models\Form;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Models\Submission;
use FluentForm\Framework\Support\Arr;
use FluentForm\App\Models\SubmissionMeta;
use FluentForm\Framework\Support\Collection;
use FluentForm\App\Services\Form\FormService;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;

class SubmissionService
{
    /**
     * @var \FluentForm\App\Models\Submission|\FluentForm\Framework\Database\Query\Builder|\FluentForm\Framework\Database\Orm\Builder
     */
    protected $model;
    protected $formService;

    public function __construct()
    {
        $this->model = new Submission();
        $this->formService = new FormService();
    }

    public function get($attributes = [])
    {
        if (!defined('FLUENTFORM_RENDERING_ENTRIES')) {
            define('FLUENTFORM_RENDERING_ENTRIES', true);
        }

        $entries = $this->model->paginateEntries($attributes);

        if (Arr::get($attributes, 'parse_entry')) {
            $form = Form::find(Arr::get($attributes, 'form_id'));

            $parsedEntries = FormDataParser::parseFormEntries($entries->items(), $form);

            $entries->setCollection(Collection::make($parsedEntries));
        }

        return apply_filters('fluentform/get_submissions', $entries);
    }

    public function find($submissionId)
    {
        try {
            if (!defined('FLUENTFORM_RENDERING_ENTRY')) {
                define('FLUENTFORM_RENDERING_ENTRY', true);
            }

            $submission = $this->model->with(['form'])->findOrFail($submissionId);

            $form = $submission->form;

            $autoRead = apply_filters_deprecated(
                'fluentform_auto_read',
                [true, $form],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/auto_read_submission'
            );

            $autoRead = apply_filters('fluentform/auto_read_submission', $autoRead, $form);

            if ('unread' === $submission->status && $autoRead) {
                $submission->fill(['status' => 'read'])->save();
            }

            $submission = FormDataParser::parseFormEntry($submission, $form, null, true);

            
            if($submission->user_id) {
                $user = get_user_by('ID', $submission->user_id);
                $userDisplayName = trim($user->first_name.' '.$user->last_name);
                if(!$userDisplayName) {
                    $userDisplayName = $user->display_name;
                }
                
                if($user) {
                    $submission->user = [
                        'ID' => $user->ID,
                        'name' => $userDisplayName,
                        'permalink' => get_edit_user_link($user->ID)
                    ];
                }
            }
            
            $submission = apply_filters_deprecated(
                'fluentform_single_response_data',
                [$submission, $form->id],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/find_submission'
            );

            return apply_filters('fluentform/find_submission', $submission)->makeHidden('form');
        } catch (Exception $e) {
            throw new Exception(
                __('No Entry found.', 'fluentform')
            );
        }
    }

    public function resources($attributes)
    {
        $resources = [];

        $formId = Arr::get($attributes, 'form_id');
        $submissionId = Arr::get($attributes, 'entry_id');
    
        if (Arr::get($attributes, 'counts')) {
            $resources['counts'] = $this->model->countByGroup($formId);
        }

        $formInputsAndLabels = null;

        $wantsLabels = Arr::get($attributes, 'labels');

        if ($wantsLabels) {
            $formInputsAndLabels = $this->formService->getInputsAndLabels($formId);
            $resources['labels'] = $formInputsAndLabels['labels'];
        }

        if (Arr::get($attributes, 'fields')) {
            $formInputsAndLabels = $formInputsAndLabels ? $formInputsAndLabels : $this->formService->getInputsAndLabels($formId);
            $resources['fields'] = $formInputsAndLabels['inputs'];
        }

        if (Arr::get($attributes, 'visibleColumns')) {
            $resources['visibleColumns'] = Helper::getFormMeta($formId, '_visible_columns', null);
        }

        if (Arr::get($attributes, 'columnsOrder')) {
            $resources['columnsOrder'] = Helper::getFormMeta($formId, '_columns_order', null);
        }

        if (Arr::get($attributes, 'next')) {
            $resources['next'] = $this->model->findAdjacentSubmission($attributes);
        }

        if (Arr::get($attributes, 'previous')) {
            $attributes['direction'] = 'previous';
            $resources['previous'] = $this->model->findAdjacentSubmission($attributes);
        }
        if (count(array_intersect(['orderData', 'widgets', 'cards'], array_keys($attributes))) > 0) {
            try {
                $submission = $this->model->with('form')->findOrFail($submissionId);
            } catch (Exception $e) {
                throw new Exception(
                    __('No Entry found.', 'fluentform')
                );
            }

            if (Arr::get($attributes, 'orderData')) {
                $hasPayment = $submission->payment_status || $submission->payment_total || 'subscription' === $submission->payment_type;

                if ($hasPayment) {
                    $resources['orderData'] = apply_filters(
                        'fluentform/submission_order_data',
                        false,
                        $submission,
                        $submission->form
                    );

                    if ($wantsLabels) {
                        $resources['labels'] = apply_filters(
                            'fluentform/submission_labels',
                            $resources['labels'],
                            $submission,
                            $submission->form
                        );
                    }
                }
            }

            if (Arr::get($attributes, 'widgets')) {
                $resources['widgets'] = apply_filters(
                    'fluentform/submissions_widgets', [], $resources, $submission
                );
            }

            if (Arr::get($attributes, 'cards')) {
                $resources['cards'] = apply_filters(
                    'fluentform/submission_cards', [], $resources, $submission
                );
            }
        }

        return apply_filters('fluentform/submission_resources', $resources);
    }

    public function updateStatus($attributes = [])
    {
        $submissionId = intval(Arr::get($attributes, 'entry_id'));

        $status = sanitize_text_field(Arr::get($attributes, 'status'));

        $this->model->amend($submissionId, ['status' => $status]);

        return $status;
    }

    public function toggleIsFavorite($submissionId)
    {
        try {
            $submission = $this->model->findOrFail($submissionId);
        } catch (Exception $e) {
            throw new Exception(
                __('No Entry found.', 'fluentform')
            );
        }

        if ($submission->is_favourite) {
            $message = __('The entry has been removed from favorites', 'fluentform');
        } else {
            $message = __('The entry has been marked as favorites', 'fluentform');
        }

        $submission->fill(['is_favourite' => !$submission->is_favourite])->save();

        return [$message, $submission->is_favourite];
    }

    public function storeColumnSettings($attributes = [])
    {
        $formId = Arr::get($attributes, 'form_id');
        $metaKey = sanitize_text_field(Arr::get($attributes, 'meta_key'));
        $metaValue = wp_unslash(Arr::get($attributes, 'settings'));

        FormMeta::persist($formId, $metaKey, $metaValue);
    }

    public function handleBulkActions($attributes = [])
    {
        $formId = Arr::get($attributes, 'form_id');

        $submissionIds = fluentFormSanitizer(Arr::get($attributes, 'entries', []));

        $actionType = sanitize_text_field(Arr::get($attributes, 'action_type'));

        if (!$formId || !count($submissionIds)) {
            throw new Exception(__('Please select entries first', 'fluentform'));
        }

        $query = $this->model->where('form_id', $formId)->whereIn('id', $submissionIds);

        $statuses = Helper::getEntryStatuses($formId);

        $message = '';

        if (isset($statuses[$actionType])) {
            $query->update([
                'status'     => $actionType,
                'updated_at' => current_time('mysql'),
            ]);

            $message = 'Selected entries successfully marked as ' . $statuses[$actionType];
        } elseif ('other.delete_permanently' == $actionType) {
            $this->deleteEntries($submissionIds, $formId);

            $message = __('Selected entries successfully deleted', 'fluentform');
        } elseif ('other.make_favorite' == $actionType) {
            $query->update([
                'is_favourite' => 1,
            ]);

            $message = __('Selected entries successfully marked as favorites', 'fluentform');
        } elseif ('other.unmark_favorite' == $actionType) {
            $query->update([
                'is_favourite' => 0,
            ]);

            $message = __('Selected entries successfully removed from favorites', 'fluentform');
        }

        return $message;
    }

    public function deleteEntries($submissionIds, $formId)
    {
        $submissionIds = (array) $submissionIds;

        do_action('fluentform/before_deleting_entries', $submissionIds, $formId);

        foreach ($submissionIds as $submissionId) {
            do_action_deprecated(
                'fluentform_before_entry_deleted',
                [$submissionId, $formId],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/before_deleting_entries'
            );
        }

        $this->deleteFiles($submissionIds, $formId);

        Submission::remove($submissionIds);

        do_action('fluentform/after_deleting_submissions', $submissionIds, $formId);

        foreach ($submissionIds as $submissionId) {
            do_action_deprecated(
                'fluentform_after_entry_deleted',
                [$submissionId, $formId],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/after_deleting_entries'
            );
        }
    }

    public function deleteFiles($submissionIds, $formId)
    {
        apply_filters_deprecated(
            'fluentform_disable_attachment_delete',
            [
                false,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/disable_attachment_delete',
            'Use fluentform/disable_attachment_delete instead of fluentform_disable_attachment_delete'
        );

        $disableAttachmentDelete = apply_filters(
            'fluentform/disable_attachment_delete', false, $formId
        );

        $shouldDelete = defined('FLUENTFORMPRO') && $formId && !$disableAttachmentDelete;

        if ($shouldDelete) {
            $deletables = $this->getAttachments($submissionIds, $formId);

            foreach ($deletables as $file) {
                $file = wp_upload_dir()['basedir'] . FLUENTFORM_UPLOAD_DIR . '/' . basename($file);

                if (is_readable($file) && !is_dir($file)) {
                    @unlink($file);
                }
            }
            // Empty Temp Uploads
            if (defined('FLUENTFORMPRO')) {
                $tempDir = wp_upload_dir()['basedir'] . FLUENTFORM_UPLOAD_DIR . '/temp/';
                $files = glob($tempDir . '*');
                if(!empty($files)){
                    foreach ($files as $file) {
                        if (basename($file) !== 'index.php') {
                            unlink($file);
                        }
                    }
                }
            }
           
        }
    }

    public function getAttachments($submissionIds, $form)
    {
        $submissionIds = (array) $submissionIds;

        if (!$form instanceof Form) {
            $form = Form::find($form);
        }

        $fields = FormFieldsParser::getAttachmentInputFields($form, ['element', 'attributes']);

        $attachments = [];

        if ($fields) {
            $fields = Arr::pluck($fields, 'attributes.name');

            $submissions = $this->model->whereIn('id', $submissionIds)->get();

            foreach ($submissions as $submission) {
                $response = json_decode($submission->response, true);

                $files = Arr::collapse(Arr::only($response, $fields));

                $attachments = array_merge($attachments, $files);
            }
        }

        return $attachments;
    }

    public function getNotes($submissionId, $attributes)
    {
        $formId = (int) Arr::get($attributes, 'form_id');
        $apiLog = 'yes' === sanitize_text_field(Arr::get($attributes, 'api_log'));

        $metaKeys = ['_notes'];

        if ($apiLog) {
            $metaKeys[] = 'api_log';
        }

        $notes = SubmissionMeta::where('response_id', $submissionId)
            ->whereIn('meta_key', $metaKeys)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($notes as $note) {
            if ($note->user_id) {
                $note->pemalink = get_edit_user_link($note->user_id);
                $user = get_user_by('ID', $note->user_id);

                if ($user) {
                    $note->created_by = $user->display_name;
                } else {
                    $note->created_by = __('Fluent Forms Bot', 'fluentform');
                }
            } else {
                $note->pemalink = false;
            }
        }

        apply_filters_deprecated(
            'fluentform_entry_notes',
            [
                $notes,
                $submissionId,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/entry_notes',
            'Use fluentform/entry_notes instead of fluentform_entry_notes'
        );

        $notes = apply_filters('fluentform/entry_notes', $notes, $submissionId, $formId);

        return apply_filters('fluentform/submission_notes', $notes, $submissionId, $formId);
    }

    public function storeNote($submissionId, $attributes = [])
    {
        $formId = (int) Arr::get($attributes, 'form_id');

        $content = sanitize_textarea_field($attributes['note']['content']);
        $status = sanitize_text_field($attributes['note']['status']);
        $user = get_user_by('ID', get_current_user_id());
        $now = current_time('mysql');

        $note = [
            'response_id' => $submissionId,
            'form_id'     => $formId,
            'meta_key'    => '_notes',
            'value'       => $content,
            'status'      => $status,
            'user_id'     => $user->ID,
            'name'        => $user->display_name,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        $note = apply_filters_deprecated(
            'fluentform_add_response_note',
            [$note],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/store_submission_note'
        );

        $note = apply_filters('fluentform/store_submission_note', $note);

        $submissionMeta = new SubmissionMeta;

        $submissionMeta->fill($note)->save();

        do_action_deprecated(
            'fluentform_new_response_note_added',
            [$submissionMeta->id, $submissionMeta],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/submission_note_stored'
        );

        do_action('fluentform/submission_note_stored', $submissionMeta->id, $submissionMeta);

        return [
            'message'   => __('Note has been successfully added', 'fluentform'),
            'note'      => $submissionMeta,
            'insert_id' => $submissionMeta->id,
        ];
    }
    
    public function updateSubmissionUser($userId, $submissionId)
    {
        if (!$userId || !$submissionId) {
            throw new Exception(__('Submission ID and User ID is required', 'fluentform'));
        }
    
        $submission = Submission::find($submissionId);
        $user = get_user_by('ID', $userId);
    
        if (!$submission || $submission->user_id == $userId || !$user) {
            throw new Exception(__('Invalid Request', 'fluentform'));
        }
    
        Submission::where('id', $submission->id)
            ->update([
                'user_id'    => $userId,
                'updated_at' => current_time('mysql'),
            ]);
    
        if (defined('FLUENTFORMPRO')) {
            // let's update the corresponding user IDs for transactions and
            wpFluent()->table('fluentform_transactions')
                ->where('submission_id', $submission->id)
                ->update([
                    'user_id'    => $userId,
                    'updated_at' => current_time('mysql'),
                ]);
        }

        do_action('fluentform/log_data', [
            'parent_source_id' => $submission->form_id,
            'source_type'      => 'submission_item',
            'source_id'        => $submission->id,
            'component'        => 'General',
            'status'           => 'info',
            'title'            => 'Associate user has been changed from ' . $submission->user_id . ' to ' . $userId,
        ]);

        do_action_deprecated(
            'fluentform_submission_user_changed',
            [
                $submission,
                $user
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/submission_user_changed',
            'Use fluentform/submission_user_changed instead of fluentform_submission_user_changed.'
        );
    
        do_action('fluentform/submission_user_changed', $submission, $user);
    
        return([
            'message' => __('Selected user has been successfully assigned to this submission', 'fluentform'),
            'user'    => [
                'name'      => $user->display_name,
                'email'     => $user->user_email,
                'ID'        => $user->ID,
                'permalink' => get_edit_user_link($user->ID),
            ],
            'user_id' => $userId,
        ]);
    }
    
    public function recordEntryDetails($entryId, $formId, $data)
    {
        $formData = Arr::except($data, [
            '__fluent_form_embded_post_id',
            '_fluentform_' . $formId . '_fluentformnonce',
            '_wp_http_referer',
            'g-recaptcha-response',
            'h-captcha-response',
            'cf-turnstile-response',
            '__stripe_payment_method_id',
            '__ff_all_applied_coupons',
            '__entry_intermediate_hash',
        ]);
        
        $entryItems = [];
        foreach ($formData as $dataKey => $dataValue) {
            if (empty($dataValue)) {
                continue;
            }
            
            if (is_array($dataValue) || is_object($dataValue)) {
                foreach ($dataValue as $subKey => $subValue) {
                    if (empty($subValue)) {
                        continue;
                    }
                    $entryItems[] = [
                        'form_id'        => $formId,
                        'submission_id'  => $entryId,
                        'field_name'     => trim($dataKey),
                        'sub_field_name' => $subKey,
                        'field_value'    => maybe_serialize($subValue),
                    ];
                }
            } else {
                $entryItems[] = [
                    'form_id'        => $formId,
                    'submission_id'  => $entryId,
                    'field_name'     => trim($dataKey),
                    'sub_field_name' => '',
                    'field_value'    => $dataValue,
                ];
            }
        }
        
        foreach ($entryItems as $entryItem) {
            EntryDetails::insert($entryItem);
        }
        
        return true;
    }
    
}
