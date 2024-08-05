<?php

/**
 * Enable Query Log
 */
if (!function_exists('fluentform_eql')) {
    function fluentform_eql()
    {
        defined('SAVEQUERIES') || define('SAVEQUERIES', true);
    }
}

/**
 * Get Query Log
 */
if (!function_exists('fluentform_gql')) {
    function fluentform_gql()
    {
        $result = [];
        foreach ((array) $GLOBALS['wpdb']->queries as $key => $query) {
            $result[++$key] = array_combine([
                'query', 'execution_time',
            ], array_slice($query, 0, 2));
        }

        return $result;
    }
}
