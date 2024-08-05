<?php

namespace FluentForm\App\Services\WPAsync;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\FormDataParser;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\Framework\Foundation\Application;

class FluentFormAsyncRequest
{
    /**
     * $prefix The prefix for the identifier
     * @var string
     */
    protected $table = 'ff_scheduled_actions';

    /**
     * $action The action for the identifier
     * @var string
     */
    protected $action = 'fluentform_background_process';

    /**
     * $actions Actions to be fired when an async request is sent
     * @var array
     */
    protected $actions = array();

    /**
     * $app Instance of Application/Framework
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app = null;

    static $formCache = [];
    static $entryCache = [];
    static $submissionCache = [];

    /**
     * Construct the Object
     * @param \FluentForm\Framework\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function queue($feed)
    {
        return wpFluent()->table($this->table)->insertGetId($feed);
    }

    public function queueFeeds($feeds)
    {
        return wpFluent()->table($this->table)
            ->insert($feeds);
    }

    public function dispatchAjax($data = [])
    {
        /* This hook is deprecated and will be removed soon */
        $sslVerify = apply_filters('fluentform_https_local_ssl_verify', false);
        
        $args = array(
            'timeout' => 0.1,
            'blocking' => false,
            'body' => $data,
            'cookies' => wpFluentForm('request')->cookie(),
            'sslverify' => apply_filters('fluentform/https_local_ssl_verify', $sslVerify),
        );

        $queryArgs = array(
            'action' => $this->action,
            'nonce' => wp_create_nonce($this->action),
        );

        $url = add_query_arg($queryArgs, admin_url( 'admin-ajax.php' ));
        wp_remote_post(esc_url_raw($url), $args);
    }

    public function handleBackgroundCall()
    {
        $originId = wpFluentForm('request')->get('origin_id', false);

        $this->processActions($originId);
        echo 'success';
        die();
    }

    public function processActions($originId = false)
    {
        $actionFeedQuery = wpFluent()->table($this->table)
                            ->where('status', 'pending');
        if($originId) {
            $actionFeedQuery = $actionFeedQuery->where('origin_id', $originId);
        }

        $actionFeeds = $actionFeedQuery->get();

        if(!$actionFeeds) {
            return;
        }

        $formCache = [];
        $submissionCache = [];
        $entryCache = [];

        foreach ($actionFeeds as $actionFeed) {
            $action = $actionFeed->action;
            $feed = maybe_unserialize($actionFeed->data);
            $feed['scheduled_action_id'] = $actionFeed->id;
            if(isset($submissionCache[$actionFeed->origin_id])) {
                $submission = $submissionCache[$actionFeed->origin_id];
            } else {
                $submission = wpFluent()->table('fluentform_submissions')->find($actionFeed->origin_id);
                $submissionCache[$submission->id] = $submission;
            }
            if(isset($formCache[$submission->form_id])) {
                $form = $formCache[$submission->form_id];
            } else {
                $form = wpFluent()->table('fluentform_forms')->find($submission->form_id);
                $formCache[$form->id] = $form;
            }

            if(isset($entryCache[$submission->id])) {
                $entry = $entryCache[$submission->id];
            } else {
                $entry = $this->getEntry($submission, $form);
                $entryCache[$submission->id] = $entry;
            }
            $formData = json_decode($submission->response, true);

            wpFluent()->table($this->table)
                ->where('id', $actionFeed->id)
                ->update([
                    'status' => 'processing',
                    'retry_count' => $actionFeed->retry_count + 1,
                    'updated_at' => current_time('mysql')
                ]);

            do_action($action, $feed, $formData, $entry, $form);
        }

        if($originId && !empty($form) && !empty($submission)) {
            /* This hook is deprecated and will be removed soon */
            do_action('fluentform_global_notify_completed', $submission->id, $form);
            
            do_action('fluentform/global_notify_completed', $submission->id, $form);
        }
    }

    private function getEntry($submission, $form)
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        return FormDataParser::parseFormEntry($submission, $form, $formInputs);
    }

    public function process($queue)
    {
        if (is_numeric($queue)) {
            $queue = wpFluent()->table($this->table)->where('status', 'pending')->find($queue);
        }

        if (!$queue || empty($queue->action)) {
            return;
        }

        $action = $queue->action;
        $feed = maybe_unserialize($queue->data);
        $feed['scheduled_action_id'] = $queue->id;

        if (isset(static::$submissionCache[$queue->origin_id])) {
            $submission = static::$submissionCache[$queue->origin_id];
        } else {
            $submission = wpFluent()->table('fluentform_submissions')->find($queue->origin_id);
            
            static::$submissionCache[$submission->id] = $submission;
        }

        if (isset(static::$formCache[$submission->form_id])) {
            $form = static::$formCache[$submission->form_id];
        } else {
            $form = wpFluent()->table('fluentform_forms')->find($submission->form_id);
            
            static::$formCache[$form->id] = $form;
        }

        if (isset(static::$entryCache[$submission->id])) {
            $entry = static::$entryCache[$submission->id];
        } else {
            $entry = $this->getEntry($submission, $form);
            
            static::$entryCache[$submission->id] = $entry;
        }

        $formData = json_decode($submission->response, true);

        wpFluent()->table($this->table)
            ->where('id', $queue->id)
            ->update([
                'status' => 'processing',
                'retry_count' => $queue->retry_count + 1,
                'updated_at' => current_time('mysql')
            ]);

        do_action($action, $feed, $formData, $entry, $form);

        $this->maybeFinished($submission->id, $form);
    }

    public function maybeFinished($originId, $form)
    {
        $pendingFeeds = wpFluent()->table($this->table)->where([
            'status'    => 'pending',
            'origin_id' => $originId
        ])->get();

        if (!$pendingFeeds) {
            do_action('fluentform/global_notify_completed', $originId, $form);
        }
    }
}
