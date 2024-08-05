<?php

namespace FluentForm\App\Services\Analytics;

use FluentForm\App\Models\FormAnalytics;
use FluentForm\App\Models\FormMeta;
use FluentForm\App\Services\Browser\Browser;
use FluentForm\Framework\Helpers\ArrayHelper;

class AnalyticsService
{
    
    public function reset($formId)
    {
        FormAnalytics::where('form_id', $formId)->delete();
        FormMeta::where('meta_key', '_total_views')->where('form_id',$formId)->delete();
        
        return ([
            'message' => __('Form Analytics has been successfully reset', 'fluentform'),
        ]);
    }
    
    public function store($formId)
    {
        $userId = null;
        if ($user = wp_get_current_user()) {
            $userId = $user->ID;
        }
        $browser = new Browser();
        $request = wpFluentForm('request');
        
        $data = [
            'count'      => 1,
            'form_id'    => $formId,
            'user_id'    => $userId,
            'ip'         => $request->getIp(),
            'browser'    => $browser->getBrowser(),
            'platform'   => $browser->getPlatform(),
            'created_at' => current_time('mysql'),
            'source_url' => $request->server('HTTP_REFERER', ''),
        ];
        
        $query = FormAnalytics::where('ip', $data['ip'])
            ->where('form_id', $data['form_id'])
            ->where('source_url', $data['source_url']);
        
        if (($record = $query->first())) {
            $query->update(['count' => ++$record->count]);
        } else {
            FormAnalytics::insert($data);
            $this->increaseTotalViews($formId);
        }
    }
    
    
    /**
     * Store (create/update) total view of a form
     *
     * @param int $formId
     */
    private function increaseTotalViews($formId)
    {
        $hasCount = FormMeta::where('meta_key', '_total_views')
            ->where('form_id', $formId)
            ->first();
        
        if ($hasCount) {
            FormMeta::where('id', $hasCount->id)
                ->update([
                    'value' => intval($hasCount->value) + 1,
                ]);
        } else {
            FormMeta::insert([
                'value'    => 1,
                'form_id'  => $formId,
                'meta_key' => '_total_views',
            ]);
        }
    }
    
}
