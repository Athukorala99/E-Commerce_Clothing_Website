<?php
namespace Depicter\Document\Models\Common\Size;


use Depicter\Document\CSS\Breakpoints;

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

	/**
	 * Retrieves sizes for all breakpoints
	 *
	 * @param mixed $sizeProp
	 * @param bool  $includeUnit
	 *
	 * @return array
	 */
	public function getResponsiveSizes( $sizeProp, $includeUnit = false ) {
		$responsiveSizes = [];

		foreach( Breakpoints::names() as $device ){
			$responsiveSizes[ $device ] = '';

			if( !empty( $this->{$device}->{$sizeProp} ) && is_string( $this->{$device}->{$sizeProp} ) ){
				$responsiveSizes[ $device ] .= $this->{$device}->{$sizeProp};

			} elseif( isset( $this->{$device}->{$sizeProp}->value ) && is_numeric( $this->{$device}->{$sizeProp}->value ) ){
				$responsiveSizes[ $device ] .= $this->{$device}->{$sizeProp}->value;
				if( $includeUnit && !empty( $this->{$device}->{$sizeProp}->unit ) ){
					$responsiveSizes[ $device ] .= $this->{$device}->{$sizeProp}->unit;
				}
			} elseif( $device == 'default' ) {
				$responsiveSizes[ $device ] .= 'auto';
			}
		}

		return $responsiveSizes;
	}

	/**
	 * If responsive sizes are all empty
	 *
	 * @param $responsiveSizes
	 *
	 * @return bool
	 */
	public function hasNoResponsiveSize( $responsiveSizes )
	{
		foreach( Breakpoints::names() as $device ){
			if( ! empty( $responsiveSizes[ $device ] ) ){
				return false;
			}
		}
		return true;
	}
}
