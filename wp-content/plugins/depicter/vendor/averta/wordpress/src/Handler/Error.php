<?php
namespace Averta\WordPress\Handler;

class Error
{
	/**
     * Mark something as being incorrectly called.
     *
     * The current behavior is to trigger a user error if `WP_DEBUG` is true.
     *
     * @param string $function The function that was called.
     * @param string $message  A message explaining what has been done incorrectly.
     * @param string $version  The version of WordPress where the message was added.
    */
	public static function doingWrong( $function, $message, $version )
	{
		_doing_it_wrong( $function, $message, $version );
	}

	/**
	 * Generates a user-level error/warning/notice message
	 *
	 * The current behavior is to trigger a user error if `WP_DEBUG` is true.
	 *
	 * @param string $message  The designated error message for this error.
	 */
	public static function trigger( $message = '' )
	{
		if ( WP_DEBUG  ) {
			trigger_error( $message, E_USER_NOTICE );
		}
	}


}
