<?php

namespace FluentForm\App\Services\FormBuilder;

use FluentForm\App\Services\FormBuilder\ShortCodeParser;

class NotificationParser
{
    protected static $cache = null;

    /**
     * Parse Norifications
     *
     * @param array  $notifications
     * @param int    $insertId
     * @param array  $data
     * @param object $form
     *
     * @return bool $cache
     */
    public static function parse($notifications, $insertId, $data, $form, $cache = true)
    {
        if ($cache && ! is_null(static::$cache)) {
            return static::$cache;
        }

        foreach ($notifications as &$notification) {
            static::setRecepient($notification, $data);

            $notification = ShortCodeParser::parse(
                $notification,
                $insertId,
                $data,
                $form
            );
        }

        return $cache ? (static::$cache = $notifications) : $notifications;
    }

    protected static function setRecepient(&$notification, $data)
    {
        if (isset($notification['sendTo']) && 'field' == $notification['sendTo']['type']) {
            $notification['sendTo']['email'] = $data[$notification['sendTo']['field']];
        }
    }
}
