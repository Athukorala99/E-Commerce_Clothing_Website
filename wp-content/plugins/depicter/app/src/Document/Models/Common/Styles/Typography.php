<?php
namespace Depicter\Document\Models\Common\Styles;


use Depicter\Document\CSS\Breakpoints;

class Typography extends States
{
	/**
	 * style name
	 */
	const NAME = 'typography';


	public function set( $css ) {
		$devices = Breakpoints::names();
		
		foreach ( $devices as $device ) {
			if ( !empty( $this->{$device} ) ) {

				$fontProperties = [
					'fontSize'      => 'font-size',
					'align'         => 'text-align',
					'color'         => 'color',
					'font'          => 'font-family',
					'fontVariant'   => 'font-weight',
					'lineHeight'    => 'line-height',
					'transform'     => 'text-transform',
					'letterSpacing' => 'letter-spacing',
					'wordwrap'      => 'word-wrap',
					'direction'     => 'direction',
					'decoration'    => 'text-decoration'
				];

				foreach ( $fontProperties as $key => $cssProperty ) {

					if ( isset( $this->{$device}->{$key} ) ) {
						$cssValue = $this->{$device}->{$key};

						if ( $key == 'fontSize' ) {
							$css[ $device ][ $cssProperty ] = $cssValue . 'px';
						} elseif ( $key == 'lineHeight') {
							$css[ $device ][ $cssProperty ] = $cssValue . '%';
						} elseif ( $key == 'font') {
							$cssValue = trim( $cssValue, '\"');
                            $css[ $device ][ $cssProperty ] = $cssValue == 'inherit' ? $cssValue : '"' . $cssValue . '"';
						} elseif ( $key == 'fontVariant') {
							$fontWeight = $cssValue;
							// extract font style and weight
							if( false !== strpos( $fontWeight, 'italic') ){
								$css[ $device ][ 'font-style' ] = 'italic';
								$fontWeight = trim( $fontWeight, 'italic');

							// reset font-style if desktop font style was italic
							} elseif( isset( $this->default->{$key} ) && false !== strpos( $this->default->{$key}, 'italic') ){
								$css[ $device ][ 'font-style' ] = 'normal';
							}

							// convert "regular" to 400
							if( 'regular' == strtolower( $fontWeight ) ){
								$fontWeight = 400;
							}

							$css[ $device ][ $cssProperty ] = $fontWeight;

						} elseif ( $key == 'wordwrap') {
							if( $cssValue == 'break' ){
								$css[ $device ][ 'overflow-wrap' ] = 'break-word';
							} else {
								$css[ $device ][ 'white-space' ] = 'nowrap';
							}
						} elseif ( $key == 'letterSpacing' ) {
							$css[ $device ][ $cssProperty ] = $cssValue . 'px';
						} elseif ( $key == 'color' ) {
							if ( false != strpos( $cssValue, 'gradient' ) ) {
								$css[ $device ][ 'background-image' ] = $cssValue;
								$css[ $device ][ '-webkit-background-clip' ] = 'text';
								$css[ $device ][ '-webkit-text-fill-color' ] = 'transparent';
							} else {
								$css[ $device ][ $cssProperty ] = $cssValue;	
							}
						} elseif ( !empty( $cssValue ) ) {
							$css[ $device ][ $cssProperty ] = $cssValue;
						}
					}
				}
			}
		}

		return $css;
	}

	public function getFontsList() {
		$devices = Breakpoints::names();
		$fontList = [];

		foreach ( $devices as $device ) {
			if ( !empty( $this->{$device}->font ) ) {
				$fontWeight = ! empty( $this->{$device}->fontVariant ) ? $this->{$device}->fontVariant : 400;
				if( ! empty( $fontList[ $this->{$device}->font ] )  ){
					if( is_array( $fontList[ $this->{$device}->font ] ) && ! in_array( $fontWeight, $fontList[ $this->{$device}->font ] ) ){
						$fontList[ $this->{$device}->font ][] = $fontWeight;
					}
				} else {
					$fontList[ $this->{$device}->font ] = [ $fontWeight ];
				}
			}
		}

		return $fontList;
	}
}
