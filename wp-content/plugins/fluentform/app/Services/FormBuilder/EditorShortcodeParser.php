<?php

namespace FluentForm\App\Services\FormBuilder;

use FluentForm\App\Services\Browser\Browser;

class EditorShortcodeParser
{
    /**
     * Available dynamic short codes
     *
     * @var null
     */
    private static $dynamicShortcodes = null;

    /**
     * mappings of methods to parse the shortcode
     *
     * @var array
     */
    private static $handlers = [
        'ip'         => 'parseIp',
        'date.m/d/Y' => 'parseDate',
        'date.d/m/Y' => 'parseDate',

        'embed_post.ID'         => 'parsePostProperties',
        'embed_post.post_title' => 'parsePostProperties',
        'embed_post.permalink'  => 'parsePostProperties',
        'http_referer'          => 'parseWPProperties',

        'wp.admin_email' => 'parseWPProperties',
        'wp.site_url'    => 'parseWPProperties',
        'wp.site_title'  => 'parseWPProperties',

        'user.ID'           => 'parseUserProperties',
        'user.display_name' => 'parseUserProperties',
        'user.first_name'   => 'parseUserProperties',
        'user.last_name'    => 'parseUserProperties',
        'user.user_email'   => 'parseUserProperties',
        'user.user_login'   => 'parseUserProperties',

        'browser.name'     => 'parseBrowserProperties',
        'browser.platform' => 'parseBrowserProperties',

        'get.param_name'           => 'parseRequestParam',
        'random_string.param_name' => 'parseRandomString',
    ];

    /**
     * Filter dynamic shortcodes in input value
     *
     * @param string $value
     *
     * @return string
     */
    public static function filter($value, $form)
    {
        if (0 === strpos($value, '{ ')) {
            // it's the css
            return $value;
        }

        if (is_null(static::$dynamicShortcodes)) {
            static::$dynamicShortcodes = fluentFormEditorShortCodes();
        }

        $filteredValue = '';

        foreach (static::parseValue($value) as $handler) {
            if (isset(static::$handlers[$handler])) {
                return call_user_func_array(
                    [__CLASS__, static::$handlers[$handler]],
                    ['{' . $handler . '}', $form]
                );
            }

            if (false !== strpos($handler, 'get.')) {
                return static::parseRequestParam($handler);
            }
            if (false !== strpos($handler, 'random_string.')) {
                return static::parseRandomString($handler);
            }

            if (false !== strpos($handler, 'user.')) {
                $value = self::parseUserProperties($handler);
                if (is_array($value) || is_object($value)) {
                    return '';
                }
                return $value;
            }

            if (false !== strpos($handler, 'date.')) {
                return self::parseDate($handler);
            }

            if (false !== strpos($handler, 'embed_post.meta.')) {
                $key = substr(str_replace(['{', '}'], '', $value), 16);
                global $post;
                if ($post) {
                    $value = get_post_meta($post->ID, $key, true);
                    if (! is_array($value) && ! is_object($value)) {
                        return $value;
                    }
                }
                return '';
            }

            if (false !== strpos($handler, 'embed_post.')) {
                return self::parsePostProperties($handler, $form);
            }

            if (false !== strpos($handler, 'cookie.')) {
                $scookieProperty = substr($handler, strlen('cookie.'));

                return wpFluentForm('request')->cookie($scookieProperty);
            }

            if (false !== strpos($handler, 'dynamic.')) {
                $dynamicKey = substr($handler, strlen('dynamic.'));
                // maybe has fallback value
                $dynamicKey = explode('|', $dynamicKey);
                $fallBack = '';
                $ref = '';
                if (count($dynamicKey) > 1) {
                    $fallBack = $dynamicKey[1];
                }
                $ref = $dynamicKey[0];

                if ('payment_summary' == $ref) {
                    return '<div class="ff_dynamic_value ff_dynamic_payment_summary" data-ref="payment_summary"><div class="ff_payment_summary"></div><div class="ff_payment_summary_fallback">' . $fallBack . '</div></div>';
                }

                return '<span class="ff_dynamic_value" data-ref="' . $ref . '" data-fallback="' . $fallBack . '">' . $fallBack . '</span>';
            }

            // if it's multi line then just return
            if (false !== strpos($handler, PHP_EOL)) { // most probably it's a css
                return '{' . $handler . '}';
            }

            $handlerArray = explode('.', $handler);

            if (count($handlerArray) > 1) {
                // it's a grouped handler
                $group = array_shift($handlerArray);
                $parsedValue = apply_filters('fluentform_editor_shortcode_callback_group_' . $group, '{' . $handler . '}', $form, $handlerArray);
                return apply_filters('fluentform/editor_shortcode_callback_group_' . $group, $parsedValue, $form, $handlerArray);
            }

            $parsedValue = apply_filters('fluentform_editor_shortcode_callback_' . $handler, '{' . $handler . '}', $form);
            return apply_filters('fluentform/editor_shortcode_callback_' . $handler, $parsedValue, $form);
        }

        return $filteredValue;
    }

