<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;

class Border extends States
{
	/**
	 * style name
	 */
	const NAME = 'border';

	/**
	 * @var string
	 */
	public $borderWidth = '1px';

	/**
	 * @var string
	 */
	public $borderStyle = 'solid';

	/**
	 * @var string
	 */
	public $borderColor = '#000';

	public function set( $css ) {
		$devices = Breakpoints::names();

		foreach ( $devices as $device ) {

			// If it is disabled in a breakpoint other than default, generate a reset style for breakpoint
			if( $device != 'default' && ! Helper::isStyleEnabled( $this, $device ) ){
				$css[$device][ self::NAME ] = 'none';

			} elseif ( Helper::isStyleEnabled( $this, $device ) ) {

				if( isset( $this->{$device}->top->value ) ){
					if ( !empty( $this->{$device}->link ) ) {
					$css[$device]['border-width'] = $this->{$device}->top->value . $this->{$device}->top->unit;
					} else {
						$css[$device]['border-width'] = $this->{$device}->top->value . $this->{$device}->top->unit . " " . $this->{$device}->right->value . $this->{$device}->right->unit . " " . $this->{$device}->bottom->value . $this->{$device}->bottom->unit . " " . $this->{$device}->left->value . $this->{$device}->left->unit;
					}
				} elseif ( $device == 'default') {
					$css[$device]['border-width'] = $this->borderWidth;
				}

				if ( !empty($this->{$device}->style) ) {
					$css[$device]['border-style'] = $this->{$device}->style;
				} elseif ($device == 'default') {
					$css[$device]['border-style'] = $this->borderStyle;
				}

				if ( !empty( $this->{$device}->color ) ) {
					$css[$device]['border-color'] = $this->{$device}->color;
				} elseif ( $device == 'default' ) {
					$css[$device]['border-color'] = $this->borderColor;
				}
			}
		}

		return $css;
	}
}
