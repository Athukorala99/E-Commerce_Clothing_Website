<?php
namespace Averta\Core\Utility;

class Date {

	/**
	 * Converts one date time format to another format
	 *
	 * @param string $dateTime
	 * @param string $toFormat
	 * @param string $fromFormat
	 * @param string $timeZone
	 *
	 * @return string
	 */
	public static function covertDateFormat( $dateTime, $toFormat = 'M d, Y H:i:s', $fromFormat = 'Y-m-d H:i:s', $timeZone = 'UTC' ){
		$dateTimeClass = \DateTime::createFromFormat(
			$fromFormat,
			$dateTime,
			new \DateTimeZone( $timeZone )
		);

		if ( $dateTimeClass === false ) {
			return $dateTime;
		}

		return $dateTimeClass->format( $toFormat );
	}
}
