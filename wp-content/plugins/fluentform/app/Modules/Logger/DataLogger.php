<?php namespace FluentForm\App\Modules\Logger;

use FluentForm\Database\Migrations\Logs;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;

class DataLogger
{
    public $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getLogFilters()
    {
        $statuses = wpFluent()->table('fluentform_logs')
            ->select('status')
            ->groupBy('status')
            ->get();
        $formattedStatuses = [];

        foreach ($statuses as $status) {
            $formattedStatuses[] = $status->status;
        }

        $apis = wpFluent()->table('ff_scheduled_actions')
            ->select('status')
            ->groupBy('status')
            ->get();

        $apiStatuses = [];
        foreach ($apis as $api) {
            $apiStatuses[] = $api->status;
        }

        $components = wpFluent()->table('fluentform_logs')
            ->select('component')
            ->groupBy('component')
            ->get();

        $formattedComponents = [];
        foreach ($components as $component) {
            $formattedComponents[] = $component->component;
        }

        $forms = wpFluent()->table('fluentform_logs')
            ->select('fluentform_logs.parent_source_id', 'fluentform_forms.title')
            ->groupBy('fluentform_logs.parent_source_id')
            ->orderBy('fluentform_logs.parent_source_id', 'DESC')
            ->join('fluentform_forms', 'fluentform_forms.id', '=', 'fluentform_logs.parent_source_id')
            ->get();

        $formattedForms = [];

        foreach ($forms as $form) {
            $formattedForms[] = [
                'form_id' => $form->parent_source_id,
                'title'   => $form->title
            ];;
        }

        wp_send_json_success([
            'available_statuses'   => $formattedStatuses,
            'available_components' => $formattedComponents,
            'available_forms'      => $formattedForms,
            'api_statuses'         => $apiStatuses
        ]);
    }

    public function log($data)
    {
        if (!$data) {
            return;
        }
        $data['created_at'] = current_time('mysql');

        if (!get_option('fluentform_db_fluentform_logs_added')) {
            Logs::migrate();
        }

        wpFluent()->table('fluentform_logs')->insert($data);
    }

