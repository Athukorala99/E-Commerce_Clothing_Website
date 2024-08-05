<?php

namespace FluentForm\App\Services\Report;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Models\EntryDetails;
use FluentForm\App\Models\Submission;
use FluentForm\App\Modules\Entries\Entries;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class ReportHelper
{
    public static function generateReport($form, $statuses = [])
    {
        $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'element', 'options']);
        $inputLabels = FormFieldsParser::getAdminLabels($form, $formInputs);

        $elements = [];
        foreach ($formInputs as $inputName => $input) {
            $elements[$inputName] = $input['element'];
            if ('select_country' == $input['element']) {
                $formInputs[$inputName]['options'] = getFluentFormCountryList();
            }
        }

        $reportableInputs = Helper::getReportableInputs();
        $formReportableInputs = array_intersect($reportableInputs, array_values($elements));
        $reportableInputs = Helper::getSubFieldReportableInputs();
        $formSubFieldInputs = array_intersect($reportableInputs, array_values($elements));
        if (!$formReportableInputs && !$formSubFieldInputs) {
            return [
                'report_items'  => (object)[],
                'total_entries' => 0,
            ];
        }

        $inputs = [];
        $subfieldInputs = [];
        foreach ($elements as $elementKey => $element) {
            if (in_array($element, $formReportableInputs)) {
                $inputs[$elementKey] = $element;
            }
            if (in_array($element, $formSubFieldInputs)) {
                $subfieldInputs[$elementKey] = $element;
            }
        }

        $reports = static::getInputReport($form->id, array_keys($inputs), $statuses);

        $subFieldReports = static::getSubFieldInputReport($form->id, array_keys($subfieldInputs), $statuses);
        $reports = array_merge($reports, $subFieldReports);
        foreach ($reports as $reportKey => $report) {
            $reports[$reportKey]['label'] = $inputLabels[$reportKey];
            $reports[$reportKey]['element'] = Arr::get($inputs, $reportKey, []);
            $reports[$reportKey]['options'] = $formInputs[$reportKey]['options'];
        }

        return [
            'report_items'  => $reports,
            'total_entries' => static::getEntryCounts($form->id, $statuses),
            'browsers'      => static::getbrowserCounts($form->id, $statuses),
            'devices'       => static::getDeviceCounts($form->id, $statuses),
        ];
    }

    public static function getInputReport($formId, $fieldNames, $statuses = [])
    {
        if (!$fieldNames) {
            return [];
        }

        $reports = EntryDetails::select(['field_name', 'sub_field_name', 'field_value'])
            ->where('form_id', $formId)
            ->whereIn('field_name', $fieldNames)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereHas('submission', function ($q) use ($statuses) {
                        return $q->whereIn('status', $statuses);
                    });
                })
            ->selectRaw('COUNT(field_name) AS total_count')
            ->groupBy(['field_name', 'field_value'])
            ->get();

        $formattedReports = [];
        foreach ($reports as $report) {
            $formattedReports[$report->field_name]['reports'][] = [
                'value'     => maybe_unserialize($report->field_value),
                'count'     => $report->total_count,
                'sub_field' => $report->sub_field_name,
            ];

            $formattedReports[$report->field_name]['total_entry'] = static::getEntryTotal($report->field_name, $formId,
                $statuses);
        }

        return $formattedReports;
    }

    public static function getSubFieldInputReport($formId, $fieldNames, $statuses)
    {
        if (!$fieldNames) {
            return [];
        }

        $reports = EntryDetails::select(['field_name', 'sub_field_name', 'field_value'])
            ->selectRaw('COUNT(field_name) AS total_count')
            ->where('form_id', $formId)
            ->whereIn('field_name', $fieldNames)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereHas('submission', function ($q) use ($statuses) {
                        return $q->whereIn('status', $statuses);
                    });
                })
            ->groupBy(['field_name', 'field_value', 'sub_field_name'])
            ->get()->toArray();
        return static::getFormattedReportsForSubInputs($reports, $formId, $statuses);
    }

    protected static function getFormattedReportsForSubInputs($reports, $formId, $statuses)
    {
        if (!count($reports)) {
            return [];
        }
        $formattedReports = [];
        foreach ($reports as $report) {
            static::setReportForSubInput((array)$report, $formattedReports);
        }
        foreach ($formattedReports as $fieldName => $val) {
            $formattedReports[$fieldName]['total_entry'] = static::getEntryTotal(
                Arr::get($report,'field_name'),
                $formId,
                $statuses
            );
            $formattedReports[$fieldName]['reports'] = array_values(
                $formattedReports[$fieldName]['reports']
            );
        }
        return $formattedReports;
    }

    protected static function setReportForSubInput($report, &$formattedReports)
    {
        $filedValue = maybe_unserialize(Arr::get($report,'field_value'));

        if (is_array($filedValue)) {
            foreach ($filedValue as $fVal) {
                static::setReportForSubInput(
                    array_merge($report, ['field_value' => $fVal]),
                    $formattedReports
                );
            }
        } else {
            $value = Arr::get($report,'sub_field_name') . ' : ' . $filedValue;
            $count = Arr::get($formattedReports, $report['field_name'] . '.reports.' . $value . '.count');
            $count = $count ? $count + Arr::get($report,'total_count') : Arr::get($report,'total_count');

            $formattedReports[$report['field_name']]['reports'][$value] = [
                'value'     => $value,
                'count'     => $count,
                'sub_field' => $report['sub_field_name'],
            ];
        }
    }

    public static function getEntryTotal($fieldName, $formId, $statuses = false)
    {
        return EntryDetails::select('id')->where('form_id', $formId)
            ->where('field_name', $fieldName)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereHas('submission', function ($q) use ($statuses) {
                        return $q->whereIn('status', $statuses);
                    });
                }
            )
            ->distinct(['field_name','submission_id'])
            ->count();
    }

    private static function getEntryCounts($formId, $statuses = false)
    {
        return Submission::where('form_id', $formId)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereIn('status', $statuses);
                })
            ->when(!$statuses, function ($q) {
                return $q->where('status', '!=', 'trashed');
            })->count();
    }

    public static function getBrowserCounts($formId, $statuses = false)
    {
        return static::getCounts($formId, 'browser', $statuses);
    }

    public static function getDeviceCounts($formId, $statuses = false)
    {
        return static::getCounts($formId, 'device', $statuses);
    }

    private static function getCounts($formId, $for, $statuses)
    {
        $deviceCounts = Submission::select([
            "$for",
        ])
            ->selectRaw('COUNT(id) as total_count')
            ->where('form_id', $formId)
            ->when(
                is_array($statuses) && (count($statuses) > 0),
                function ($q) use ($statuses) {
                    return $q->whereIn('status', $statuses);
                })
            ->when(!$statuses, function ($q) {
                return $q->where('status', '!=', 'trashed');
            })
            ->groupBy("$for")->get();

        $formattedData = [];
        foreach ($deviceCounts as $deviceCount) {
            $formattedData[$deviceCount->{$for}] = $deviceCount->total_count;
        }
        return $formattedData;
    }

    public static function maybeMigrateData($formId)
    {
        // We have to check if we need to migrate the data
        if ('yes' == Helper::getFormMeta($formId, 'report_data_migrated')) {
            return true;
        }
        // let's migrate the data
        $unmigratedData = Submission::select(['id', 'response'])
            ->where('form_id', $formId)
            ->doesntHave('entryDetails')
            ->get();

        if (!$unmigratedData) {
            return Helper::setFormMeta($formId, 'report_data_migrated', 'yes');
        }
        $entries = new Entries();
        foreach ($unmigratedData as $datum) {
            $value = json_decode($datum->response, true);
            $entries->recordEntryDetails($datum->id, $formId, $value);
        }
        return true;
    }
}
