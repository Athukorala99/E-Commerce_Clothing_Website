<?php
namespace Depicter\Document\Models\Common;


use Depicter\Document\CSS\Breakpoints;

class ResponsiveScale extends States
{

	/**
	 * Retrieves responsive scales for all breakpoints
	 *
	 * @return array
	 */
	public function getResponsiveSizes() {
		$responsiveSizes = [];

		foreach( Breakpoints::names() as $device ){

			if( ! isset( $this->{$device} ) ){
				$responsiveSizes[ $device ] = $device == 'default' ? true : '';
			} else{
				$responsiveSizes[ $device ] = $this->{$device} ? 'true' : 'false';
			}
		}

		return $responsiveSizes;
	}

}
