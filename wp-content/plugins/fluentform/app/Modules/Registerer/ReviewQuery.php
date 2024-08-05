<?php

namespace FluentForm\App\Modules\Registerer;

use FluentForm\App\Models\Form;
use FluentForm\App\Helpers\Helper;
use FluentForm\App\Http\Controllers\AdminNoticeController;

class ReviewQuery
{
    public function register()
    {
        if ($this->shouldRegister()) {
            add_action('fluentform/global_menu', [$this, 'show'], 99);
        }
    }

    protected function shouldRegister()
    {
        if (Helper::isFluentAdminPage() && !wp_doing_ajax()) {
            return Form::count() > 3;
        }

        return false;
    }

    public function show()
    {
        $notice = new AdminNoticeController();
        $msg = $this->getMessage();
        $notice->addNotice($msg);
        $notice->showNotice();
    }

    private function getMessage()
    {
        return [
            'name'    => 'review_query',
            'title'   => '',
            'message' => sprintf('Thank you for using Fluent Forms. We would be very grateful if you could share your experience and leave a review for us in %s',
                '<a target="_blank" href="https://wordpress.org/support/plugin/fluentform/reviews/#new-post">WordPress.org</a>. Your reviews inspire us to keep improving the plugin and delivering a better user experience.'),
            'links' => [
                [
                    'href'     => 'https://wordpress.org/support/plugin/fluentform/reviews/#new-post',
                    'btn_text' => 'Yes',
                    'btn_atts' => 'class="mr-1 el-button--success el-button--mini ff_review_now" data-notice_name="review_query"',
                ],
                [
                    'href'     => admin_url('admin.php?page=fluent_forms'),
                    'btn_text' => 'Maybe Later',
                    'btn_atts' => 'class="mr-1 el-button--info el-button--soft el-button--mini ff_nag_cross" data-notice_type="temp" data-notice_name="review_query"',
                ],
                [
                    'href'     => admin_url('admin.php?page=fluent_forms'),
                    'btn_text' => 'Do not show again',
                    'btn_atts' => 'class="text-button el-button--mini ff_nag_cross" data-notice_type="permanent" data-notice_name="review_query"',
                ],
            ],
        ];
    }
}
