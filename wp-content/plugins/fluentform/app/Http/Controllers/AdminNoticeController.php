<?php
namespace FluentForm\App\Http\Controllers;

use FluentForm\Framework\Helpers\ArrayHelper;

class AdminNoticeController extends Controller
{
    private $notice = false;
    private $noticeDisabledTime = 60 * 60 * 24 * 15; // 15 days
    private $noticePrefKey = '_fluentform_notice_pref';
    private $pref = false;
    
    public function showNotice()
    {
        if ($notice = $this->notice) {
            $this->renderNotice($notice, $notice['name']);
        }
    }
    
    public function addNotice($notice)
    {
        $this->notice = $notice;
    }
    
    public function noticeActions()
    {
        $noticeName = sanitize_text_field($this->request->get('notice_name'));
        $actionType = sanitize_text_field($this->request->get('action_type', 'permanent'));
        
        if ($noticeName == 'track_data_notice') {
            $notificationPref = $this->getNoticePref();
            $notificationPref[$noticeName] = [
                'status'           => 'no',
                'email_subscribed' => 'no',
                'timestamp'        => time()
            ];
            update_option($this->noticePrefKey, $notificationPref, 'no');
            $this->pref = $notificationPref;
        } elseif ($noticeName == 'review_query') {
            $this->disableNotice($noticeName, $actionType);
            return $this->sendSuccess(true);
        }
        $this->disableNotice($noticeName, $actionType);
        
        return $this->sendSuccess([
            'message' => 'success'
        ]);
        die();
    }
    
    public function renderNotice($notice, $notice_key = false)
    {
        if (!$this->hasPermission()) {
            return;
        }
        if ($notice_key) {
            if (!$this->shouldShowNotice($notice_key)) {
                return;
            }
        }
        wp_enqueue_style('fluentform_admin_notice', fluentformMix('css/admin_notices.css'));
        wp_enqueue_script('fluentform_admin_notice', fluentformMix('js/admin_notices.js'), array(
            'jquery'
        ), FLUENTFORM_VERSION);
        wpFluentForm('view')->render('admin.notices.info', array(
            'notice'        => $notice,
            'show_logo'     => false,
            'show_hide_nag' => false,
            'logo_url'      => fluentformMix('img/fluent_icon.png')
        ));
    }
    
    public function hasNotice()
    {
        return ($this->notice) ? true : false;
    }
    
    private function disableNotice($notice_key, $type = 'temp')
    {
        $noticePref = $this->getNoticePref();
        $noticePref[$notice_key][$type] = time();
        update_option($this->noticePrefKey, $noticePref, 'no');
        $this->pref = $noticePref;
    }
    
    public function getNoticePref()
    {
        if (!$this->pref) {
            $this->pref = is_array(get_option($this->noticePrefKey)) ? get_option($this->noticePrefKey) : [];
        }
        return $this->pref;
    }
    
    public function shouldShowNotice($noticeName)
    {
        $notificationPref = $this->getNoticePref();
        if (!$notificationPref) {
            return true;
        }
        
        $maybeHidePermanently = isset($notificationPref[$noticeName]['permanent']);
        if ($maybeHidePermanently) {
            return false;
        }
        
        if ($this->haveTempHideNotice($noticeName)) {
            return false;
        }
        
        return true;
    }
    
    private function haveTempHideNotice($noticeName)
    {
        $tempHideNotices = get_option($this->noticePrefKey);
        if ($tempHideNotices && isset($tempHideNotices[$noticeName]['temp'])) {
            $tempDisabledTime = $tempHideNotices[$noticeName]['temp'];
            $difference = time() - intval($tempDisabledTime);
            
            if ($difference < $this->noticeDisabledTime) {
                return true;
            }
            return false;
        }
        return false;
    }
    
    private function hasPermission()
    {
        return current_user_can('fluentform_dashboard_access');
    }
}
