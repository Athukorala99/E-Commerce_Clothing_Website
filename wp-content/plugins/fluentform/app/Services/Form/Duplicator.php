<?php

namespace FluentForm\App\Services\Form;

use FluentForm\App\Models\Form;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class Duplicator
{
    public function duplicateFormMeta(Form $form, Form $existingForm)
    {
        $extras = [];

        foreach ($existingForm->formMeta as $meta) {
            if ('notifications' == $meta->meta_key || '_pdf_feeds' == $meta->meta_key) {
                $extras[$meta->meta_key][] = $meta;

                continue;
            }
            if ("ffc_form_settings_generated_css" == $meta->meta_key || "ffc_form_settings_meta" == $meta->meta_key) {
                $meta->value = str_replace('ff_conv_app_' . $existingForm->id, 'ff_conv_app_' . $form->id, $meta->value);
            }

            $form->formMeta()->create([
                'meta_key' => $meta->meta_key,
                'value'    => $meta->value,
            ]);
        }

        $pdfFeedMap = $this->getPdfFeedMap($form, $extras);

        if (array_key_exists('notifications', $extras)) {
            $extras = $this->notificationWithPdfMap($extras, $pdfFeedMap);

            foreach ($extras['notifications'] as $notify) {
                $notifyData = [
                    'meta_key' => $notify->meta_key,
                    'value'    => $notify->value,
                ];

                $form->formMeta()->create($notifyData);
            }
        }
    }


    public function maybeDuplicateFiles($form, $existingForm, $data)
    {
        if (
            isset($data['form_fields']) &&
            $formFields = \json_decode($data['form_fields'], true)
        ) {
            $fields = Arr::get($formFields, 'fields', []);
            foreach ($fields as $field) {
                if (
                    "chained_select" === $field['element'] &&
                    "file" === Arr::get($field, 'settings.data_source.type', '') &&
                    $metaKey = Arr::get($field, 'settings.data_source.meta_key', '')
                ) {
                    // duplicate csv file for chained select field, if uploaded.
                    $path = wp_upload_dir()['basedir'] . FLUENTFORM_UPLOAD_DIR;
                    $target = $path . '/' . $metaKey . '_' . $existingForm->id . '.csv';
                    if (file_exists($target)) {
                        copy($target, $path . '/' . $metaKey . '_' . $form->id . '.csv');
                    }
                }
            }
        }
    }

    /**
     * Map pdf feed ID to replace with duplicated PDF feed ID when duplicating form
     *
     * @param  \FluentForm\App\Models\Form $form
     * @param  array                       $formMeta
     * @return array                       $pdfFeedMap
     */
    private function getPdfFeedMap(Form $form, $formMeta)
    {
        $pdfFeedMap = [];

        if (array_key_exists('_pdf_feeds', $formMeta)) {
            foreach ($formMeta['_pdf_feeds'] as $pdf_feed) {
                $pdfData = [
                    'meta_key' => $pdf_feed->meta_key,
                    'value'    => $pdf_feed->value,
                ];
                $pdfFeedMap[$pdf_feed->id] = $form->formMeta()->insertGetId($pdfData);
            }
        }

        return $pdfFeedMap;
    }

    /**
     * Map notification data with PDF feed map
     *
     * @param  array $formMeta
     * @param  array $pdfFeedMap
     * @return array $formMeta
     */
    private function notificationWithPdfMap($formMeta, $pdfFeedMap)
    {
        foreach ($formMeta['notifications'] as $key => $notification) {
            $notificationValue = json_decode($notification->value);
            $pdf_attachments = [];
            $hasPdfAttachments = isset($notificationValue->pdf_attachments) && count($notificationValue->pdf_attachments);

            if ($hasPdfAttachments) {
                foreach ($notificationValue->pdf_attachments as $attachment) {
                    $pdf_attachments[] = json_encode($pdfFeedMap[$attachment]);
                }
            }
            $notificationValue->pdf_attachments = $pdf_attachments;
            $notification->value = json_encode($notificationValue);

            $formMeta['notifications'][$key] = $notification;
        }

        return $formMeta;
    }
}
