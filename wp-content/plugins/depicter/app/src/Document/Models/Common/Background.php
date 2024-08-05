<?php
namespace Depicter\Document\Models\Common;


use Averta\Core\Utility\Arr;
use Averta\Core\Utility\Embed;
use Averta\WordPress\Utility\JSON;
use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\CSS\Selector;
use Depicter\Document\Models\Traits\HasDataSheetTrait;
use Depicter\Html\Html;
use Depicter\Services\MediaBridge;

class Background
{
	use HasDataSheetTrait;

	/**
	 * @var Color
	 */
	public $color;

	/**
	 * @var string
	 */
	public $fitMode;

	/**
	 * @var object
	 */
	public $video;

	/**
	 * @var object
	 */
	public $image;

	/**
	 * @var Color
	 */
	public $overlay;

	/**
	 * @var Styles\Filter
	 */
	public $filter;

	/**
	 * @var array
	 */
	public $kenBurnsData = [];

	/**
	 * Check if section has background or not
	 *
	 * @return boolean
	 */
	public function hasBackground() {
		return !empty( $this->image->src ) || !empty( $this->video->src );
	}


	/**
	 * Render background markup
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function render() {
		$html = '';

		if ( !empty( $this->image->src ) ) {
			$originalSrc = $this->image->src;
			$this->image->src = $this->maybeReplaceDataSheetTags( $this->image->src );
			$html .= $this->renderImage( \Depicter::media()->getSourceUrl( $this->image->src ) );
			// restore original image src
			$this->image->src = $originalSrc;
		}

		if ( !empty( $this->video->src ) ) {
			$videoType = $this->video->type ?? '';
			if ( $videoType == 'embedVideo') {
				$html .= $this->renderEmbedVideo();
			} else {
				$html .= $this->renderVideo( \Depicter::media()->getSourceUrl( $this->video->src ) );
			}
		}

		return $html;
	}

	/**
	 * Render images
	 *
	 * @param $imageUrl
	 *
	 * @return Html
	 */
	protected function renderImage( $imageUrl ) {
		$imageID = \Depicter::media()->getAttachmentId( $this->image->src );
		$imageSrcSet = is_numeric( $imageID ) ? \Depicter::media()->getSrcSet( $imageID, 'full' ) : '';

		$args = [
			'class'             => 'depicter-bg',
			'src'			    =>  \Depicter::media()::IMAGE_PLACEHOLDER_SRC,
			'data-depicter-src' => $imageUrl,
			'alt'               => is_numeric( $imageID ) ? \Depicter::media()->getAltText( $imageID ) : ''
		];

		if ( !empty( $this->image->alt ) ) {
			$args['alt'] = $this->image->alt;
		}

		if ( !empty( $this->kenBurnsData ) ) {
			$args = Arr::merge( $args, $this->kenBurnsData );
		}

		if ( $imageSrcSet ) {
			$args['data-depicter-srcset'] = $imageSrcSet;
		}

		$available_args = [
			'responsiveArgs' => [
				'data-object-fit'       => 'fitMode',
				'data-object-position'  => 'position'
			]
		];

		$args = $this->getElementAttributes( 'image', $args, $available_args );

		$cropAttributes = $this->getCropAttributes( 'image' );

		return Html::img( $imageUrl, Arr::merge( $cropAttributes, $args ) );
	}

	/**
	 * Renders video tag
	 *
	 * @param $videoUrl
	 *
	 * @return \TypeRocket\Html\Html
	 */
	public function renderVideo( $videoUrl ) {

		$args = [
			'class' 		=> Selector::prefixify( 'bg-video' ),
			'src'           => $videoUrl,
			'preload'       => 'metadata',
			'playsinline'   => "true"
		];

		$available_args = [
			'muted' => 'muted',
			'loop'  => 'loop',
			'data-goto-next'  => 'goNextSlide',
			'data-auto-pause' => 'pause',
			'responsiveArgs' => [
				'data-object-fit'       => 'fitMode',
				'data-object-position'  => 'position'
			]
		];

		$args = $this->getElementAttributes( 'video', $args, $available_args );

		return Html::video( $args );
	}

