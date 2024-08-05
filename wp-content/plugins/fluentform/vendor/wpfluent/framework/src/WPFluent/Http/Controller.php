<?php

namespace FluentForm\Framework\Http;

use WP_REST_Response;
use ReflectionException;
use FluentForm\Framework\Foundation\App;
use FluentForm\Framework\Validator\ValidationException;

abstract class Controller
{
    /**
     * @var \FluentForm\Framework\Foundation\Application
     */
    protected $app = null;

    /**
     * @var \FluentForm\Framework\Request\Request
     */
    protected $request = null;

    /**
     * @var \FluentForm\Framework\Response\Response
     */
    protected $response = null;

    public function __construct()
    {
        $this->app = App::getInstance();
        $this->request = $this->app['request'];
        $this->response = $this->app['response'];
    }

    public function validate($data, $rules, $messages = [])
    {
        try {
            $validator = $this->app->validator->make($data, $rules, $messages);

            if ($validator->validate()->fails()) {
                throw new ValidationException(
                    'Unprocessable Entity!', 422, null, $validator->errors()
                );
            }

            return $data;

        } catch (ValidationException $e) {

            if (defined('REST_REQUEST') && REST_REQUEST) {
                throw $e;
            };

            $this->app->doCustomAction('handle_exception', $e);
        }
    }

    public function json($data = null, $code = 200)
    {
        return $this->response->json($data, $code);
    }

    public function send($data = null, $code = 200)
    {
        do_action( 'litespeed_control_set_nocache', 'fluentform api request' );

        nocache_headers();
        return $this->response->send($data, $code);
    }

    public function sendSuccess($data = null, $code = 200)
    {
        nocache_headers();
        do_action( 'litespeed_control_set_nocache', 'fluentform api request' );

        return $this->response->sendSuccess($data, $code);
    }

    public function sendError($data = null, $code = null)
    {
        nocache_headers();
        do_action( 'litespeed_control_set_nocache', 'fluentform api request' );

        return $this->response->sendError($data, $code);
    }

    public function __get($key)
    {
        try {
            return App::getInstance($key);
        } catch(ReflectionException $e) {
            $class = get_class($this);
            wp_die("Undefined property {$key} in $class");
        }
    }

    public function response($data, $code = 200)
    {
        do_action( 'litespeed_control_set_nocache', 'fluentform api request' );
        nocache_headers();

        return new WP_REST_Response($data, $code);
    }
}
