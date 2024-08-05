<?php

namespace FluentForm\App\Modules\Acl;

use FluentForm\Framework\Helpers\ArrayHelper;
/**
 * @deprecated deprecated use FluentForm\App\Http\Controllers\RoleManagerController
 */
class Managers
{
    /**
     * Request object
     *
     * @var \FluentForm\Framework\Request\Request $request
     */
    protected $request;

    public function __construct()
    {
        $this->request = wpFluentForm('request');
    }

    public function get()
    {
        $limit = $this->request->get('per_page', 10);
        $page = $this->request->get('page', 1);

        $query = new \WP_User_Query([
            'meta_key'     => '_fluent_forms_has_role',
            'meta_value'   => 1,
            'meta_compare' => '=',
            'number'       => $limit,
            'paged'        => $page,
        ]);

        $managers = [];

        foreach ($query->get_results() as $user) {
            $managers[] = [
                'id'          => $user->ID,
                'first_name'  => $user->first_name,
                'last_name'   => $user->last_name,
                'email'       => $user->user_email,
                'permissions' => Acl::getUserPermissions($user),
            ];
        }

        wp_send_json([
            'managers' => [
                'data'  => $managers,
                'total' => $query->get_total(),
            ],
            'permissions' => Acl::getReadablePermissions(),
        ], 200);
    }

    public function store()
    {
        $manager = $this->request->get('manager');

        $this->validate();

        $permissions = ArrayHelper::get($manager, 'permissions', []);

        $user = get_user_by('email', $manager['email']);

        Acl::attachPermissions($user, $permissions);

        update_user_meta($user->ID, '_fluent_forms_has_role', 1);

        wp_send_json([
            'message' => __('Manager has been saved.', 'fluentform'),
        ], 200);
    }

    public function remove()
    {
        $user = get_user_by('ID', $this->request->get('id'));

        if (!$user) {
            return $this->sendError([
                'message' => __('Associate user could not be found', 'fluentform'),
            ]);
        }

        Acl::attachPermissions($user, []);

        delete_user_meta($user->ID, '_fluent_forms_has_role');

        wp_send_json([
            'message' => __('Manager has been removed.', 'fluentform'),
        ], 200);
    }

    private function validate()
    {
        $manager = $this->request->get('manager');

        $rules = [
            'permissions' => 'required',
            'email'       => 'required|email',
        ];

        $validator = wpFluentForm('validator')->make($manager, $rules);

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
            $this->sendError([
                'errors' => $errors,
            ]);
        }

        return true;
    }

    private function sendError($data, $code = 423)
    {
        wp_send_json($data, $code);
    }

    public function dependencyValidate($permissions)
    {
        $allPermissions = Acl::getReadablePermissions();

        foreach ($permissions as $permission) {
            $depends = ArrayHelper::get($allPermissions, $permission . '.depends', []);

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
}
