<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;

class Opacity
{
	/**
	 * style name
	 */
	const NAME = 'opacity';

	/**
	 * @var string
	 */
	public $default;

	/**
	 * @var string
	 */
	public $tablet;

	/**
	 * @var string
	 */
	public $mobile;

	public function set( $css ) {
		$devices = Breakpoints::names();
		foreach ( $devices as $device ) {
			if ( !empty( $this->{$device} ) ) {
				$css[ $device ][ self::NAME ] = $this->{$device};
			}
		}

		return $css;
	}
}