	/**
	 * Get crop attributes
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	protected function getCropAttributes( string $type = 'image' ){
		if ( ! $this->hasCustomFitMode( $type ) ) {
			return [];
		}

		$attributes = [];
		$breakpointNames = Breakpoints::names();

		foreach ( $breakpointNames as $device ) {
			if( ! empty( $this->{$type}->cropData->{$device} ) ){
				$attributeDeviceValue = $this->{$type}->cropData->{$device};
				$attributeDeviceValue = is_object( $attributeDeviceValue ) || is_array( $attributeDeviceValue ) ? JSON::encode( $attributeDeviceValue ) : '';
				$attributeDeviceName  = $device === 'default' ? 'data-crop' : "data-{$device}-crop";
				$attributes[ $attributeDeviceName ] = $attributeDeviceValue;
			}
		}

		return $attributes;
	}

	/**
	 * Whether background types has custom fit mode or not
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	protected function hasCustomFitMode( string $type = 'image' ){
		if ( empty( $this->{$type}->fitMode->default ) ) {
			return false;
		}

		$hasCustomFitMode= false;
		$breakpointNames = Breakpoints::names();

		foreach ( $breakpointNames as $device ) {
			if( !empty( $this->{$type}->fitMode->{$device} ) && $this->{$type}->fitMode->{$device} === 'custom' ){
				return true;
			}
		}

		return false;
	}

	public function getElementAttributes( $element_type, $args, $available_args ) {
		foreach ( $available_args as $attribute => $property ) {
			// for object properties that has responsive value like fitMode and position
			if ( $attribute == 'responsiveArgs' ) {
				foreach ( $property as $key => $value ) {
					$breakpointNames = Breakpoints::names();
					if ( !empty( $this->{$element_type}->{$value} ) ) {
						foreach ( $breakpointNames as $device ) {
							$args[ $key ][] = !empty( $this->{$element_type}->{$value}->{$device} ) ? $this->{$element_type}->{$value}->{$device} : '';
						}
						$args[ $key ] = implode(',', $args[ $key ] );
					}
				}

			// for simple object properties
			} elseif ( !empty( $this->{$element_type}->{$property} ) ) {
				$value = $this->{$element_type}->{$property} == "1" ? "true" : $this->{$element_type}->{$property};
				$args[ $attribute ] = $value;
			}

			// In video background, if each of `muted` or `loop` are not set, consider it as `true` by default
			if( $element_type === 'video' ){
				if( in_array( $property, ['muted', 'loop'] ) ){
					if( ! isset( $this->{$element_type}->{$property} ) ){
						$args[ $attribute ] = 'true';
					}
				}
			}
		}
		return $args;
	}

	/**
	 * Get css background color
	 *
	 * @return array
	 */
	public function getColor() {
		$devices = Breakpoints::names();

		$css = [];
		foreach ( $devices as $device ) {
			if ( !empty( $this->color->{$device} ) ) {
				if ( false == strpos( $this->color->{$device}, 'gradient' ) ) {
					$css[ $device]['background-color'] = $this->color->{$device};
				} else {
					$css[ $device]['background-image'] = $this->color->{$device};
				}
			}
		}

		return $css;
	}

	/**
	 * Get background overlay styles
	 *
	 * @return array
	 */
	public function getOverlayStyles() {
		$default = [
			"content" => '""',
			"display" => "block",
			"position"=> "absolute",
			"top"     => "0",
			"bottom"  => "0",
			"right"   => "0",
			"left"    => "0",
			"z-index" => "1",
		];

		$devices = Breakpoints::names();

		$css = [];
		foreach ( $devices as $device ) {
			if ( !empty( $this->overlay->{$device} ) && ('transparent' !== $this->overlay->{$device} ) ) {
				$css[ $device] = $default;
				if ( false == strpos( $this->overlay->{$device}, 'gradient' ) ) {
					$css[ $device]['background-color'] = $this->overlay->{$device};
				} else {
					$css[ $device]['background-image'] = $this->overlay->{$device};
				}
			}
		}

		return $css;
	}

	/**
	 * Get filter styles of background
	 *
	 * @return array
	 */
	public function getSectionBackgroundFilter() {
		if( empty( $this->filter ) ){
			return [];
		}
		return $this->filter->set([]);
	}

	/**
	 * Get class name of background container
	 *
	 * @return string
	 */
	public function getContainerClassName() {
		if ( !empty( $this->video->src ) && $this->video->type != "embedVideo" ) {
			return Selector::prefixify('bg-video-container');
		}
		return Selector::prefixify('section-background');
	}

	/**
	 * Set ken burns data
	 *
	 * @param array $kenBurnsData
	 *
	 * @return void
	 */
	public function setKenBurnsData( array $kenBurnsData = [] ) {
		$this->kenBurnsData = $kenBurnsData;
	}

	/**
	 * Renders embed video tag
	 *
	 * @return \TypeRocket\Html\Html
	 */
	public function renderEmbedVideo() {
		$embedUrl = $this->video->src;

		if ( $this->video->embedType == 'youtube' ) {
			if( $embedUrl = Embed::getYouTubeVimeoEmbedUrl( $embedUrl ) ){
				$embedUrl = add_query_arg([
					'controls' => '0',
					'mute' => '1',
					'rel' => '0'
				], $embedUrl );
			}
		} else if ( $this->video->embedType == 'vimeo' ) {
			if( $embedUrl = Embed::getYouTubeVimeoEmbedUrl( $embedUrl ) ){
				$embedUrl = add_query_arg([
					'controls' => '0',
					'background' => '1'
				], $embedUrl );
			}
		}

		$iframe = Html::iframe([
			'src' => $embedUrl,
			"frameborder" => "0",
			"allowfullscreen" => "",
			'data-type' => $this->video->embedType,
			'data-width' => $this->video->size->width,
			'data-height' => $this->video->size->height,
			'data-goto-next' => $this->video->goNextSlide ?? false,
			'data-auto-pause' => $this->video->pause ?? false,
			'data-loop' => $this->video->loop ?? true
		]);

		if ( $this->video->embedType == 'youtube' ) {
			$iframe .= Html::img( Embed::getYouTubePosterUrl( $this->video->src ), [
				'class' => Selector::prefixify( 'bg-embed-poster' )
			]);
		}

		return Html::div(['class' => 'depicter-bg-embed'], $iframe );
	}
}
