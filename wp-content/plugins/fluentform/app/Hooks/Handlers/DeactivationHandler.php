<?php

namespace FluentForm\App\Hooks\Handlers;

class DeactivationHandler
{
    public function handle()
    {
        $this->disableCronSchedule();
    }

    private function disableCronSchedule()
    {
        wp_clear_scheduled_hook('fluentform_do_scheduled_tasks');
        wp_clear_scheduled_hook('fluentform_do_email_report_scheduled_tasks');
    }
}