    /**
     * Parse request query param.
     *
     * @param string    $value
     * @param \stdClass $form
     *
     * @return string
     */
    public static function parseRequestParam($value)
    {
        $exploded = explode('.', $value);
        $param = array_pop($exploded);
        $value = wpFluentForm('request')->get($param);

        if (! $value) {
            return '';
        }

        if (is_array($value)) {
            return esc_attr(implode(', ', $value));
        }

        return esc_attr($value);
    }

    /**
     * Parse the curly braced shortcode into array
     *
     * @param string $value
     *
     * @return mixed
     */
    public static function parseValue($value)
    {
        if (! is_array($value)) {
            return preg_split(
                '/{(.*?)}/',
                $value,
                -1,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
            );
        }

        return $value;
    }

    /**
     * Declare all parsers and must be [private] static methods
     */

    /**
     * Parse loggedin user properties
     *
     * @param string $value
     *
     * @return string
     */
    private static function parseUserProperties($value, $form = null)
    {
        if ($user = wp_get_current_user()) {
            $prop = substr(str_replace(['{', '}'], '', $value), 5);

            if (false !== strpos($prop, 'meta.')) {
                $metaKey = substr($prop, strlen('meta.'));
                $userId = $user->ID;
                $data = get_user_meta($userId, $metaKey, true);
                if (! is_array($data)) {
                    return $data;
                }
                return '';
            }

            return $user->{$prop};
        }

        return '';
    }

    /**
     * Parse embedded post properties
     *
     * @param string $value
     *
     * @return string
     */
    private static function parsePostProperties($value, $form = null)
    {
        global $post;
        if (! $post) {
            return '';
        }

        $key = $prop = substr(str_replace(['{', '}'], '', $value), 11);

        if (false !== strpos($key, 'author.')) {
            $authorProperty = substr($key, strlen('author.'));
            $authorId = $post->post_author;
            if ($authorId) {
                $data = get_the_author_meta($authorProperty, $authorId);
                if (! is_array($data)) {
                    return $data;
                }
            }
            return '';
        } elseif (false !== strpos($key, 'meta.')) {
            $metaKey = substr($key, strlen('meta.'));
            $postId = $post->ID;
            $data = get_post_meta($postId, $metaKey, true);
            if (! is_array($data)) {
                return $data;
            }
            return '';
        } elseif (false !== strpos($key, 'acf.')) {
            $metaKey = substr($key, strlen('acf.'));
            $postId = $post->ID;
            if (function_exists('get_field')) {
                $data = get_field($metaKey, $postId, true);
                if (! is_array($data)) {
                    return $data;
                }
                return '';
            }
        }

        if ('permalink' == $prop) {
            return site_url(esc_attr(wpFluentForm('request')->server('REQUEST_URI')));
        }

        if (property_exists($post, $prop)) {
            return $post->{$prop};
        }
        return '';
    }

    /**
     * Parse WP Properties
     *
     * @param string $value
     *
     * @return string
     */
    private static function parseWPProperties($value, $form = null)
    {
        if ('{wp.admin_email}' == $value) {
            return get_option('admin_email');
        }
        if ('{wp.site_url}' == $value) {
            return site_url();
        }
        if ('{wp.site_title}' == $value) {
            return get_option('blogname');
        }
        if ('{http_referer}' == $value) {
            return wp_get_referer();
        }

        return '';
    }

    /**
     * Parse browser/user-agent properties
     *
     * @param string $value
     *
     * @return string
     */
    private static function parseBrowserProperties($value, $form = null)
    {
        $browser = new Browser();
        if ('{browser.name}' == $value) {
            return $browser->getBrowser();
        } elseif ('{browser.platform}' == $value) {
            return $browser->getPlatform();
        }

        return '';
    }

    /**
     * Parse ip shortcode
     *
     * @param string $value
     *
     * @return string
     */
    private static function parseIp($value, $form = null)
    {
        $ip = wpFluentForm('request')->getIp();
        return $ip ? $ip : $value;
    }

    /**
     * Parse date shortcode
     *
     * @param string $value
     *
     * @return string
     */
    private static function parseDate($value, $form = null)
    {
        $format = substr(str_replace(['}', '{'], '', $value), 5);
        $date = date($format, strtotime(current_time('mysql')));
        return $date ? $date : '';
    }

    /**
     * Parse request query param.
     *
     * @param string    $value
     * @param \stdClass $form
     *
     * @return string
     */
    public static function parseQueryParam($value)
    {
        $exploded = explode('.', $value);
        $param = array_pop($exploded);
        $value = wpFluentForm('request')->get($param);

        if (! $value) {
            return '';
        }

        if (is_array($value)) {
            return sanitize_textarea_field(implode(', ', $value));
        }

        return sanitize_textarea_field($value);
    }

    /**
     * Generate random a string with prefix
     *
     * @param $value
     *
     * @return string
     */
    public static function parseRandomString($value)
    {
        $exploded = explode('.', $value);
        $prefix = array_pop($exploded);
        $value = $prefix . uniqid();

        return apply_filters('fluentform/shortcode_parser_callback_random_string', $value, $prefix, new static());
    }
}
