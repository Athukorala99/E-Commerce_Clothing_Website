<?php
namespace Depicter\Media;

/**
 * Media File Class
 *
 * @package Depicter\Media
 */
class Url
{

	/**
	 * check if url is valid
	 * @param $url
	 *
	 * @return bool
	 */
	public function isUrl( $url ) {
		return (bool) filter_var( $url, FILTER_VALIDATE_URL );
	}

	/**
	 * convert url to path
	 * @param $url
	 *
	 * @return false|string
	 */
	public function toUri( $url ) {
		$siteUrl = rtrim( get_site_url(), "/") . "/";
		$relativePath = str_replace( $siteUrl, '', $url );
		if ( file_exists( ABSPATH . $relativePath ) ) {
			return ABSPATH . $relativePath;
		}
		return false;
	}

	/**
	 * check if url is external or not
	 * @param $url
	 *
	 * @return bool
	 */
	public function isExternal( $url ) {
		$siteUrl = parse_url( get_site_url() );
		$siteHost = !empty( $siteUrl['path'] ) ? $siteUrl['host'] . $siteUrl['path'] : $siteUrl['host'];

		$urlHost = parse_url( $url, PHP_URL_HOST );
		// check for multisite that links are like subdirectory not subdomain
		$urlHost = !empty( $siteUrl['path'] ) && strpos( $url, $urlHost . $siteUrl['path'] ) ? $urlHost . $siteUrl['path'] : $urlHost;

		return $siteHost != $urlHost && !strpos( $siteHost, $urlHost) && !strpos( $urlHost, $siteHost );
	}
}
