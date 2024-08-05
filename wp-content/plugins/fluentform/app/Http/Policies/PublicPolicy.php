<?php

namespace FluentForm\App\Http\Policies;


use FluentForm\Framework\Request\Request;
use FluentForm\Framework\Foundation\Policy;

class PublicPolicy extends Policy
{
    /**
     * Check permission for any method
     *
     * @param \FluentForm\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return true;
    }
}
