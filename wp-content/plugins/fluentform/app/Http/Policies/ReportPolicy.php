<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Request\Request;
use FluentForm\Framework\Foundation\Policy;
use FluentForm\Framework\Support\Arr;

class ReportPolicy extends Policy
{
    /**
     * Check permission for any method
     *
     * @param  \FluentForm\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return Acl::hasPermission('fluentform_dashboard_access');
    }

    public function form(Request $request)
    {
        return Acl::hasPermission('fluentform_entries_viewer', intval($request->get('form_id')));
    }

    public function submissions()
    {
        return Acl::hasPermission('fluentform_entries_viewer');
    }
}
