<?php

namespace FluentForm\App\Modules\Acl;
/**
 * @deprecated deprecated use FluentForm\App\Http\Controllers\RoleManagerController
 */
class RoleManager
{
    public function getRoles()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_success([
                'capability' => [],
                'roles'      => [],
            ], 200);
        }

        $roles = \get_editable_roles();
        $formatted = [];
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

        $capability = get_option('_fluentform_form_permission');

        if (is_string($capability)) {
            $capability = [];
        }

        wp_send_json_success([
            'capability' => $capability,
            'roles'      => $formatted,
        ], 200);
    }

    public function setRoles()
    {
        if (current_user_can('manage_options')) {
            $capability = wp_unslash(wpFluentForm('request')->get('capability', []));

            foreach ($capability as $item) {
                if ('subscriber' == strtolower($item)) {
                    wp_send_json_error([
                        'message' => __('Sorry, you can not give access to the Subscriber role.', 'fluentform'),
                    ], 423);
                }
            }

            update_option('_fluentform_form_permission', $capability, 'no');
            wp_send_json_success([
                'message' => __('Successfully saved the role(s).', 'fluentform'),
            ], 200);
        } else {
            wp_send_json_error([
                'message' => __('Sorry, You can not update permissions. Only administrators can update permissions', 'fluentform'),
            ], 423);
        }
    }

    public function verifyPermissionSet()
    {
        $availablePermissions = Acl::getPermissionSet();
        $currentCapability = $this->currentUserFormFormCapability();
        if (!$currentCapability) {
            return;
        }

        // Give permission to internal issues
        foreach ($availablePermissions as $permission) {
            add_filter('fluentform/verify_user_permission_' . $permission, function ($allowed) {
                return true;
            });
        }

        // Give permission to menu items
        add_filter('fluentform/dashboard_capability', function ($capability) use ($currentCapability) {
            return $currentCapability;
        });

        add_filter('fluentform/settings_capability', function ($capability) use ($currentCapability) {
            return $currentCapability;
        });
    }

    public function currentUserFormFormCapability()
    {
        if (current_user_can('manage_options')) {
            return 'manage_options';
        }
        if (!is_user_logged_in()) {
            return false;
        }

        $capabilities = get_option('_fluentform_form_permission');
        if (is_string($capabilities)) {
            $capabilities = (array) $capabilities;
        }
        if (!$capabilities) {
            return;
        }
        foreach ($capabilities as $capability) {
            if (current_user_can($capability)) {
                return $capability;
            }
        }
        return false;
    }
}
