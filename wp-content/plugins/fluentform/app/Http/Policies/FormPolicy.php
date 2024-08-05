<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Request\Request;
use FluentForm\Framework\Foundation\Policy;

class FormPolicy extends Policy
{
    /**
     * Check permission for any method
     *
     * @param  \FluentForm\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return Acl::hasPermission('fluentform_forms_manager');
    }

    public function index()
    {
        return Acl::hasPermission('fluentform_dashboard_access');
    }

    public function templates()
    {
        return Acl::hasAnyFormPermission();
    }

    public function updateModuleStatus()
    {
        return Acl::hasPermission('fluentform_settings_manager');
    }

    public function ping()
    {
        return Acl::hasAnyFormPermission();
    }
}
