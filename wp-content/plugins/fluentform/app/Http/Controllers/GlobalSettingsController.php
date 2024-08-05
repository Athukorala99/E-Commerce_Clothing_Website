<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\Framework\Validator\ValidationException;
use FluentForm\App\Services\GlobalSettings\GlobalSettingsService;

class GlobalSettingsController extends Controller
{
    public function index(GlobalSettingsService $globalSettingsService)
    {
        $result = $globalSettingsService->get($this->request->all());
        return $this->sendSuccess($result);
    }
    
    public function store(GlobalSettingsService $globalSettingsService)
    {
        try {
            $result = $globalSettingsService->store($this->request->all());
            return $this->sendSuccess($result);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }
}