    public function getLogsByEntry($entry_id, $log_type = 'logs', $sourceType = 'submission_item')
    {
        if ($log_type == 'logs') {
            $logs = wpFluent()->table('fluentform_logs')
                ->where('source_id', $entry_id)
                ->where('source_type', $sourceType)
                ->orderBy('id', 'DESC')
                ->get();
    
            $logs = apply_filters_deprecated(
                'fluentform_entry_logs',
                [
                    $logs,
                    $entry_id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_logs',
                'Use fluentform/submission_logs instead of fluentform_entry_logs.'
            );

            $logs = apply_filters('fluentform/submission_logs', $logs, $entry_id);
        } else {
            $logs = wpFluent()->table('ff_scheduled_actions')
                ->select([
                    'id',
                    'action',
                    'status',
                    'note',
                    'created_at'
                ])
                ->where('origin_id', $entry_id)
                ->orderBy('id', 'DESC')
                ->get();
    
            $logs = apply_filters_deprecated(
                'fluentform_entry_api_logs',
                [
                    $logs,
                    $entry_id
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/submission_api_logs',
                'Use fluentform/submission_api_logs instead of fluentform_entry_api_logs.'
            );

            $logs = apply_filters('fluentform/submission_api_logs', $logs, $entry_id);
        }


        wp_send_json_success([
            'logs' => $logs
        ], 200);
    }

    public function getAllLogs()
    {
        $limit = intval($this->app->request->get('per_page'));
        $pageNumber = intval($this->app->request->get('page_number'));

        $skip = ($pageNumber - 1) * $limit;

        global $wpdb;
        $logsQuery = wpFluent()->table('fluentform_logs')
            ->select([
                'fluentform_logs.*',
                wpFluent()->raw($wpdb->prefix . 'fluentform_forms.title as form_title'),
                wpFluent()->raw($wpdb->prefix . 'fluentform_logs.parent_source_id as form_id'),
                wpFluent()->raw($wpdb->prefix . 'fluentform_logs.source_id as entry_id')
            ])
            ->leftJoin('fluentform_forms', 'fluentform_forms.id', '=', 'fluentform_logs.parent_source_id')
            ->orderBy('fluentform_logs.id', 'DESC');
        // ->whereIn('fluentform_logs.source_type', ['submission_item', 'form_item']);


        if ($parentSourceId = $this->app->request->get('parent_source_id')) {
            $logsQuery = $logsQuery->where('fluentform_logs.parent_source_id', intval($parentSourceId));
        }

        if ($status = $this->app->request->get('status')) {
            $logsQuery = $logsQuery->where('fluentform_logs.status', sanitize_text_field($status));
        }

        if ($component = $this->app->request->get('component')) {
            $logsQuery = $logsQuery->where('fluentform_logs.component', sanitize_text_field($component));
        }

        if ($formId = $this->app->request->get('form_id')) {
            $logsQuery = $logsQuery->where('fluentform_forms.id', intval($formId));
        }

        $logsQueryMain = $logsQuery;

        $logs = $logsQuery->offset($skip)
            ->limit($limit)
            ->get();

        foreach ($logs as $log) {
            if($log->source_type == 'submission_item' && $log->entry_id) {
                $log->submission_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $log->form_id . '#/entries/' . $log->entry_id);
            }
        }
    
        $logs = apply_filters_deprecated(
            'fluentform_all_logs',
            [
                $logs
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/all_logs',
            'Use fluentform/all_logs instead of fluentform_all_logs.'
        );

        $logs = apply_filters('fluentform/all_logs', $logs);

        $total = $logsQueryMain->count();

        wp_send_json_success([
            'logs'  => $logs,
            'total' => $total
        ], 200);

    }


    public function getApiLogs()
    {
        $limit = intval($this->app->request->get('per_page'));
        $pageNumber = intval($this->app->request->get('page_number'));

        $skip = ($pageNumber - 1) * $limit;
        global $wpdb;
        $logsQuery = wpFluent()->table('ff_scheduled_actions')
            ->select([
                'ff_scheduled_actions.id',
                'ff_scheduled_actions.action',
                'ff_scheduled_actions.form_id',
                'ff_scheduled_actions.origin_id',
                'ff_scheduled_actions.status',
                'ff_scheduled_actions.note',
                'ff_scheduled_actions.created_at',
                wpFluent()->raw($wpdb->prefix . 'fluentform_forms.title as form_title')
            ])
            ->join('fluentform_forms', 'fluentform_forms.id', '=', 'ff_scheduled_actions.form_id')
            ->orderBy('ff_scheduled_actions.id', 'DESC');


        if ($formId = $this->app->request->get('form_id')) {
            $logsQuery = $logsQuery->where('ff_scheduled_actions.form_id', intval($formId));
        }

        if ($status = $this->app->request->get('status')) {
            $logsQuery = $logsQuery->where('ff_scheduled_actions.status', $status);
        }

        if ($component = $this->app->request->get('component')) {
            $logsQuery = $logsQuery->where('ff_scheduled_actions.action', $component);
        }

        $logsQueryMain = $logsQuery;

        $logs = $logsQuery->offset($skip)
            ->limit($limit)
            ->get();
    
        $logs = apply_filters_deprecated(
            'fluentform_api_all_logs',
            [
                $logs
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/api_all_logs',
            'Use fluentform/api_all_logs instead of fluentform_api_all_logs.'
        );

        $logs = apply_filters('fluentform/api_all_logs', $logs);

        foreach ($logs as $log) {
            $log->submission_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $log->form_id . '#/entries/' . $log->origin_id);
        }

        $total = $logsQueryMain->count();

        wp_send_json_success([
            'logs'  => $logs,
            'total' => $total
        ], 200);

    }

    public function deleteLogsByIds($ids = [])
    {
        if (!$ids) {
            $ids = wp_unslash($this->app->request->get('log_ids'));
        }

        if (!$ids) {
            wp_send_json_error([
                'message' => 'No selections found'
            ], 423);
        }

        wpFluent()->table('fluentform_logs')
            ->whereIn('id', $ids)
            ->delete();

        wp_send_json_success([
            'message' => __('Selected log(s) successfully deleted', 'fluentform')
        ], 200);
    }

    public function deleteApiLogsByIds($ids = [])
    {
        if (!$ids) {
            $ids = wp_unslash($this->app->request->get('log_ids'));
        }

        if (!$ids) {
            wp_send_json_error([
                'message' => 'No selections found'
            ], 423);
        }

        wpFluent()->table('ff_scheduled_actions')
            ->whereIn('id', $ids)
            ->delete();

        wp_send_json_success([
            'message' => __('Selected log(s) successfully deleted', 'fluentform')
        ], 200);
    }

    public function retryApiAction()
    {
        $logId = $this->app->request->get('log_id');
        $actionFeed = wpFluent()->table('ff_scheduled_actions')
            ->find($logId);

        if (!$actionFeed) {
            wp_send_json_error([
                'message' => 'API log does not exist'
            ], 423);
        }

        if (!$actionFeed->status == 'success') {
            wp_send_json_error([
                'message' => 'API log already in success mode'
            ], 423);
        }

        $form = wpFluent()->table('fluentform_forms')->find($actionFeed->form_id);

        $feed = maybe_unserialize($actionFeed->data);
        $feed['scheduled_action_id'] = $actionFeed->id;

        $submission = wpFluent()->table('fluentform_submissions')->find($actionFeed->origin_id);
        $entry = $this->getEntry($submission, $form);
        $formData = json_decode($submission->response, true);

        wpFluent()->table($this->table)
            ->where('id', $actionFeed->id)
            ->update([
                'status'      => 'manual_retry',
                'retry_count' => $actionFeed->retry_count + 1,
                'updated_at'  => current_time('mysql')
            ]);

        do_action($actionFeed->action, $feed, $formData, $entry, $form);

        /*
         * Hopefully it's done
         */
        $actionFeed = wpFluent()->table('ff_scheduled_actions')
            ->find($logId);

        wp_send_json_success([
            'message' => 'Retry completed',
            'feed'    => $actionFeed
        ], 200);
    }

    private function getEntry($submission, $form)
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        return FormDataParser::parseFormEntry($submission, $form, $formInputs);
    }

    public function getApiLogFilters()
    {
        $apis = wpFluent()->table('ff_scheduled_actions')
            ->select('status')
            ->groupBy('status')
            ->get();

        $apiStatuses = [];
        foreach ($apis as $api) {
            $apiStatuses[] = $api->status;
        }

        $components = wpFluent()->table('ff_scheduled_actions')
            ->select('action')
            ->groupBy('action')
            ->get();

        $formattedComponents = [];
        foreach ($components as $component) {
            $formattedComponents[] = $component->action;
        }

        $forms = wpFluent()->table('ff_scheduled_actions')
            ->select('ff_scheduled_actions.form_id', 'fluentform_forms.title')
            ->groupBy('ff_scheduled_actions.form_id')
            ->orderBy('ff_scheduled_actions.form_id', 'DESC')
            ->join('fluentform_forms', 'fluentform_forms.id', '=', 'ff_scheduled_actions.form_id')
            ->get();

        $formattedForms = [];

        foreach ($forms as $form) {
            $formattedForms[] = [
                'form_id' => $form->form_id,
                'title'   => $form->title
            ];;
        }

        wp_send_json_success([
            'available_components' => $formattedComponents,
            'available_forms'      => $formattedForms,
            'api_statuses'         => $apiStatuses
        ]);
    }
}
