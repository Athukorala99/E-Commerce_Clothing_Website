<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Foundation\Policy;

class RoleManagerPolicy extends Policy
{
    public function index()
    {
        return Acl::hasPermission('fluentform_full_access');
    }
}
