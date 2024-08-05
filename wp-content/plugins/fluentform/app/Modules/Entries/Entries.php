<?php

namespace FluentForm\App\Modules\Entries;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Modules\Registerer\TranslationString;
use FluentForm\Framework\Helpers\ArrayHelper;

/**
 * @deprecated deprecated use FluentForm\App\Http\Controllers\SubmissionController
 */
class Entries extends EntryQuery
{
    /**
     * The form response model.
     *
     * @var \WpFluent\QueryBuilder\QueryBuilderHandler $responseMetaModel
     */
    protected $responseMetaModel;

    /**
     * Entries constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->responseMetaModel = wpFluent()->table('fluentform_submission_meta');
    }

    public function getAllFormEntries()
    {
        $formId = intval($this->request->get('form_id'));

        $limit = intval($this->request->get('per_page', 10));
        $page = intval($this->request->get('page', 1));
        $offset = ($page - 1) * $limit;

        $search = sanitize_text_field($this->request->get('search'));
        $status = sanitize_text_field($this->request->get('entry_status'));

        $query = wpFluent()->table('fluentform_submissions')
            ->select([
                'fluentform_submissions.id',
                'fluentform_submissions.form_id',
                'fluentform_submissions.status',
                'fluentform_submissions.created_at',
                'fluentform_submissions.browser',
                'fluentform_submissions.currency',
                'fluentform_submissions.total_paid',
                'fluentform_forms.title',
            ])
            ->join('fluentform_forms', 'fluentform_forms.id', '=', 'fluentform_submissions.form_id')
            ->orderBy('fluentform_submissions.id', 'DESC')
            ->limit($limit)
            ->offset($offset);

        if ($formId) {
            $query->where('fluentform_submissions.form_id', $formId);
        }

        if ($status) {
            $query->where('fluentform_submissions.status', $status);
        } else {
            $query->where('fluentform_submissions.status', '!=', 'trashed');
        }

        $dateRange = $this->request->get('date_range');
        if ($dateRange) {
            $query->where('fluentform_submissions.created_at', '>=', $dateRange[0] . ' 00:00:01');
            $query->where('fluentform_submissions.created_at', '<=', $dateRange[1] . ' 23:59:59');
        }

        if ($search) {
            $query->where('fluentform_submissions.response', 'LIKE', '%' . $search . '%');
            $query->orWhere('fluentform_forms.title', 'LIKE', '%' . $search . '%');
            $query->orWhere('fluentform_submissions.id', 'LIKE', '%' . $search . '%');
        }

        $total = $query->count();
        $entries = $query->get();
        foreach ($entries as $entry) {
            $entry->entry_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $entry->form_id . '#/entries/' . $entry->id);
            $entry->human_date = human_time_diff(strtotime($entry->created_at), strtotime(current_time('mysql')));
        }
        wp_send_json_success([
            'entries'         => $entries,
            'total'           => $total,
            'last_page'       => ceil($total / $limit),
            'available_forms' => $this->getAvailableForms(),
        ]);
    }

    public function getEntriesReport()
    {
        $from = date('Y-m-d H:i:s', strtotime('-30 days'));
        $to = date('Y-m-d H:i:s', strtotime('+1 days'));

        $ranges = $this->request->get('date_range', []);

        if (!empty($ranges[0])) {
            $from = $ranges[0];
        }

        if (!empty($ranges[1])) {
            $time = strtotime($ranges[1]) + 24 * 60 * 60;
            $to = date('Y-m-d H:i:s', $time);
        }

        $period = new \DatePeriod(new \DateTime($from), new \DateInterval('P1D'), new \DateTime($to));

        $range = [];

        foreach ($period as $date) {
            $range[$date->format('Y-m-d')] = 0;
        }

        $itemsQuery = wpFluent()->table('fluentform_submissions')->select([
            wpFluent()->raw('DATE(created_at) AS date'),
            wpFluent()->raw('COUNT(id) AS count'),
        ])
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date', 'ASC');

        $formId = $this->request->get('form_id');

        if ($formId) {
            $itemsQuery = $itemsQuery->where('form_id', $formId);
        }

        $items = $itemsQuery->get();
        foreach ($items as $item) {
            $range[$item->date] = $item->count; //Filling value in the array
        }

        wp_send_json_success([
            'stats' => $range,
        ]);
    }

    public function renderEntries($form_id)
    {
        wp_enqueue_script('fluentform_form_entries');

        $forms = wpFluent()
            ->table('fluentform_forms')
            ->select(['id', 'title'])
            ->orderBy('id', 'DESC')
            ->get();

        $emailNotifications = wpFluent()
            ->table('fluentform_form_meta')
            ->where('form_id', $form_id)
            ->where('meta_key', 'notifications')
            ->get();

        $formattedNotification = [];

        foreach ($emailNotifications as $notification) {
            $value = \json_decode($notification->value, true);
            $formattedNotification[] = [
                'id'   => $notification->id,
                'name' => ArrayHelper::get($value, 'name'),
            ];
        }

        $form = wpFluent()->table('fluentform_forms')->find($form_id);
        $submissionShortcodes = \FluentForm\App\Services\FormBuilder\EditorShortCode::getSubmissionShortcodes();
        $submissionShortcodes['shortcodes']['{ip}'] = 'IP';
        if ($form->has_payment) {
            $submissionShortcodes['shortcodes']['{payment.payment_status}'] = __('Payment Status','fluentform');
            $submissionShortcodes['shortcodes']['{payment.payment_total}'] = __('Payment Total','fluentform');
        }
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);
        $data = [
            'all_forms_url'         => admin_url('admin.php?page=fluent_forms'),
            'forms'                 => $forms,
            'form_id'               => $form->id,
            'enabled_auto_delete'   => Helper::isEntryAutoDeleteEnabled($form_id),
            'current_form_title'    => $form->title,
            'entry_statuses'        => Helper::getEntryStatuses($form_id),
            'entries_url_base'      => admin_url('admin.php?page=fluent_forms&route=entries&form_id='),
            'no_found_text'         => __('Sorry! No entries found. All your entries will be shown here once you start getting form submissions', 'fluentform'),
            'has_pro'               => defined('FLUENTFORMPRO'),
            'printStyles'           => [fluentformMix('css/settings_global.css')],
            'email_notifications'   => $formattedNotification,
            'available_countries'   => getFluentFormCountryList(),
            'upgrade_url'           => fluentform_upgrade_url(),
            'form_entries_str'      => TranslationString::getEntriesI18n(),
            'editor_shortcodes'     =>  $submissionShortcodes['shortcodes'],
            'input_labels'          =>  $inputLabels,
        ];
    
        $data = apply_filters_deprecated(
            'fluent_form_entries_vars',
            [
                $data,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/entries_vars',
            'Use fluentform/entries_vars instead of fluent_form_entries_vars.'
        );

        $fluentFormEntriesVars = apply_filters('fluentform/entries_vars', $data, $form);

        wp_localize_script(
            'fluentform_form_entries',
            'fluent_form_entries_vars',
            $fluentFormEntriesVars
        );

        wpFluentForm('view')->render('admin.form.entries', [
            'form_id' => $form_id,
            'has_pdf' => defined('FLUENTFORM_PDF_VERSION') ? 'true' : 'false',
        ]);
    }

    public function getEntriesGroup()
    {
        $formId = intval($this->request->get('form_id'));
        $counts = $this->groupCount($formId);
        wp_send_json_success([
            'counts' => $counts,
        ], 200);
    }

    public function _getEntries(
        $formId,
        $currentPage,
        $perPage,
        $sortBy,
        $entryType,
        $search,
        $wheres = []
    ) {
        $this->formId = $formId;
        $this->per_page = $perPage;
        $this->sort_by = $sortBy;
        $this->page_number = $currentPage;
        $this->search = $search;
        $this->wheres = $wheres;

        if ('favorite' == $entryType) {
            $this->is_favourite = true;
        } elseif ('all' != $entryType && $entryType) {
            $this->status = $entryType;
        }

        $dateRange = $this->request->get('date_range');
        if ($dateRange) {
            $this->startDate = $dateRange[0];
            $this->endDate = $dateRange[1];
        }

        $form = $this->formModel->find($formId);
        $formMeta = $this->getFormInputsAndLabels($form);
        $formLabels = $formMeta['labels'];
    
        $formLabels = apply_filters_deprecated(
            'fluentfoform_entry_lists_labels',
            [
                $formLabels,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/entry_lists_labels',
            'Use fluentform/entry_lists_labels instead of fluentfoform_entry_lists_labels.'
        );

        $formLabels = apply_filters('fluentform/entry_lists_labels', $formLabels, $form);
        $submissions = $this->getResponses();
        $submissions['data'] = FormDataParser::parseFormEntries($submissions['data'], $form);

        return compact('submissions', 'formLabels');
    }

    public function getEntries()
    {
        if (!defined('FLUENTFORM_RENDERING_ENTRIES')) {
            define('FLUENTFORM_RENDERING_ENTRIES', true);
        }

        $wheres = [];

        if ($paymentStatuses = $this->request->get('payment_statuses')) {
            if (is_array($paymentStatuses)) {
                $wheres[] = ['payment_status', $paymentStatuses];
            }
        }

        $entries = $this->_getEntries(
            intval($this->request->get('form_id')),
            intval($this->request->get('current_page', 1)),
            intval($this->request->get('per_page', 10)),
            Helper::sanitizeOrderValue($this->request->get('sort_by', 'DESC')),
            sanitize_text_field($this->request->get('entry_type', 'all')),
            sanitize_text_field($this->request->get('search')),
            $wheres
        );

        $entriesFormLabels = $entries['formLabels'];
        $formId = $this->request->get('form_id');
    
        $entriesFormLabels = apply_filters_deprecated(
            'fluentform_all_entry_labels',
            [
                $entriesFormLabels,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/all_entry_labels',
            'Use fluentform/all_entry_labels instead of fluentform_all_entry_labels.'
        );

        $labels = apply_filters('fluentform/all_entry_labels', $entriesFormLabels, $formId);

        $form = $this->formModel->find($this->request->get('form_id'));

        if ($form->has_payment) {
            $entriesFormLabels = apply_filters_deprecated(
                'fluentform_all_entry_labels_with_payment',
                [
                    $entriesFormLabels,
                    false,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/all_entry_labels_with_payment',
                'Use fluentform/all_entry_labels_with_payment instead of fluentform_all_entry_labels_with_payment.'
            );

            $labels = apply_filters('fluentform/all_entry_labels_with_payment', $entriesFormLabels, false, $form);
        }
        $formId = $this->request->get('form_id');
        $visible_columns = Helper::getFormMeta($formId, '_visible_columns', null);
        $columns_order = Helper::getFormMeta($formId, '_columns_order', null);
    
        $entries['submissions'] = apply_filters_deprecated(
            'fluentform_all_entries',
            [
                $entries['submissions']
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/all_entries',
            'Use fluentform/all_entries instead of fluentform_all_entries.'
        );

        wp_send_json_success([
            'submissions'     => apply_filters('fluentform/all_entries', $entries['submissions']),
            'labels'          => $labels,
            'visible_columns' => $visible_columns,
            'columns_order'   => $columns_order,
        ], 200);
    }

    public function _getEntry()
    {
        $this->formId = intval($this->request->get('form_id'));

        $entryId = intval($this->request->get('entry_id'));

        $entry_type = sanitize_key($this->request->get('entry_type', 'all'));

        if ('favorite' === $entry_type) {
            $this->is_favourite = true;
        } elseif ('all' !== $entry_type) {
            $this->status = $entry_type;
        }

        $this->sort_by = Helper::sanitizeOrderValue($this->request->get('sort_by', 'ASC'));

        $this->search = sanitize_text_field($this->request->get('search'));

        $submission = $this->getResponse($entryId);

        if (!$submission) {
            wp_send_json_error([
                'message' => 'No Entry found.',
            ], 422);
        }

        $form = $this->formModel->find($this->formId);

        $autoRead = apply_filters_deprecated(
            'fluentform_auto_read',
            [
                true,
                $form
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/auto_read',
            'Use fluentform/auto_read instead of fluentform_auto_read.'
        );

        if ('unread' == $submission->status && apply_filters('fluentform/auto_read', $autoRead, $form)) {
            wpFluent()->table('fluentform_submissions')
                ->where('id', $entryId)
                ->update([
                    'status' => 'read',
                ]);

            $submission->status = 'read';
        }

        $formMeta = $this->getFormInputsAndLabels($form);

        $submission = FormDataParser::parseFormEntry($submission, $form, $formMeta['inputs'], true);

        if ($submission->user_id) {
            $user = get_user_by('ID', $submission->user_id);
            $user_data = [
                'name'      => $user->display_name,
                'email'     => $user->user_email,
                'ID'        => $user->ID,
                'permalink' => get_edit_user_link($user->ID),
            ];
            $submission->user = $user_data;
        }
    
        $submission = apply_filters_deprecated(
            'fluentform_single_response_data',
            [
                $submission,
                $this->formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/find_submission',
            'Use fluentform/find_submission instead of fluentform_single_response_data.'
        );

        $submission = apply_filters('fluentform/find_submission', $submission, $this->formId);
    
        $fields = $formMeta['inputs'];
        $fields = apply_filters_deprecated(
            'fluentform_single_response_input_fields',
            [
                $fields,
                $this->formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/single_response_input_fields',
            'Use fluentform/single_response_input_fields instead of fluentform_single_response_input_fields.'
        );

        $fields = apply_filters(
            'fluentform/single_response_input_fields',
            $fields,
            $this->formId
        );
        $labels = $formMeta['labels'];
        $labels = apply_filters_deprecated(
            'fluentform_single_response_input_labels',
            [
                $labels,
                $this->formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/single_response_input_labels',
            'Use fluentform/single_response_input_labels instead of fluentform_single_response_input_labels.'
        );

        $labels = apply_filters(
            'fluentform/single_response_input_labels',
            $labels,
            $this->formId
        );

        $order_data = false;

        if ($submission->payment_status || $submission->payment_total || 'subscription' === $submission->payment_type) {
            $order_data = apply_filters_deprecated(
                'fluentform_submission_order_data',
                [
                    $order_data,
                    $submission,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_order_data',
                'Use fluentform/submission_order_data instead of fluentform_submission_order_data.'
            );

            $order_data = apply_filters(
                'fluentform/submission_order_data',
                $order_data,
                $submission,
                $form
            );
    
            $labels = apply_filters_deprecated(
                'fluentform_submission_entry_labels_with_payment',
                [
                    $labels,
                    $submission,
                    $form
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_entry_labels_with_payment',
                'Use fluentform/submission_entry_labels_with_payment instead of fluentform_submission_entry_labels_with_payment.'
            );

            $labels = apply_filters(
                'fluentform/submission_entry_labels_with_payment',
                $labels,
                $submission,
                $form
            );
        }

        $nextSubmissionId = $this->getNextResponse($entryId);

        $previousSubmissionId = $this->getPrevResponse($entryId);

        return [
            'submission' => $submission,
            'next'       => $nextSubmissionId,
            'prev'       => $previousSubmissionId,
            'labels'     => $labels,
            'fields'     => $fields,
            'order_data' => $order_data,
        ];
    }

    public function getEntry()
    {
        if (!defined('FLUENTFORM_RENDERING_ENTRY')) {
            define('FLUENTFORM_RENDERING_ENTRY', true);
        }

        $entryData = $this->_getEntry();

        $widgets = apply_filters_deprecated(
            'fluentform_single_entry_widgets',
            [
                [],
                $entryData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/single_entry_widgets',
            'Use fluentform/single_entry_widgets instead of fluentform_single_entry_widgets.'
        );
        $entryData['widgets'] = apply_filters('fluentform/single_entry_widgets', $widgets, $entryData);

        $cards = apply_filters_deprecated(
            'fluentform_single_entry_cards',
            [
                [],
                $entryData
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/single_entry_cards',
            'Use fluentform/single_entry_cards instead of fluentform_single_entry_cards.'
        );
        $entryData['extraCards'] = apply_filters('fluentform/single_entry_cards', $cards, $entryData);

        wp_send_json_success($entryData, 200);
    }

    /**
     * @param       $form
     * @param array $with
     *
     * @return array
     *
     * @todo: Implement Caching mechanism so we don't have to parse these things for every request
     */
    public function getFormInputsAndLabels($form, $with = ['admin_label', 'raw'])
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, $with);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);
        return [
            'inputs' => $formInputs,
            'labels' => $inputLabels,
        ];
    }

    public function getNotes()
    {
        $formId = intval($this->request->get('form_id'));
        $entry_id = intval($this->request->get('entry_id'));
        $apiLog = 'yes' == sanitize_text_field($this->request->get('api_log'));

        $metaKeys = ['_notes'];

        if ($apiLog) {
            $metaKeys[] = 'api_log';
        }

        $notes = $this->responseMetaModel
            ->where('form_id', $formId)
            ->where('response_id', $entry_id)
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
    
        $notes = apply_filters_deprecated(
            'fluentform_entry_notes',
            [
                $notes,
                $entry_id,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/entry_notes',
            'Use fluentform/entry_notes instead of fluentform_entry_notes.'
        );

        $notes = apply_filters('fluentform/entry_notes', $notes, $entry_id, $formId);

        wp_send_json_success([
            'notes' => $notes,
        ], 200);
    }

    public function addNote()
    {
        $entryId = intval($this->request->get('entry_id'));
        $formId = intval($this->request->get('form_id'));
        $note = $this->request->get('note');
        $note_content = sanitize_textarea_field($note['content']);
        $note_status = sanitize_text_field($note['status']);
        $user = get_user_by('ID', get_current_user_id());

        $response_note = [
            'response_id' => $entryId,
            'form_id'     => $formId,
            'meta_key'    => '_notes',
            'value'       => $note_content,
            'status'      => $note_status,
            'user_id'     => $user->ID,
            'name'        => $user->display_name,
            'created_at'  => current_time('mysql'),
            'updated_at'  => current_time('mysql'),
        ];
    
        $response_note = apply_filters_deprecated(
            'fluentform_add_response_note',
            [
                $response_note
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/add_response_note',
            'Use fluentform/add_response_note instead of fluentform_add_response_note.'
        );

        $response_note = apply_filters('fluentform/add_response_note', $response_note);

        $insertId = $this->responseMetaModel->insertGetId($response_note);

        $added_note = $this->responseMetaModel->find($insertId);

        do_action_deprecated(
            'fluentform_new_response_note_added',
            [
                $insertId,
                $added_note
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/new_response_note_added',
            'Use fluentform/new_response_note_added instead of fluentform_new_response_note_added.'
        );

        do_action('fluentform/new_response_note_added', $insertId, $added_note);

        wp_send_json_success([
            'message'   => __('Note has been successfully added', 'fluentform'),
            'note'      => $added_note,
            'insert_id' => $insertId,
        ], 200);
    }

    public function changeEntryStatus()
    {
        $formId = intval($this->request->get('form_id'));
        $entryId = intval($this->request->get('entry_id'));
        $newStatus = sanitize_text_field($this->request->get('status'));

        $this->responseModel
            ->where('form_id', $formId)
            ->where('id', $entryId)
            ->update(['status' => $newStatus]);

        wp_send_json_success([
            'message' => __('Item has been marked as ' . $newStatus, 'fluentform'),
            'status'  => $newStatus,
        ], 200);
    }

    public function deleteEntry()
    {
        $formId = intval($this->request->get('form_id'));
        $entryId = intval($this->request->get('entry_id'));
        $newStatus = sanitize_text_field($this->request->get('status'));

        $this->deleteEntryById($entryId, $formId);

        wp_send_json_success([
            'message' => __('Item Successfully deleted', 'fluentform'),
            'status'  => $newStatus,
        ], 200);
    }

    public function deleteEntryById($entryId, $formId = false)
    {
        do_action_deprecated(
            'fluentform_before_entry_deleted',
            [
                $entryId,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_entry_deleted',
            'Use fluentform/before_entry_deleted instead of fluentform_init_custom_stylesheet.'
        );

        do_action('fluentform/before_entry_deleted', $entryId, $formId);
        $disableAttachmentDelete = false;
        $disableAttachmentDelete = apply_filters_deprecated(
            'fluentform_disable_attachment_delete',
            [
                $disableAttachmentDelete,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/disable_attachment_delete',
            'Use fluentform/disable_attachment_delete instead of fluentform_disable_attachment_delete.'
        );

        $disableAttachmentDelete = apply_filters('fluentform/disable_attachment_delete', $disableAttachmentDelete, $formId);
        if (defined('FLUENTFORMPRO') && $formId && !$disableAttachmentDelete) {
            if (is_numeric($formId)) {
                $form = wpFluent()->table('fluentform_forms')->find($formId);
            } else {
                $form = $formId;
            }
            $deletableFiles = $this->getSubmissionAttachments($entryId, $form);
            if ($deletableFiles) {
                foreach ($deletableFiles as $eachFile) {
                    $file = wp_upload_dir()['basedir'] . FLUENTFORM_UPLOAD_DIR . '/' . basename($eachFile);
                    if (is_readable($file) && !is_dir($file)) {
                        @unlink($file);
                    }
                }
            }
        }

        wpFluent()->table('fluentform_submissions')
            ->where('id', $entryId)
            ->delete();
        wpFluent()->table('fluentform_submission_meta')
            ->where('response_id', $entryId)
            ->delete();

        wpFluent()->table('fluentform_logs')
            ->where('source_id', $entryId)
            ->where('source_type', 'submission_item')
            ->delete();

        wpFluent()->table('fluentform_entry_details')
            ->where('submission_id', $entryId)
            ->delete();

        ob_start();
        if (defined('FLUENTFORMPRO')) {
            try {
                wpFluent()->table('fluentform_order_items')
                    ->where('submission_id', $entryId)
                    ->delete();

                wpFluent()->table('fluentform_transactions')
                    ->where('submission_id', $entryId)
                    ->delete();

                wpFluent()->table('fluentform_subscriptions')
                    ->where('submission_id', $entryId)
                    ->delete();

                wpFluent()->table('ff_scheduled_actions')
                    ->where('origin_id', $entryId)
                    ->where('type', 'submission_action')
                    ->delete();
            } catch (\Exception $exception) {
                // ...
            }
        }

        $errors = ob_get_clean();

        do_action_deprecated(
            'fluentform_after_entry_deleted',
            [
                $entryId,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_entry_deleted',
            'Use fluentform/after_entry_deleted instead of fluentform_after_entry_deleted.'
        );

        do_action('fluentform/after_entry_deleted', $entryId, $formId);

        return true;
    }

    private function getSubmissionAttachments($submissionId, $form)
    {
        $fields = FormFieldsParser::getAttachmentInputFields($form, ['element', 'attributes']);

        $deletableFiles = [];
        if ($fields) {
            $submission = wpFluent()->table('fluentform_submissions')
                ->where('id', $submissionId)
                ->first();

            $data = json_decode($submission->response, true);

            foreach ($fields as $field) {
                if (!empty($data[$field['attributes']['name']])) {
                    $files = $data[$field['attributes']['name']];
                    if (is_array($files)) {
                        $deletableFiles = array_merge($deletableFiles, $files);
                    } else {
                        $deletableFiles = $files;
                    }
                }
            }
        }

        return $deletableFiles;
    }

    public function favoriteChange()
    {
        $formId = intval($this->request->get('form_id'));
        $entryId = intval($this->request->get('entry_id'));
        $newStatus = intval($this->request->get('is_favourite'));
        if ($newStatus) {
            $message = __('Item has been added to favorites', 'fluentform');
        } else {
            $message = __('Item has been removed from favorites', 'fluentform');
        }
        $this->responseModel
            ->where('form_id', $formId)
            ->where('id', $entryId)
            ->update(['is_favourite' => $newStatus]);

        wp_send_json_success([
            'message'      => $message,
            'is_favourite' => $newStatus,
        ], 200);
    }

    public function handleBulkAction()
    {
        $formId = intval($this->request->get('form_id'));
        $entries = fluentFormSanitizer($this->request->get('entries', []));

        $actionType = sanitize_text_field($this->request->get('action_type'));

        // check if it's status change or not
        $statuses = Helper::getEntryStatuses($formId);

        if (!$formId || !count($entries)) {
            wp_send_json_error([
                'message' => __('Please select entries first', 'fluentform'),
            ], 400);
        }

        $bulkQuery = wpFluent()->table('fluentform_submissions')
            ->where('form_id', $formId)
            ->whereIn('id', $entries);

        if (isset($statuses[$actionType])) {
            // it's status change
            $bulkQuery->update([
                'status'     => $actionType,
                'updated_at' => current_time('mysql'),
            ]);

            wp_send_json_success([
                'message' => 'Selected entries successfully marked as ' . $statuses[$actionType],
            ], 200);
        }

        // now other action handler
        if ('other.delete_permanently' == $actionType) {
            $form = wpFluent()->table('fluentform_forms')->find($formId);
            foreach ($entries as $entryId) {
                $this->deleteEntryById($entryId, $form);
            }
            $message = __('Selected entries successfully deleted', 'fluentform');
        } elseif ('other.make_favorite' == $actionType) {
            $bulkQuery->update([
                'is_favourite' => 1,
            ]);
            $message = __('Selected entries successfully marked as favorites', 'fluentform');
        } elseif ('other.unmark_favorite' == $actionType) {
            $bulkQuery->update([
                'is_favourite' => 0,
            ]);
            $message = __('Selected entries successfully removed from favorites', 'fluentform');
        }

        wp_send_json_success([
            'message' => $message,
        ], 200);
    }
    
    /**
     * @deprecated Use \FluentForm\App\Services\Submission\SubmissionService::recordEntryDetails
     * @param $entryId
     * @param $formId
     * @param $data
     * @return bool
     */
    public function recordEntryDetails($entryId, $formId, $data)
    {
        $formData = ArrayHelper::except($data, Helper::getWhiteListedFields($formId));

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
            wpFluent()->table('fluentform_entry_details')->insert($entryItem);
        }

        return true;
    }

    public function updateEntryDiffs($entryId, $formId, $formData)
    {
        wpFluent()->table('fluentform_entry_details')
            ->where('submission_id', $entryId)
            ->where('form_id', $formId)
            ->whereIn('field_name', array_keys($formData))
            ->delete();

        $entryItems = [];
        foreach ($formData as $dataKey => $dataValue) {
            if (!$dataValue) {
                continue;
            }

            if (is_array($dataValue)) {
                foreach ($dataValue as $subKey => $subValue) {
                    $entryItems[] = [
                        'form_id'        => $formId,
                        'submission_id'  => $entryId,
                        'field_name'     => $dataKey,
                        'sub_field_name' => $subKey,
                        'field_value'    => maybe_serialize($subValue),
                    ];
                }
            } else {
                $entryItems[] = [
                    'form_id'        => $formId,
                    'submission_id'  => $entryId,
                    'field_name'     => $dataKey,
                    'sub_field_name' => '',
                    'field_value'    => $dataValue,
                ];
            }
        }

        foreach ($entryItems as $entryItem) {
            wpFluent()->table('fluentform_entry_details')->insert($entryItem);
        }

        return true;
    }

    public function getUsers()
    {
        // if (!current_user_can('list_users')) {
        //     wp_send_json_error([
        //         'message' => __('Sorry, You do not have permission to list users', 'fluentform')
        //     ]);
        // }

        $search = sanitize_text_field($this->request->get('search'));

        $users = get_users([
            'search' => "*{$search}*",
            'number' => 50,
        ]);

        $formattedUsers = [];

        foreach ($users as $user) {
            $formattedUsers[] = [
                'ID'    => $user->ID,
                'label' => $user->display_name . ' - ' . $user->user_email,
            ];
        }

        wp_send_json_success([
            'users' => $formattedUsers,
        ]);
    }

    public function changeEntryUser()
    {
        $userId = intval($this->request->get('user_id'));
        $submissionId = intval($this->request->get('submission_id'));

        if (!$userId || !$submissionId) {
            wp_send_json_error([
                'message' => __('Submission ID and User ID is required', 'fluentform'),
            ], 423);
        }

        $submission = fluentFormApi('submissions')->find($submissionId);

        $user = get_user_by('ID', $userId);

        if (!$submission || $submission->user_id == $userId || !$user) {
            wp_send_json_error([
                'message' => __('Invalid Request', 'fluentform'),
            ], 423);
        }

        wpFluent()->table('fluentform_submissions')
            ->where('id', $submission->id)
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

        $logData = [
            'parent_source_id' => $submission->form_id,
            'source_type'      => 'submission_item',
            'source_id'        => $submission->id,
            'component'        => 'General',
            'status'           => 'info',
            'title'            => 'Associate user has been changed from ' . $submission->user_id . ' to ' . $userId,
        ];

        do_action('fluentform/log_data', $logData);

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

        wp_send_json_success([
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

    public function getAvailableForms()
    {
        $forms = wpFluent()->table('fluentform_forms')
            ->select(['id', 'title'])
            ->orderBy('id', 'DESC')
            ->get();

        $formattedForms = [];
        foreach ($forms as $form) {
            $formattedForms[] = [
                'id'    => $form->id,
                'title' => $form->title,
            ];
        }

        return $formattedForms;
    }
}
