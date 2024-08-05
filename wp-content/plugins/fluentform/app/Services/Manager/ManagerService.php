<?php

namespace FluentForm\App\Services\Manager;

use FluentForm\App\Modules\Acl\Acl;
use FluentForm\Framework\Support\Arr;
use FluentForm\Framework\Validator\ValidationException;
use FluentForm\Framework\Validator\Validator;

class ManagerService
{
    public function getManagers($attributes = [])
    {
        $limit = Arr::get($attributes, 'per_page', 10);
        $page = Arr::get($attributes, 'page', 1);
        $offset = $page == 1 ? 0 : ($page - 1) * $limit;

        $query = new \WP_User_Query([
            'meta_key'     => '_fluent_forms_has_role',
            'meta_value'   => 1,
            'meta_compare' => '=',
            'number'       => $limit,
            'offset'       => $offset,
        ]);

        $managers = [];

        foreach ($query->get_results() as $user) {
            $managers[] = [
                'id'          => $user->ID,
                'first_name'  => $user->first_name,
                'last_name'   => $user->last_name,
                'email'       => $user->user_email,
                'permissions' => Acl::getUserPermissions($user),
                'roles'       => $this->getUserRoles($user->roles)
            ];
        }

        $total = $query->get_total();

        return ([
            'managers'  => $managers,
            'total' => $total,
            'permissions' => Acl::getReadablePermissions(),
        ]);
    }
    
    public function addManager($attributes = [])
    {
        $manager = Arr::get($attributes, 'manager');
        
        $this->validate($manager);
        
        $permissions = Arr::get($manager, 'permissions', []);
        
        $user = get_user_by('email', $manager['email']);
        
        if (!$user) {
            throw new ValidationException('', 0, null, ['message' => 'Please Provide Valid Email']);
        }
        
        Acl::attachPermissions($user, $permissions);
        
        update_user_meta($user->ID, '_fluent_forms_has_role', 1);
        
        $updatedUser = [
            'id'          => $user->ID,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => $user->user_email,
            'permissions' => Acl::getUserPermissions($user)
        ];
        
        return ([
            'message' => __('Manager has been saved.', 'fluentform'),
            'manager' => $updatedUser
        ]);
    }
    
    public function removeManager($attributes = [])
    {
        $userID = intval(Arr::get($attributes, 'id'));
        $user = get_user_by('ID', $userID);
        
        if (!$user) {
            return ([
                'message' => __('Associate user could not be found', 'fluentform'),
            ]);
        }
        
        Acl::attachPermissions($user, []);
        
        delete_user_meta($user->ID, '_fluent_forms_has_role');
        
        $deletedUser = [
            'id'          => $user->ID,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => $user->user_email,
            'permissions' => Acl::getUserPermissions($user)
        ];
        
        return ([
            'message' => __('Manager has been removed.', 'fluentform'),
            'manager' => $deletedUser
        ]);
    }
    
    private function validate($manager)
    {
        $rules = [
            'permissions' => 'required',
            'email'       => 'required|email',
        ];
        
        $validatorInstance = new Validator();
        $validator = $validatorInstance->make($manager, $rules);
        
        $errors = null;
        
        if ($validator->validate()->fails()) {
            $errors = $validator->errors();
        }
        
        if (!isset($errors['email'])) {
            $user = get_user_by('email', $manager['email']);
            
            if (!$user) {
                $errors['email'] = [
                    'no_user' => __('We could not found any user with this email.', 'fluentform'),
                ];
            }
        }
        
        if (!isset($errors['permissions'])) {
            $message = $this->dependencyValidate($manager['permissions']);
            
            if ($message) {
                $errors['permissions'] = [
                    'dependency' => $message,
                ];
            }
        }
        
        if ($errors) {
            throw new ValidationException('', 0, null, [
                'errors' => $errors,
            ]);
        }
    }
    
    private function dependencyValidate($permissions)
    {
        $allPermissions = Acl::getReadablePermissions();
        
        foreach ($permissions as $permission) {
            $depends = Arr::get($allPermissions, $permission . '.depends', []);
            
            if ($depends && $more = array_values(array_diff($depends, $permissions))) {
                $message = $allPermissions[$permission]['title'] . ' requires permission: ';
                
                foreach ($more as $i => $p) {
                    $joiner = $i ? ', ' : '';
                    $message = $message . $joiner . $allPermissions[$p]['title'];
                }
                
                return $message;
            }
        }
    }

    private function getUserRoles($roles)
    {
        $roleStr = '';
        if (count($roles) > 1) {
            foreach ($roles as $role) {
                $roleStr .= $role . ', ';
            }
        } else {
            $roleStr = Arr::get($roles, '0');
        }

        return $roleStr;
    }
}
