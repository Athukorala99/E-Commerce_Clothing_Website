<?php

namespace FluentForm\App\Http\Controllers;

use FluentForm\Framework\Validator\ValidationException;
use FluentForm\App\Services\Manager\ManagerService;

class ManagersController extends Controller
{
    public function index(ManagerService $managerService)
    {
        $result = $managerService->getManagers($this->request->all());
        return $this->sendSuccess($result);
    }
    
    public function addManager(ManagerService $managerService)
    {
        try {
            $result = $managerService->addManager($this->request->all());
            return $this->sendSuccess($result);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }
    
    public function removeManager(ManagerService $managerService)
    {
        try {
            $result = $managerService->removeManager($this->request->all());
            return $this->sendSuccess($result);
        } catch (ValidationException $exception) {
            return $this->sendError($exception->errors(), 422);
        }
    }
}
