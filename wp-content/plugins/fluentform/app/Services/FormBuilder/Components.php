<?php

namespace FluentForm\App\Services\FormBuilder;

use Closure;

class Components implements \JsonSerializable
{
    /**
     * $items [Components list]
     *
     * @var array
     */
    protected $items = [];

    /**
     * Build the object instance
     *
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Add a component into list [$items]
     *
     * @param string $name
     * @param array  $component
     * @param string $group     ['general'|'advanced']
     *
     * @return $this
     */
    public function add($name, array $component, $group)
    {
        if (isset($this->items[$group])) {
            $this->items[$group][$name] = $component;
        }

        return $this;
    }

    /**
     * Remove a component from the list [$items]
     *
     * @param string $name
     * @param string $group ['general'|'advanced']
     *
     * @return $this
     */
    public function remove($name, $group)
    {
        if (isset($this->items[$group])) {
            unset($this->items[$group][$name]);
        }

        return $this;
    }

    /**
     * Modify an existing component
     *
     * @param string  $name
     * @param Closure $callback [to modify the component within]
     * @param string  $group
     *
     * @return $this
     */
    public function update($name, Closure $callback, $group)
    {
        $element = $callback($this->items[$group][$name]);
        $this->items[$group][$name] = $element;
        return $this;
    }

    /**
     * Sort the components in list [$items]
     *
     * @param string $sortBy [key to sort by]
     *
     * @return $this
     */
    public function sort($sortBy = 'index')
    {
        foreach ($this->items as $group => &$items) {
            usort($items, function ($a, $b) {
                if (@$a['index'] == @$b['index']) {
                    return 0;
                }
                return @$a['index'] < @$b['index'] ? -1 : 1;
            });
        }

        return $this;
    }

    /**
     * Return array [$items]
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * Return array [$items]
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Getter to hook proxy call
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, ['general', 'advanced'])) {
            return new GroupSetterProxy($this, $key);
        }
    }
}
