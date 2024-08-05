<?php

namespace FluentForm\App\Api;

use FluentForm\Framework\Helpers\ArrayHelper;

class Submission
{
    public function get($args = [])
    {
        $args = wp_parse_args($args, [
            'per_page'   => 10,
            'page'       => 1,
            'search'     => '',
            'form_ids'   => [],
            'sort_type'  => 'DESC',
            'entry_type' => 'all',
            'user_id'    => false,
        ]);

        $offset = $args['per_page'] * ($args['page'] - 1);
    
        $entryQuery = \FluentForm\App\Models\Submission::orderBy('id', \FluentForm\App\Helpers\Helper::sanitizeOrderValue($args['sort_type']))
            ->limit($args['per_page'])
            ->offset($offset);

        $type = sanitize_text_field($args['entry_type']);

        if ($type && 'all' != $type) {
            $entryQuery->where('status', $type);
        }

        if ($args['form_ids'] && is_array($args['form_ids'])) {
            $entryQuery->whereIn('form_id', $args['form_ids']);
        }

        if ($searchString = sanitize_text_field($args['search'])) {
            $entryQuery->where(function ($q) use ($searchString) {
                $q->where('id', 'LIKE', "%{$searchString}%")
                    ->orWhere('response', 'LIKE', "%{$searchString}%")
                    ->orWhere('status', 'LIKE', "%{$searchString}%")
                    ->orWhere('created_at', 'LIKE', "%{$searchString}%");
            });
        }

        if ($args['user_id']) {
            $entryQuery->where('user_id', (int) $args['user_id']);
        }

        $count = $entryQuery->count();

        $data = $entryQuery->get();

        $dataCount = count($data);

        $from = $dataCount > 0 ? ($args['page'] - 1) * $args['per_page'] + 1 : null;

        $to = $dataCount > 0 ? $from + $dataCount - 1 : null;
        $lastPage = (int) ceil($count / $args['per_page']);

        foreach ($data as $datum) {
            $datum->response = json_decode($datum->response, true);
        }

        return [
            'current_page' => $args['page'],
            'per_page'     => $args['per_page'],
            'from'         => $from,
            'to'           => $to,
            'last_page'    => $lastPage,
            'total'        => $count,
            'data'         => $data,
        ];
    }

    public function find($submissionId)
    {
        $submission = \FluentForm\App\Models\Submission::find($submissionId);
        $submission->response = json_decode($submission->response);
        return $submission;
    }

    public function transactions($columnValue, $column = 'submission_id')
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        return wpFluent()->table('fluentform_transactions')
            ->where($column, $columnValue)
            ->get();
    }

    public function transaction($columnValue, $column = 'id')
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        return wpFluent()->table('fluentform_transactions')
            ->where($column, $columnValue)
            ->first();
    }

    public function subscriptions($submissionId, $withTransactions = false)
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        $subscriptions = wpFluent()->table('fluentform_subscriptions')
            ->where('submission_id', $submissionId)
            ->get();

        if ($withTransactions) {
            foreach ($subscriptions as $subscription) {
                $subscription->transactions = $this->transactionsBySubscriptionId($subscription->id);
            }
        }

        return $subscriptions;
    }

    public function getSubscription($subscriptionId, $withTransactions = false)
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        $subscription = wpFluent()->table('fluentform_subscriptions')
            ->where('id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return false;
        }

        if ($withTransactions) {
            $subscription->transactions = $this->transactionsBySubscriptionId($subscription->id);
        }

        return $subscription;
    }

    public function transactionsByUserId($userId = false, $args = [])
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        if (!$userId) {
            $userId = get_current_user_id();
        }
        if (!$userId) {
            return [];
        }

        $user = get_user_by('ID', $userId);

        if (!$user) {
            return [];
        }

        $args = wp_parse_args($args, [
            'transaction_types' => [],
            'statuses'          => [],
            'grouped'           => false,
        ]);

        $query = wpFluent()->table('fluentform_transactions')
            ->orderBy('id', 'DESC')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->ID)
                    ->orderBy('id', 'DESC')
                    ->orWhere('payer_email', $user->user_email);
            });

        if (!empty($args['transaction_types'])) {
            $query->whereIn('transaction_type', $args['transaction_types']);
        }

        if (!empty($args['statuses'])) {
            $query->whereIn('status', $args['statuses']);
        }

        if (!empty($args['grouped'])) {
            $query->groupBy('submission_id');
        }

        return $query->get();
    }

    public function transactionsBySubscriptionId($subscriptionId)
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        return wpFluent()->table('fluentform_transactions')
            ->where('subscription_id', $subscriptionId)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function transactionsBySubmissionId($submissionId)
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        return wpFluent()->table('fluentform_transactions')
            ->where('submission_id', $submissionId)
            ->get();
    }

    public function subscriptionsByUserId($userId = false, $args = [])
    {
        if (!defined('FLUENTFORMPRO')) {
            return [];
        }

        if (!$userId) {
            $userId = get_current_user_id();
        }
        if (!$userId) {
            return [];
        }

        $user = get_user_by('ID', $userId);

        if (!$user) {
            return [];
        }

        $args = wp_parse_args($args, [
            'statuses'   => [],
            'form_title' => false,
        ]);

        $submissions = \FluentForm\App\Models\Submission::select(['id', 'currency'])
            ->where('user_id', $userId)
            ->where('payment_type', 'subscription')
            ->get();

        if (!$submissions) {
            return [];
        }

        $submissionIds = [];
        $currencyMaps = [];
        foreach ($submissions as $submission) {
            $submissionIds[] = $submission->id;
            $currencyMaps[$submission->id] = $submission->currency;
        }

        $query = wpFluent()->table('fluentform_subscriptions')
            ->select(['fluentform_subscriptions.*'])
            ->orderBy('id', 'DESC')
            ->whereIn('submission_id', $submissionIds);

        if ($args['statuses']) {
            $query->whereIn('status', $args['statuses']);
        }

        if ($args['form_title']) {
            $query->addSelect(['fluentform_forms.title'])
                ->leftJoin('fluentform_forms', 'fluentform_forms.id', '=', 'fluentform_subscriptions.form_id');
        }

        $subscriptions = $query->get();
        foreach ($subscriptions as $subscription) {
            $subscription->currency = ArrayHelper::get($currencyMaps, $subscription->submission_id);
        }
        return $subscriptions;
    }
}
