<?php

namespace FluentForm\App\Http\Controllers;


use FluentForm\App\Services\GlobalSearchService;

class GlobalSearchController extends Controller
{
    /**
     * Get the search links.
     *
     * @param  \FluentForm\App\Services\GlobalSearchService $globalSearchService
     * @return \WP_REST_Response
     */
    public function index(GlobalSearchService $globalSearchService)
    {
        return $this->sendSuccess(
            $globalSearchService->get()
        );
    }


}
