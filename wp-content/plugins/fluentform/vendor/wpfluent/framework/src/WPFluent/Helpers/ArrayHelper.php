<?php

namespace FluentForm\Framework\Helpers;

use FluentForm\Framework\Support\Arr;
use FluentForm\Framework\Support\Helper;

class ArrayHelper extends Arr
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public static function value($value)
    {
        return Helper::value($value);
    }

    public static function isTrue($array, $key)
    {
        $value = static::get($array, $key);

        if (is_bool($value)) {
            return $value;
        }

        if ('false' == $value || '0' == $value || !$value) {
            return false;
        }

        return true;
    }
}
