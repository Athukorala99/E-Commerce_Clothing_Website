<?php

namespace FluentForm\Framework\Support;

Class Sanitizer
{
	public static function sanitizeEmail($arg)
	{
		return sanitize_email($arg);
	}

	public static function sanitizeFileName($arg)
	{
		return sanitize_file_name($arg);
	}

	public static function sanitizeHtmlClass($arg)
	{
		return sanitize_html_class($arg);
	}

	public static function sanitizeKey($arg)
	{
		return sanitize_key($arg);
	}

	public static function sanitizeMeta($arg)
	{
		return sanitize_meta($arg);
	}

	public static function sanitizeMimeType($arg)
	{
		return sanitize_mime_type($arg);
	}

	public static function sanitizeOption($arg)
	{
		return sanitize_option($arg);
	}

	public static function sanitizeSqlOrderby($arg)
	{
		return sanitize_sql_orderby($arg);
	}

	public static function sanitizeTextField($arg)
	{
		return sanitize_text_field($arg);
	}

	public static function sanitizeTitle($arg)
	{
		return sanitize_title($arg);
	}

	public static function sanitizeTitleForQuery($arg)
	{
		return sanitize_title_for_query($arg);
	}

	public static function sanitizeTitleWithDashes($arg)
	{
		return sanitize_title_with_dashes($arg);
	}

	public static function sanitizeUser($arg)
	{
		return sanitize_user($arg);
	}

	public static function wpFilterPostKses($arg)
	{
		return wp_filter_post_kses($arg);
	}

	public static function wpFilterNohtmlKses($arg)
	{
		return wp_filter_nohtml_kses($arg);
	}

	public static function escAttr($arg)
	{
		return esc_attr($arg);
	}

	public static function escHtml($arg)
	{
		return esc_html($arg);
	}

	public static function escJs($arg)
	{
		return esc_js($arg);
	}

	public static function escTextarea($arg)
	{
		return esc_textarea($arg);
	}

	public static function escUrl($arg)
	{
		return esc_url($arg);
	}

	public static function escUrlRaw($arg)
	{
		return esc_url_raw($arg);
	}

	public static function escXml($arg)
	{
		return esc_xml($arg);
	}

	public static function kses($arg)
	{
		return wp_kses($arg);
	}

	public static function ksesPost($arg)
	{
		return wp_kses_post($arg);
	}

	public static function ksesData($arg)
	{
		return wp_kses_data($arg);
	}

	public static function escHtml__($arg)
	{
		return esc_html__($arg);
	}

	public static function escAttr__($arg)
	{
		return esc_attr__($arg);
	}

	public static function escHtmlE($arg)
	{
		return esc_html_e($arg);
	}

	public static function escAttrE($arg)
	{
		return esc_attr_e($arg);
	}

	public static function escHtmlX($arg)
	{
		return esc_html_x($arg);
	}

	public static function escAttrX($arg)
	{
		return esc_attr_x($arg);
	}

	public static function sanitize(array $data = [], array $rules = [])
	{
		foreach ($rules as $key => $ruleString) {

			$methods = explode('|', $ruleString);

			foreach ($methods as $method) {

				$suffix = '';

				if (Str::endsWith($method, '__')) {
					$suffix = '__';
				}
				
				$method = Str::camel($method) . $suffix;

				Arr::set($data, $key, static::$method(Arr::get($data, $key)));
			}
		}

		return $data;
	}
}
