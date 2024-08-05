<?php

namespace FluentForm\App\Http\Policies;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Request\Request;
use FluentForm\Framework\Foundation\Policy;

class SubmissionPolicy extends Policy
{
    /**
     * Check permission for any method
     *
     * @param  \FluentForm\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return Acl::hasPermission('fluentform_entries_viewer');
    }

    public function handleBulkActions()
    {
        return Acl::hasPermission('fluentform_manage_entries');
    }

    public function updateStatus()
    {
        return $this->handleBulkActions();
    }

    public function toggleIsFavorite()
    {
        return $this->handleBulkActions();
    }

    public function remove()
    {
        return $this->handleBulkActions();
    }
}
