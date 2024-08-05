<?php

namespace FluentForm\App\Services\Roles;

use FluentForm\Framework\Support\Arr;

class RolesService
{
    public function getRoles($attributes = [])
    {
        if (!current_user_can('manage_options')) {
            return ([
                'capability' => [],
                'roles'      => [],
            ]);
        }

        $formatted = $this->getFormattedRoles();

        $capability = get_option('_fluentform_form_permission');

        if (is_string($capability)) {
            $capability = [];
        }

        return ([
            'capability' => $capability,
            'roles'      => $formatted,
        ]);
    }
    
    public function setCapability($attributes = [])
    {
        if (current_user_can('manage_options')) {
            $capability = wp_unslash(Arr::get($attributes, 'capability', []));
            
            foreach ($capability as $item) {
                if ('subscriber' == strtolower($item)) {
                    return ([
                        'message' => __('Sorry, you can not give access to the Subscriber role.', 'fluentform'),
                    ]);
                }
            }
            
            update_option('_fluentform_form_permission', $capability, 'no');
            
            return ([
                'message' => __('Successfully saved the role(s).', 'fluentform'),
            ]);
        } else {
            return ([
                'message' => __('Sorry, You can not update permissions. Only administrators can update permissions',
                    'fluentform')
            ]);
        }
    }
    
    private function getFormattedRoles()
    {
        if (!function_exists('get_editable_roles')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        
        $formatted = [];
        $roles = \get_editable_roles();
        
        foreach ($roles as $key => $role) {
            if ('administrator' == $key) {
                continue;
            }
            if ('subscriber' != $key) {
                $formatted[] = [
                    'name' => $role['name'],
                    'key'  => $key,
                ];
            }
        }
        return $formatted;
    }
}
