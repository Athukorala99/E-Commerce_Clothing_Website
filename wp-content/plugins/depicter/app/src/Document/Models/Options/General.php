<?php
namespace Depicter\Document\Models\Options;

use Depicter\Document\CSS\Breakpoints;

class General
{
	/**
	 * @var string
	 */
	public $fullscreenMargin;

	/**
	 * @var bool
	 */
	public $keepAspect;

	/**
	 * @var Unit
	 */
	public $minHeight;

	/**
	 * @var Unit
	 */
	public $maxHeight;

	/**
	 * @var States
	 */
	public $visible;

	/**
	 * @var string
	 */
	public $backgroundColor = '';

	/**
	 * @var All
	 */
	protected $allOptions;


	public function setAllOptions( $allOptions ){
		$this->allOptions = $allOptions;
	}

	public function getAllOptions(){
		return $this->allOptions;
	}

	public function getStylesList(){
		$styles = [
			'default' => []
		];

		$isFullscreen = $this->getAllOptions()->getLayout() === 'fullscreen';

		if( $isFullscreen || $this->keepAspect ) {
			$styles = $this->getMinHeightStyles( $styles, true );
		}

		if( ! empty( $this->maxHeight->value )  && $isFullscreen ){
			$styles[ 'default' ]['max-height'] = $this->maxHeight;
		}

		if( $this->backgroundColor ){
			$styles['default']['background-color'] = $this->backgroundColor;
		}

		return $styles;
	}

	public function getMinHeightStyles( $styles = [], $keepAspect = null ){
		if( empty( $styles ) ){
			$styles = [
				'default' => []
			];
		}

		$keepAspect = $keepAspect ?? $this->keepAspect;

		if( $keepAspect ) {
			$heightSizes = $this->getAllOptions()->getSizes('height', false );
			foreach ( $heightSizes as $device => $height ){
				if( ! empty( $this->minHeight->value ) && ( $device === 'default' || $height > $this->minHeight->value ) ){
					$styles[ $device ]['min-height'] = $this->minHeight;
				}
			}
		}

		return $styles;
	}


	/**
	 * Get before init document styles
	 *
	 * @return array
	 */
	public function getBeforeInitStyles(){
		$styles = [
			'default' => []
		];
		$layout = $this->getAllOptions()->getLayout();

		if( $layout == 'fullscreen' ){
			if( is_numeric( $this->fullscreenMargin ) ){
				$styles['default']['height'] = "calc( 100vh - {$this->fullscreenMargin}px )";
			} elseif ( $this->fullscreenMargin === 'auto' ) {
				$styles['default']['height'] = "100vh";
			}
		} elseif( $layout == 'boxed' ){
			$responsiveSizes = $this->getAllOptions()->getSizes('width', true);
			foreach ( $responsiveSizes as $device => $value ){
				$styles[ $device ][ 'width' ] = $value;
			}

			$responsiveSizes = $this->getAllOptions()->getSizes('height', true);
			foreach ( $responsiveSizes as $device => $value ){
				$styles[ $device ][ 'height' ] = $value;
			}
		} elseif( $layout == 'fullwidth' ){
			$responsiveSizes = $this->getAllOptions()->getSizes('height', true);
			foreach ( $responsiveSizes as $device => $value ){
				$styles[ $device ][ 'height' ] = $value;
			}
		}

		return $styles;
	}


}
