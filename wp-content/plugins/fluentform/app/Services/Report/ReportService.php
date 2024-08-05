<?php

namespace FluentForm\App\Services\Report;

use Exception;
use FluentForm\App\Models\Form;
use FluentForm\App\Models\Submission;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;


class ReportService
{
    /**
     * Get Form Report
     * @param array $attr
     * @return array|mixed $response
     * @throws Exception
     */
    public function form($attr = [])
    {
        $formId = (int) Arr::get($attr, 'form_id');
        ReportHelper::maybeMigrateData($formId);
        try {
            $form = Form::findOrFail($formId);
        } catch (Exception $e) {
            throw new \Exception("The form couldn't be found.");
        }

        $statuses = Arr::get($attr, 'statuses', []);
        return ReportHelper::generateReport($form, $statuses);
    }

    /**
     * Get Submissions Report
     *
     * @throws Exception
     */
    public function submissions($args)
    {
        try {
            return Submission::report($args);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


}
