<?php
namespace Averta\WordPress\Utility;


class Escape
{
    /**
     * Escape HTML.
     *
     * @param string $input
     *
     * @return string
     */
    public static function html( $input )
    {
        return esc_html( $input );
    }

    /**
     * Escape Attribute.
     *
     * @param string $input
     *
     * @return string
     */
    public static function attribute( $input )
    {
        return esc_attr( $input );
    }

    /**
     * Escape URL.
     *
     * @param string $input
     *
     * @return string
     */
    public static function url( $input )
    {
        return esc_url( $input );
    }

    /**
     * Escape SQL.
     *
     * @param string $input
     *
     * @return string
     */
    public static function sql( $input )
    {
        return esc_sql( $input );
    }

    /**
     * Escape Inline Javascript.
     *
     * @param string $input
     *
     * @return string
     */
    public static function js( $input )
    {
        return esc_js( $input );
    }

    /**
     * Escape Inline Javascript.
     *
     * @param string $input
     *
     * @return string
     */
    public static function textarea( $input )
    {
        return esc_textarea( $input );
    }

    /**
     * Escape post content.
     *
     * @param string $input
     *
     * @return string
     */
    public static function content( $input )
    {
        return wp_kses_post( $input );
    }

}
