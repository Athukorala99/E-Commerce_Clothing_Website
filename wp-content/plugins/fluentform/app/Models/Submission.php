<?php

namespace FluentForm\App\Models;

use Exception;
use FluentForm\Framework\Support\Arr;

class Submission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_submissions';

    /**
     * A submission is owned by a User.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    /**
     * A submission is owned by a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    /**
     * A submission has many meta.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function submissionMeta()
    {
        return $this->hasMany(SubmissionMeta::class, 'response_id', 'id');
    }

    /**
     * A submission has many logs.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'source_id', 'id');
    }

    /**
     * A submission has many entry details.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function entryDetails()
    {
        return $this->hasMany(EntryDetails::class, 'submission_id', 'id');
    }

    public function customQuery($attributes = [])
    {
        $entryType = Arr::get($attributes, 'entry_type');
        $dateRange = Arr::get($attributes, 'date_range');
        $isFavourite = false;
        $status = $entryType;

        // We have to handle favorites separately because status and favorites are different
        if ('favorites' === $entryType) {
            $isFavourite = true;
            $status = false;
        }

        $formId = Arr::get($attributes, 'form_id');
        $startDate = Arr::get($dateRange, 0);
        $endDate = Arr::get($dateRange, 1);
        $search = Arr::get($attributes, 'search');
        $sortBy = Arr::get($attributes, 'sort_by', 'DESC');

        $wheres = [];
        $paymentStatuses = Arr::get($attributes, 'payment_statuses');

        if ($paymentStatuses && is_array($paymentStatuses)) {
            $wheres[] = ['payment_status', $paymentStatuses];
        }

        $query = $this->orderBy('id', $sortBy)
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->when($isFavourite, function ($q) {
                return $q->where('is_favourite', true);
            })
            ->where(function ($q) use ($status) {
                $operator = '=';

                if (!$status) {
                    $operator = '!=';
                    $status = 'trashed';
                }

                return $q->where('status', $operator, $status);
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $endDate .= ' 23:59:59';

                return $q->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
            })
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($q) use ($search) {
                    return $q->where('id', 'LIKE', "%{$search}%")
                        ->orWhere('response', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%")
                        ->orWhere('created_at', 'LIKE', "%{$search}%");
                });
            })
            ->when($wheres, function ($q) use ($wheres) {
                foreach ($wheres as $where) {
                    if (is_array($where) && count($where) > 1) {
                        if (count($where) > 2) {
                            $column = $where[0];
                            $operator = $where[1];
                            $value = $where[2];
                        } else {
                            $column = $where[0];
                            $operator = '=';
                            $value = $where[1];
                        }

                        if (is_array($value)) {
                            return $q->whereIn($column, $value);
                        } else {
                            return $q->where($column, $operator, $value);
                        }
                    }
                }
            });

        return $query;
    }

    public function paginateEntries($attributes = [])
    {
        $formId = Arr::get($attributes, 'form_id');
        $query = $this->customQuery($attributes);
        $response = $query->paginate();
        $response = apply_filters_deprecated(
            'fluentform_get_raw_responses',
            [
                $response,
                $formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_raw_responses',
            'Use fluentform/get_raw_responses instead of fluentform_get_raw_responses.'
        );

        return apply_filters('fluentform/get_raw_responses', $response, $formId);
    }

    public function findPreviousSubmission($attributes = [])
    {
        $query = $this->customQuery($attributes);

        $sortBy = Arr::get($attributes, 'sort_by', 'DESC');

        $operator = 'ASC' === $sortBy ? '<' : '>';

        $entryId = Arr::get($attributes, 'entry_id');

        $columns = Arr::get($attributes, 'columns', 'id');

        $submission = $query->select($columns)->where('id', $operator, $entryId)->first();

        return apply_filters('fluentform/next_submission', $submission, $entryId, $attributes);
    }

    public function findAdjacentSubmission($attributes = [])
    {
        $sortBy = Arr::get($attributes, 'sort_by', 'DESC');

        $direction = Arr::get($attributes, 'direction', 'next');

        $operator = 'ASC' === $sortBy && 'previous' === $direction ? '>' : '<';

        if ('previous' === $direction) {
            $operator = 'ASC' === $sortBy ? '>' : '<';
        } else {
            $operator = 'ASC' === $sortBy ? '<' : '>';
            $attributes['sort_by'] = 'ASC' === $sortBy ? 'DESC' : 'ASC';
        }

        $entryId = Arr::get($attributes, 'entry_id');

        $columns = Arr::get($attributes, 'columns', 'id');

        $query = $this->customQuery($attributes);

        $submission = $query->select($columns)->where('id', $operator, $entryId)->first();

        return apply_filters('fluentform/next_submission', $submission, $entryId, $attributes);
    }

    public function countByGroup($formId)
    {
        $statuses = $this->selectRaw('status, COUNT(*) as count')
            ->where('form_id', $formId)
            ->groupBy('status')
            ->get();

        $counts = [];

        foreach ($statuses as $status) {
            $counts[$status->status] = (int) $status->count;
        }

        $counts['all'] = array_sum($counts);

        if (isset($counts['trashed'])) {
            $counts['all'] -= $counts['trashed'];
        }

        $favorites = $this->where('form_id', $formId)
            ->where('is_favourite', 1)
            ->where('status', '!=', 'trashed')
            ->count();

        $counts['favorites'] = $favorites;

        return array_merge([
            'unread'  => 0,
            'read'    => 0,
            'trashed' => 0,
        ], $counts);
    }

    public function amend($id, $data = [])
    {
        $this->where('id', $id)->update($data);
    }

    public static function remove($submissionIds)
    {
        static::whereIn('id', $submissionIds)->delete();

        SubmissionMeta::whereIn('response_id', $submissionIds)->delete();

        Log::whereIn('source_id', $submissionIds)
            ->where('source_type', 'submission_item')
            ->delete();

        EntryDetails::whereIn('submission_id', $submissionIds)->delete();

        //delete the pro models this way for now
        // todo: handle these pro models deletion
        try {
            if(defined('FLUENTFORMPRO_VERSION')) {
                wpFluent()->table('fluentform_order_items')
                    ->whereIn('submission_id', $submissionIds)
                    ->delete();

                wpFluent()->table('fluentform_transactions')
                    ->whereIn('submission_id', $submissionIds)
                    ->delete();

                wpFluent()->table('fluentform_subscriptions')
                    ->whereIn('submission_id', $submissionIds)
                    ->delete();
            }

            wpFluent()->table('ff_scheduled_actions')
                ->whereIn('origin_id', $submissionIds)
                ->where('type', 'submission_action')
                ->delete();

        } catch (Exception $exception) {
            // ...
        }
    }

    public function allSubmissions($attributes = []) {
        $customQuery = $this->customQuery($attributes);
        $search = Arr::get($attributes, 'search');

        $result = $customQuery
            ->with([
                'form' => function ($q) {
                    $q->select(['id', 'title']);
                }
            ])
            ->select(['id', 'form_id', 'status', 'created_at', 'browser', 'currency', 'total_paid'])
            ->when($search, function ($q) use ($search){
                return $q->orWhereHas('form', function ($q) use ($search) {
                    return $q->orWhere('title', 'LIKE', "%{$search}%");
                });
            })
            ->paginate()
            ->toArray();

        foreach ($result['data'] as &$entry) {
            $entry['entry_url'] = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $entry['form_id'] . '#/entries/' . $entry['id']);
            $entry['human_date'] = human_time_diff(strtotime($entry['created_at']), strtotime(current_time('mysql')));
        }

        $result['available_forms'] = $this->availableForms();

        return $result;
    }

    public function availableForms()
    {
        $form = new Form();
        return $form->select('id', 'title')->get();
    }

    public static function report($attributes)
    {
        $from = date('Y-m-d H:i:s', strtotime('-30 days'));
        $to = date('Y-m-d H:i:s', strtotime('+1 days'));
        $formId = Arr::get($attributes, 'form_id');
        $status = Arr::get($attributes, 'entry_status');
        $start = Arr::get($attributes, 'date_range.0', '');
        $end = Arr::get($attributes, 'date_range.1', '');

        if ($start && $startTime = (strtotime($start) + 24 * 60 * 60)) {
            if ($start === $end) {
                $startTime = strtotime($start);
            }
            $from = date('Y-m-d H:i:s', $startTime);
        }

        if ($end  && $endTime = (strtotime($end) + 24 * 60 * 60)) {
            $to = date('Y-m-d H:i:s', $endTime);
        }

        $period = new \DatePeriod(new \DateTime($from), new \DateInterval('P1D'), new \DateTime($to));

        $range = [];

        foreach ($period as $date) {
            $range[$date->format('Y-m-d')] = 0;
        }

        $items = self::selectRaw('DATE(created_at) AS date')
            ->selectRaw('COUNT(id) AS count')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->when($formId, function ($q) use ($formId) {
                return $q->where('form_id', $formId);
            })
            ->when($status, function ($q2) use ($status) {
                return $q2->where('status', $status);
            })
            ->get();
        

        foreach ($items as $item) {
            $range[$item->date] = $item->count;
        }

        return $range;
    }
}
