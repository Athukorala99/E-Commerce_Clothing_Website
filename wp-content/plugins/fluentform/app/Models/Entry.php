<?php

namespace FluentForm\App\Models;

use Exception;
use FluentForm\Framework\Support\Arr;

class Entry extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_submissions';

    /**
     * A formMeta is owned by a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    /**
     * An entry has many meta.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function entryMeta()
    {
        return $this->hasMany(EntryMeta::class, 'response_id', 'id');
    }

    /**
     * An entry has many logs.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'source_id', 'id');
    }

    /**
     * An entry has many entry details.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\HasMany
     */
    public function entryDetails()
    {
        return $this->hasMany(EntryDetails::class, 'submission_id', 'id');
    }

    public function paginateEntries($attributes = [])
    {
        $formId = Arr::get($attributes, 'form_id');
        $isFavourite = Arr::get($attributes, 'is_favourite');
        $status = Arr::get($attributes, 'status');
        $startDate = Arr::get($attributes, 'start_date');
        $endDate = Arr::get($attributes, 'end_date');
        $search = Arr::get($attributes, 'search');
        $wheres = Arr::get($attributes, 'wheres');

        $query = $this->orderBy('id', $attributes['sort_by'])
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

    public static function remove($entryIds)
    {
        static::whereIn('id', $entryIds)->delete();

        EntryMeta::whereIn('response_id', $entryIds)->delete();

        Log::whereIn('source_id', $entryIds)
            ->where('source_type', 'submission_item')
            ->delete();

        EntryDetails::whereIn('submission_id', $entryIds)->delete();

        //delete the pro models this way for now
        // todo: handle these pro models deletion
        // todo: delete files
        try {
            if(defined('FLUENTFORMPRO_VERSION')) {
                wpFluent()->table('fluentform_order_items')
                    ->whereIn('submission_id', $entryIds)
                    ->delete();

                wpFluent()->table('fluentform_transactions')
                    ->whereIn('submission_id', $entryIds)
                    ->delete();

                wpFluent()->table('fluentform_subscriptions')
                    ->whereIn('submission_id', $entryIds)
                    ->delete();
            }

            wpFluent()->table('ff_scheduled_actions')
                ->whereIn('origin_id', $entryIds)
                ->where('type', 'submission_action')
                ->delete();
        } catch (Exception $exception) {
            // ...
        }
    }
}
