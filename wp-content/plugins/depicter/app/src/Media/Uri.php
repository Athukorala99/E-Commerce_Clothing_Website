<?php

namespace Depicter\Media;


class Uri
{
	/**
	 * Convert path to url
	 *
	 * @param string $file  File path
	 *
	 * @return string
	 */
	public static function toUrl( $file ) {
		return str_replace(
			\Depicter::storage()->uploads()->getBaseDirectory(),
			\Depicter::storage()->uploads()->getBaseUrl(),
			$file
		);
	}
}
