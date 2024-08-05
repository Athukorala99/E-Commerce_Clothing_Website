<?php
namespace Depicter\Document\Models\Common\Position;

class States
{
	/**
	 * @var Base
	 */
	public $default;

	/**
	 * @var Base
	 */
	public $tablet;

	/**
	 * @var Base
	 */
	public $mobile;

	public function getOffset( $device ) {
		$x = $y = $origin = '';
		if ( isset( $this->{$device}->x->value ) ) {
			$x = "x:" . $this->{$device}->x->value . $this->{$device}->x->unit . ";";
		}
		if ( isset( $this->{$device}->y->value ) ) {
			$y = "y:" . $this->{$device}->y->value . $this->{$device}->y->unit . ";";
		}

		if ( isset( $this->{$device}->origin ) ) {
			$origin = "origin:" . $this->{$device}->origin;
		}

		return $x . $y . $origin;
	}
}
