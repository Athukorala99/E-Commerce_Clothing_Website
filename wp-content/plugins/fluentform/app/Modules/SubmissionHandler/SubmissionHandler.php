<?php

namespace FluentForm\App\Modules\SubmissionHandler;

use FluentForm\App\Services\Form\SubmissionHandlerService;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Validator\ValidationException;

class SubmissionHandler
{
    protected $request = null;
    
    public function __construct(Application $app)
    {
        $this->request = $app['request'];
    }
    public function submit()
    {
        try {
            parse_str($this->request->get('data'), $data);     // Parse the url encoded data from the request object.
            $data['_wp_http_referer'] = isset($data['_wp_http_referer']) ? sanitize_url(urldecode($data['_wp_http_referer'])) : '';
            $this->request->merge(['data' => $data]);           // Merge it back again to the request object.
    
            $formId = (int) $this->request->get('form_id');
            $response = (new SubmissionHandlerService())->handleSubmission($data, $formId);
            return wp_send_json_success($response);
        } catch (ValidationException $e) {
            return wp_send_json($e->errors(), $e->getCode());
        }
    }
}
