<?php

namespace FluentForm\App\Http\Requests;

use FluentForm\Framework\Foundation\RequestGuard;

class UserRequest extends RequestGuard
{
    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * @return array
     */
    public function sanitize()
    {
        $data = $this->all();

        $data['age'] = intval($data['age']);

        $data['address'] = wp_kses($data['address']);

        $data['name'] = sanitize_text_field($data['name']);

        return $data;
    }
}
