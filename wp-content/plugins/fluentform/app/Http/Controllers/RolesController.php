<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\Framework\Validator\ValidationException;
use FluentForm\App\Services\Roles\RolesService;

class RolesController extends Controller
{
    public function index(RolesService $rolesService)
    {
        $result = $rolesService->getRoles($this->request->all());
        return $this->sendSuccess($result);
    }
    
    public function addCapability(RolesService $rolesService)
    {
        try {
            $result = $rolesService->setCapability($this->request->all());
            return $this->sendSuccess($result);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }
}
