<?php

namespace FluentForm\App\Modules\Entries;

class EntryQuery
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;
    protected $formModel;
    protected $responseModel;

    protected $formId = false;

    protected $per_page = 10;
    protected $page_number = 1;
    protected $status = false;
    protected $is_favourite = null;
    protected $sort_by = 'ASC';
    protected $search = false;
    protected $wheres = [];

    protected $startDate;
    protected $endDate;

    public function __construct()
    {
        $this->request = wpFluentForm('request');
        $this->formModel = wpFluent()->table('fluentform_forms');
        $this->responseModel = wpFluent()->table('fluentform_submissions');
    }

    public function getResponses()
    {
        $query = $this->responseModel
            ->where('form_id', $this->formId)
            ->orderBy('id', \FluentForm\App\Helpers\Helper::sanitizeOrderValue($this->sort_by));

        if ($this->per_page > 0) {
            $query = $query->limit($this->per_page);
        }

        if ($this->page_number > 0) {
            $query = $query->offset(($this->page_number - 1) * $this->per_page);
        }

        if ($this->is_favourite) {
            $query->where('is_favourite', $this->is_favourite);
            $query->where('status', '!=', 'trashed');
        } else {
            if (!$this->status) {
                $query->where('status', '!=', 'trashed');
            } else {
                $query->where('status', $this->status);
            }
        }

        if ($this->startDate && $this->endDate) {
            $endDate = $this->endDate;
            $endDate .= ' 23:59:59';
            $query->where('created_at', '>=', $this->startDate);
            $query->where('created_at', '<=', $endDate);
        }

        if ($this->search) {
            $searchString = $this->search;
            $query->where(function ($q) use ($searchString) {
                $q->where('id', 'LIKE', "%{$searchString}%")
                    ->orWhere('response', 'LIKE', "%{$searchString}%")
                    ->orWhere('status', 'LIKE', "%{$searchString}%")
                    ->orWhere('created_at', 'LIKE', "%{$searchString}%");
            });
        }

        if ($this->wheres) {
            foreach ($this->wheres as $where) {
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
                        $query->whereIn($column, $value);
                    } else {
                        $query->where($column, $operator, $value);
                    }
                }
            }
        }

        $total = $query->count();
        $responses = $query->get();
    
        $responses = apply_filters_deprecated(
            'fluentform_get_raw_responses',
            [
                $responses,
                $this->formId
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/get_raw_responses',
            'Use fluentform/get_raw_responses instead of fluentform_get_raw_responses.'
        );

        $responses = apply_filters('fluentform/get_raw_responses', $responses, $this->formId);

        return [
            'data'     => $responses,
            'paginate' => [
                'total'        => $total,
                'per_page'     => $this->per_page,
                'current_page' => $this->page_number,
                'last_page'    => ceil($total / $this->per_page),
            ],
        ];
    }

    public function getResponse($entryId)
    {
        return wpFluent()->table('fluentform_submissions')->find($entryId);
    }

    public function getNextResponse($entryId)
    {
        $query = $this->getNextPrevEntryQuery();

        $operator = 'ASC' == $this->sort_by ? '>' : '<';

        return $query->select('id')
            ->where('id', $operator, $entryId)
            ->orderBy('id', \FluentForm\App\Helpers\Helper::sanitizeOrderValue($this->sort_by))
            ->first();
    }

    public function getPrevResponse($entryId)
    {
        $query = $this->getNextPrevEntryQuery();

        $operator = 'ASC' == $this->sort_by ? '<' : '>';

        $orderBy = 'ASC' == $this->sort_by ? 'DESC' : 'ASC';

        return $query->select('id')
            ->where('id', $operator, $entryId)
            ->orderBy('id', $orderBy)
            ->first();
    }

    protected function getNextPrevEntryQuery()
    {
        $query = wpFluent()->table('fluentform_submissions')->limit(1);

        if ($this->is_favourite) {
            $query->where('is_favourite', $this->is_favourite)->where('status', '!=', 'trashed');
        } else {
            if (!$this->status) {
                $query->where('status', '!=', 'trashed');
            } else {
                $query->where('status', $this->status);
            }
        }

        if ($this->search) {
            $query->where('response', 'LIKE', "%{$this->search}%");
        }

        return $query->where('form_id', $this->formId);
    }

    public function groupCount($form_id)
    {
        $statuses = $this->responseModel
            ->select($this->responseModel->raw('status, COUNT(*) as count'))
            ->where('form_id', $form_id)
            ->groupBy('status')
            ->get();

        $counts = [];
        foreach ($statuses as $status) {
            $counts[$status->status] = $status->count;
        }

        $counts['all'] = array_sum($counts);
        if (isset($counts['trashed'])) {
            $counts['all'] -= $counts['trashed'];
        }

        $favorites = wpFluent()
            ->table('fluentform_submissions')
            ->where('form_id', $form_id)
            ->where('is_favourite', 1)
            ->where('status', '!=', 'trashed')
            ->count();

        $counts['favourites'] = $favorites;

        return $counts;
    }
}
