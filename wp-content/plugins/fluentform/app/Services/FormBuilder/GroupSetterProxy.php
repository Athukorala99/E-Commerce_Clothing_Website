<?php

namespace FluentForm\App\Services\FormBuilder;

class GroupSetterProxy
{
    /**
     * Element group
     *
     * @var string
     */
    protected $group = null;

    /**
     * Form builder components collection
     *
     * @var \FluentForm\App\Services\FormBuilder\Components
     */
    protected $collection = null;

    public function __construct($collection, $group)
    {
        $this->group = $group;
        $this->collection = $collection;
    }

    /**
     * Dynamic call method
     *
     * @param string $method
     * @param array  $params
     *
     * @return \FluentForm\App\Services\FormBuilder\Components
     */
    public function __call($method, $params)
    {
        call_user_func_array([
            $this->collection, $method,
        ], array_merge($params, [$this->group]));

        return $this->collection;
    }
}
