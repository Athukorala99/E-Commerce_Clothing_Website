<?php

namespace FluentForm\App\Http\Controllers;

use Exception;
use FluentForm\App\Services\Logger\Logger;

class LogController extends Controller
{
    public function get(Logger $logger)
    {
        try {
            return $this->sendSuccess(
                $logger->get($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => __('Something went wrong, please try again!', 'fluentform'),
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function getFilters(Logger $logger)
    {
        try {
            return $this->sendSuccess(
                $logger->getFilters($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => __('Something went wrong, please try again!', 'fluentform'),
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function remove(Logger $logger)
    {
        try {
            return $this->sendSuccess(
                $logger->remove($this->request->all())
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
