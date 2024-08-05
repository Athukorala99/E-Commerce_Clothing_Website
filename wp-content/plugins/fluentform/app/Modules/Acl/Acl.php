<?php

namespace FluentForm\App\Modules\Acl;

use FluentForm\Framework\Helpers\ArrayHelper;

class Acl
{
    public static $capability = '';

    public static $role = '';

    public static function getPermissionSet()
    {
        $data = [
            'fluentform_dashboard_access',
            'fluentform_forms_manager',
            'fluentform_entries_viewer',
            'fluentform_manage_entries',
            'fluentform_view_payments',
            'fluentform_manage_payments',
            'fluentform_settings_manager',
            'fluentform_full_access',
        ];
    
        $data = apply_filters_deprecated(
            'fluentform_permission_set',
            [
                $data
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/permission_set',
            'Use fluentform/permission_set instead of fluentform_permission_set.'
        );

        return apply_filters('fluentform/permission_set', $data);
    }

    /**
     * Fluentform access controll permissions assignment.
     */
    public static function setPermissions()
    {
        // Fire an event letting others know that fluentform
        // is going to assign permission set to a role.
        do_action_deprecated(
            'before_fluentform_permission_set_assignment',
            [

            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/before_permission_set_assignment',
            'Use fluentform/before_permission_set_assignment instead of before_fluentform_permission_set_assignment.'
        );

        do_action('fluentform/before_permission_set_assignment');

        // The permissions that fluentform supports altogether.
        $permissions = self::getPermissionSet();

        // The role that fluentform will use
        // to attach the permission set.
        $role = get_role('administrator');

        if ($role) {
            // Looping through permission set to add to the role.
            foreach ($permissions as $permission) {
                $role->add_cap($permission);
            }
        }

        // Fire an event letting others know that fluentform is
        // done with the permission assignment to the role.
        do_action_deprecated(
            'after_fluentform_permission_set_assignment',
            [

            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/after_permission_set_assignment',
            'Use fluentform/after_permission_set_assignment instead of after_fluentform_permission_set_assignment.'
        );
        do_action('fluentform/after_permission_set_assignment');
    }

    /**
     * Verify if current user has a fluentform permission.
     *
     * @param $permission
     * @param null   $formId
     * @param string $message
     * @param bool   $json
     *
     * @throws \Exception
     */
    public static function verify(
        $permission,
        $formId = null,
        $message = 'You do not have permission to perform this action.',
        $json = true
    ) {
        static::verifyNonce();

        $allowed = static::hasPermission($permission, $formId);

        if (!$allowed) {
            if ($json) {
                wp_send_json_error([
                    'message' => $message,
                ], 422);
            } else {
                throw new \Exception($message);
            }
        }
    }

    public static function hasPermission($permissions, $formId = false)
    {
        $userCapability = static::getCurrentUserCapability();

        if ($userCapability) {
            return $userCapability;
        } else {
            if (current_user_can('fluentform_full_access')) {
                return true;
            }

            $permissions = (array) $permissions;

            foreach ($permissions as $permission) {
                $allowed = current_user_can($permission);

                if ($allowed) {
                    $allowed = apply_filters_deprecated(
                        'fluentform_verify_user_permission_' . $permission,
                        [
                            $allowed,
                            $formId
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/verify_user_permission_' . $permission,
                        'Use fluentform/verify_user_permission_' . $permission . ' instead of fluentform_verify_user_permission_' . $permission
                    );

                    return apply_filters('fluentform/verify_user_permission_' . $permission, $allowed, $formId);
                }
            }

            return false;
        }
    }

    public static function hasAnyFormPermission($form_id = false)
    {
        $allPermissions = static::getPermissionSet();

        foreach ($allPermissions as $permission) {
            if (static::hasPermission($permission, $form_id)) {
                return true;
            }
        }

        return false;
    }

    public static function getCurrentUserCapability()
    {
        if (static::$capability) {
            return static::$capability;
        }

        if (is_user_logged_in()) {
            static::$capability = static::findUserCapability(wp_get_current_user());
        } else {
            static::$capability = false;
        }

        return apply_filters('fluentform/current_user_capability', static::$capability);
    }

    public static function findUserCapability($user)
    {
        if (!$user) {
            return false;
        }

        if (static::isSuperMan($user)) {
            return 'manage_options';
        }

        $capabilities = get_option('_fluentform_form_permission');

        if (is_string($capabilities)) {
            $capabilities = (array) $capabilities;
        }

        if (!$capabilities) {
            return false;
        }

        foreach ($capabilities as $capability) {
            if ($user->has_cap($capability)) {
                return $capability;
            }
        }

        return false;
    }

    public static function getCurrentUserRole()
    {
        $user = wp_get_current_user();

        return static::$role = $user->roles[0];
    }

    public static function verifyNonce($key = 'fluent_forms_admin_nonce')
    {
        if (!wp_doing_ajax()) {
            return;
        }

        $nonce = wpFluentForm('request')->get($key);

        if (!wp_verify_nonce($nonce, $key)) {
            $message = __('Nonce verification failed, please try again.', 'fluentform');
            $message = apply_filters('fluentform/nonce_error', $message);

            wp_send_json_error([
                'message' => $message,
            ], 422);
        }
    }

    public static function getReadablePermissions()
    {
        return [
            'fluentform_dashboard_access' => [
                'title'   => __('View Forms', 'fluentform'),
                'depends' => [],
            ],
            'fluentform_forms_manager' => [
                'title'   => __('Manage Forms', 'fluentform'),
                'depends' => [
                    'fluentform_dashboard_access',
                ],
            ],
            'fluentform_entries_viewer' => [
                'title'   => __('View Entries', 'fluentform'),
                'depends' => [
                    'fluentform_dashboard_access',
                ],
            ],
            'fluentform_manage_entries' => [
                'title'   => __('Manage Entries', 'fluentform'),
                'depends' => [
                    'fluentform_entries_viewer',
                ],
            ],
            'fluentform_view_payments' => [
                'title'   => __('View Payments', 'fluentform'),
                'depends' => [
                    'fluentform_dashboard_access',
                    'fluentform_entries_viewer',
                ],
            ],
            'fluentform_manage_payments' => [
                'title'   => __('Manage Payments', 'fluentform'),
                'depends' => [
                    'fluentform_view_payments',
                ],
            ],
            'fluentform_settings_manager' => [
                'title'   => __('Manage Settings', 'fluentform'),
                'depends' => [],
            ],
            'fluentform_full_access' => [
                'title'   => __('Full Access', 'fluentform'),
                'depends' => [],
            ],
        ];
    }

    public static function getUserPermissions($user = false)
    {
        if (is_numeric($user)) {
            $user = get_user_by('ID', $user);
        }

        if (!$user) {
            return [];
        }

        $permissionSet = static::getPermissionSet();
        $isSuperMan = static::isSuperMan($user);
        $capability = static::findUserCapability($user);

        if ($isSuperMan || $capability) {
            if ($isSuperMan) {
                // $permissionSet[] = 'administrator';
            }

            return $permissionSet;
        }

        $userPermissions = array_values(array_intersect(array_keys($user->allcaps), $permissionSet));

        return apply_filters('fluentform/current_user_permissions', $userPermissions);
    }

    public static function isSuperMan($user = false)
    {
        if ($user) {
            return $user->has_cap('manage_options');
        } else {
            return current_user_can('manage_options');
        }
    }

    public static function getCurrentUserPermissions()
    {
        return static::getUserPermissions(wp_get_current_user());
    }

    public static function attachPermissions($user, $permissions)
    {
        if (is_numeric($user)) {
            $user = get_user_by('ID', $user);
        }

        if (!$user) {
            return false;
        }

        if (user_can($user, 'manage_options')) {
            return $user;
        }

        $allPermissions = static::getPermissionSet();

        foreach ($allPermissions as $permission) {
            $user->remove_cap($permission);
        }

        $permissions = array_intersect($allPermissions, $permissions);

        foreach ($permissions as $permission) {
            $user->add_cap($permission);
        }

        return $user;
    }
}
