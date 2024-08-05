<?php
namespace Depicter\Document\Models\Elements;

use Averta\Core\Utility\Data;
use Averta\Core\Utility\Media;
use Averta\WordPress\Utility\JSON;
use Depicter;
use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Models;
use Depicter\Front\Preview;
use Depicter\Html\Html;
use Depicter\Media\Image\FileResizedFinder;
use Depicter\Services\MediaBridge;

class Image extends Models\Element
{
	use Models\Traits\MediaSourceTrait;

	/**
	 * SVG Mime type
	 */
	const SVG_MIME = 'image/svg+xml';

	/**
	 * SVG Mime type
	 */
	const GIF_MIME = 'image/gif';

	/**
	 * @var array
	 */
	protected $renderArgs = [];

	/**
	 * Whether in preview mode or not
	 *
	 * @var bool
	 */
	protected $isPreviewMode;

	/**
	 * List of breakpoint sizes
	 *
	 * @var array
	 */
	protected $breakpoints;

	/**
	 * Stores inherited crop data options
	 *
	 * @var array
	 */
	protected $inheritedOptions = [];


	/**
	 * Calculates render options
	 *
	 * @return $this
	 */
	protected function setRenderOptions(){
		$this->breakpoints = Breakpoints::all();
		$this->breakpoints['default'] = 1025;

		$this->inheritedOptions = [
			'resizeW' => null,
			'resizeH' => null,
			'cropW'   => null,
			'cropH'   => null,
			'focalX'  => 0.5,
			'focalY'  => 0.5,
			'cropData'=> null
		];

		$this->renderArgs['assetId'] = $this->hasDataSheet() ? $this->maybeReplaceDataSheetTags( $this->options->source ) : $this->options->source;
		if ( empty( $this->renderArgs['assetId'] ) ) {
			return '';
		}

		$this->renderArgs['isPreview'] = Depicter::front()->preview()->isPreview();
		$this->renderArgs['isSVG'] = $this->isSvg( $this->renderArgs['assetId'] );
		$this->renderArgs['isGif'] = $this->isGif( $this->renderArgs['assetId'] );
		$this->renderArgs['sizeUnit'] = $this->size->default->width->unit ?: 'px';
		$this->renderArgs['attachmentId'] = Depicter::media()->getAttachmentId( $this->renderArgs['assetId'] );
		$this->renderArgs['isAttachment'] = is_numeric( $this->renderArgs['attachmentId'] );
		$this->renderArgs['altText'] = Depicter::media()->getAltText( $this->renderArgs['attachmentId'] );

		$this->isPreviewMode = $this->renderArgs['isPreview'] || $this->renderArgs['isSVG'] || $this->renderArgs['isGif'] || ! $this->renderArgs['isAttachment'];

		return $this;
	}

	/**
	 * Whether it's preview mode or not
	 *
	 * @return bool
	 */
	protected function isPreviewMode(){
		return $this->isPreviewMode;
	}


	protected function getUnitSize( $device = 'default' ){
		 return $this->size->{$device}->width->unit ?: 'px';
	}

	protected function hasRelativeUnitSize( $device = 'default' ){
		$relativeUnits = ['%'];

		if( isset( $this->size->{$device}->width->unit ) && in_array( $this->size->{$device}->width->unit, $relativeUnits )  ){
			return true;
		}
		if( isset( $this->size->{$device}->height->unit ) && in_array( $this->size->{$device}->height->unit, $relativeUnits )  ){
			return true;
		}
		return false;
	}

	/**
	 * Render the element wrapper tag
	 *
	 * @throws \JsonMapper_Exception
	 */
	protected function renderPictureWrapper(){

		$args = $this->getDefaultAttributes();

		$devices = Breakpoints::names();
		foreach( $devices as $device ) {
			$dataAttrName = $device == 'default' ? 'data-crop' : 'data-' . $device . '-crop';
			$hasRelativeUnitSize = $this->hasRelativeUnitSize( $device );

			if ( $this->isPreviewMode || $hasRelativeUnitSize ) {

				if( ! empty( $this->cropData->{$device} ) ){
					$mediaWidth  = $this->cropData->{$device}->mediaSize->width;
					$mediaHeight = $this->cropData->{$device}->mediaSize->height;

					if( $this->hasDataSheet() ){
						$attachment = wp_get_attachment_image_src( $this->renderArgs['attachmentId'], 'full' );
						if ( !$attachment ) {
							continue;
						}
						$originalMediaWidth  = $attachment[1] ?: $mediaWidth;
						$originalMediaHeight = $attachment[2] ?: $mediaHeight;
						[ $mediaWidth, $mediaHeight ] = Media::fitInBox( 'contain', $mediaWidth, $mediaHeight, $originalMediaWidth, $originalMediaHeight );
					}

					$args[ $dataAttrName ] = [
						'mediaSize'=> [
							'width'  => $mediaWidth,
							'height' => $mediaHeight
						]
					];
					$args[ $dataAttrName ]['focalPoint'] = [
						'x' => ! empty( $this->cropData->{$device}->focalPoint->x ) ? $this->cropData->{$device}->focalPoint->x : 0.5,
						'y' => ! empty( $this->cropData->{$device}->focalPoint->y ) ? $this->cropData->{$device}->focalPoint->y : 0.5
					];

					$args[ $dataAttrName ] = JSON::encode( $args[ $dataAttrName ] );
				}

			} elseif( ! $hasRelativeUnitSize && ! empty( $this->cropData->{$device} ) && $this->inheritedOptions['cropData'] !== "false" ){
				$args[ $dataAttrName ] = "false";
			}

			if( isset( $args[ $dataAttrName ] ) ){
				$this->inheritedOptions['cropData'] = $args[ $dataAttrName ];
			}
		}

		$this->markup = Html::picture( $args );
	}

	/**
	 * Renders a default image tag
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	protected function renderImageTag(){
		[ $resizeWidth, $resizeHeight, $cropWidth, $cropHeight, $args ] = $this->getImageEditOptions( 'default' );

		$hasRelativeUnitSize = $this->hasRelativeUnitSize( 'default' );

		if ( $this->isPreviewMode ) {
			$imageSource = Depicter::media()->getSourceUrl( $this->renderArgs['assetId'], [ $cropWidth, $cropHeight ], false, [ 'dry' => true ] );

		} else {
			$args['upscale'] = true;

			if( $hasRelativeUnitSize ){
				$cropWidth = null;
				$cropHeight = null;
			}
			$imageSource = Depicter::media()->resizeSourceUrl(
				$this->renderArgs['attachmentId'], $resizeWidth, $resizeHeight, $cropWidth, $cropHeight, $args
			);
		}

		$img = Html::img( '', [
			'src' => \Depicter::media()::IMAGE_PLACEHOLDER_SRC,
			'data-depicter-src' => $imageSource,
			'alt' => $this->renderArgs['altText']
		] );

		if ( $this->isPreviewMode && ! $this->renderArgs['isSVG'] && ! $hasRelativeUnitSize ) {
			$img = Html::source([
				'data-depicter-srcset' => $imageSource,
			]) . $img;
		}

		if( $this->renderArgs['isSVG'] ){
			$this->addMediaUlrToDictionary( $imageSource, '', '', self::SVG_MIME );
		}

		$this->markup->nest( $img . "\n" );

		if ( false !== $a = $this->getLinkTag() ) {
			$this->markup = $a->nest( "\n". $this->markup . "\n" );
		}

		return $this->markup;
	}

	/**
	 * Renders element markup
	 *
	 * @return string|void
	 * @throws \JsonMapper_Exception
	 * @throws \Exception
	 */
	public function render() {

		$this->setRenderOptions();

		if ( empty( $this->renderArgs['assetId'] ) ) {
			return '';
		}

		$this->renderPictureWrapper();

		$this->renderSourceTags();
		$this->renderImageTag();

		return $this->markup;
	}


	/**
	 * Retrieves attachment mime type
	 *
	 * @param string|int $assetId
	 *
	 * @return false|string
	 */
	protected function getMimeType( $assetId ){
		$attachmentId = Depicter::media()->getAttachmentId( $assetId );
		return is_numeric( $attachmentId ) ? get_post_mime_type( $attachmentId ) : false;
	}

	/**
	 * Whether it's svg file or not
	 *
	 * @param string|int $assetId
	 *
	 * @return bool
	 */
	public function isSvg( $assetId ) {
		return $this->getMimeType( $assetId ) == self::SVG_MIME;
	}

	/**
	 * Whether it's gif file or not
	 *
	 * @param string|int $assetId
	 *
	 * @return bool
	 */
	public function isGif( $assetId ) {
		return $this->getMimeType( $assetId ) == self::GIF_MIME;
	}

	/**
	 * Retrieves image edit options for a breakpoint
	 *
	 * @param string $device
	 *
	 * @return array
	 */
	protected function getImageEditOptions( $device = 'default' ){
		$params = [];

		$this->inheritedOptions['resizeW'] = $this->cropData->{$device}->mediaSize->width ?? null;
		$this->inheritedOptions['resizeH'] = $this->cropData->{$device}->mediaSize->height ?? null;

		if( $this->hasDataSheet() ){
			$attachment = wp_get_attachment_image_src( $this->renderArgs['attachmentId'], 'full' );
			if ( !$attachment ) {
				return;
			}
			$originalMediaWidth  = $attachment[1] ?: null;
			$originalMediaHeight = $attachment[2] ?: null;
			[ $mediaWidth, $mediaHeight ] = Media::fitInBox( 'contain', $this->inheritedOptions['resizeW'], $this->inheritedOptions['resizeH'], $originalMediaWidth, $originalMediaHeight );
			$params[] = $mediaWidth;
			$params[] = $mediaHeight;

		} else {
			$params[] = $this->inheritedOptions['resizeW'];
			$params[] = $this->inheritedOptions['resizeH'];
		}

		$params[] = $this->inheritedOptions['cropW'] = !empty( $this->size->{$device}->width->value  ) ?
                                            $this->size->{$device}->width->value :
                                            null;

		$params[] = $this->inheritedOptions['cropH'] = !empty( $this->size->{$device}->height->value ) ?
                                            $this->size->{$device}->height->value :
                                            null;

		$this->inheritedOptions['focalX'] = !empty( $this->cropData->{$device}->focalPoint->x ) ?
                                            $this->cropData->{$device}->focalPoint->x :
                                            $this->inheritedOptions['focalX'];

		$this->inheritedOptions['focalY'] = !empty( $this->cropData->{$device}->focalPoint->y ) ?
                                            $this->cropData->{$device}->focalPoint->y :
                                            $this->inheritedOptions['focalY'];

		$params[] = [
			'focalX' => $this->inheritedOptions['focalX'],
			'focalY' => $this->inheritedOptions['focalY']
		];

		return $params;
	}

	/**
	 * Retrieves source urls (srcset) for a breakpoint in array
	 *
	 * @param string $device
	 *
	 * @return array
	 */
	protected function getSourceUrls( $device = 'default' ){

		[ $resizeWidth, $resizeHeight, $cropWidth, $cropHeight, $args ] = $this->getImageEditOptions( $device );

		if( ! $cropWidth && ! $cropHeight && ! $resizeWidth && ! $resizeHeight ){
			return [];
		}

		$args['upscale'] = true;

		if( $this->hasRelativeUnitSize( $device ) ){
			$cropWidth = null;
			$cropHeight = null;
		}

		$imageSources = [];
		$imageSources[] = Depicter::media()->resizeSourceUrl(
			$this->renderArgs['attachmentId'], $resizeWidth, $resizeHeight, $cropWidth, $cropHeight, $args
		);

		if( empty( $imageSources ) ){
			return [];
		}

		// $args['upscale'] = false;

		$retinaImageSource = Depicter::media()->resizeSourceUrl(
			$this->renderArgs['attachmentId'],
			$resizeWidth  ? $resizeWidth  * 2 : $resizeWidth,
			$resizeHeight ? $resizeHeight * 2 : $resizeHeight,
			 $cropWidth  ? $cropWidth  * 2 : $cropWidth,
			 $cropHeight ? $cropHeight * 2 : $cropHeight,
			$args
		);

		if( $retinaImageSource ){
			$imageSources[] = $retinaImageSource . ' 2x';
		}

		return $imageSources;
	}

	/**
	 * Generates and appends a source tag with media query to element markup
	 *
	 * @param array  $imageSources
	 * @param string $mediaQueryCondition
	 * @param int   $mediaQuerySize
	 */
	protected function appendSourceTag( $imageSources = [], $mediaQueryCondition = 'max-width', $mediaQuerySize = null ){
		if( ! $imageSources ){
			return;
		}

		$this->addMediaUlrToDictionary( $imageSources, $mediaQueryCondition, $mediaQuerySize );

		$attributes = [
			'data-depicter-srcset' => trim( implode( ', ', $imageSources ), ', ' ),
			'srcset' => \Depicter::media()::IMAGE_PLACEHOLDER_SRC,
		];
		if( $mediaQueryCondition && $mediaQuerySize ){
			$attributes['media'] = '(' . $mediaQueryCondition . ': ' . $mediaQuerySize . 'px)';
		}
		$sourceTag = Html::source( $attributes );

		$this->markup->nest( "\n" . $sourceTag . "\n" );
	}

	/**
	 * Renders and appends necessary source tags with media queries for breakpoints
	 */
	protected function renderSourceTags(){
		if( $this->isPreviewMode ){
			return;
		}

		$desktopSources = $this->getSourceUrls( 'default' );
		$tabletSources  = $this->getSourceUrls( 'tablet' );
		$mobileSources  = $this->getSourceUrls( 'mobile' );

		if( ! $tabletSources && ! $mobileSources  ){
			$this->appendSourceTag( $desktopSources );
			return;
		}

		if( $desktopSources == $tabletSources ){
			if( $tabletSources == $mobileSources ){
				// if all breakpoints sources are the same
				$this->appendSourceTag( $desktopSources );
			} else {
				// if desktop and tablet sources are the same
				$this->appendSourceTag( $mobileSources, 'max-width', $this->breakpoints['mobile'] );
				$this->appendSourceTag( $desktopSources, 'min-width', $mobileSources ? (int) $this->breakpoints['mobile'] + 1 : 0 );
			}
		} elseif( $tabletSources == $mobileSources ){
			// if tablet and mobile sources are the same
			$this->appendSourceTag( $tabletSources, 'max-width', $this->breakpoints['tablet'] );
			$this->appendSourceTag( $desktopSources, 'min-width', ( $tabletSources ? (int) $this->breakpoints['tablet'] + 1 : 0 ) );
		} else {
			$this->appendSourceTag( $mobileSources, 'max-width', $this->breakpoints['mobile'] );
			$this->appendSourceTag( $tabletSources, 'max-width', $this->breakpoints['tablet'] );

			$mediaQuerySize = (int) $this->breakpoints['default'];
			if( ! $tabletSources ){
				$mediaQuerySize = $mobileSources ? (int) $this->breakpoints['mobile'] + 1 : 0;
			}
			$this->appendSourceTag( $desktopSources, 'min-width', $mediaQuerySize );
		}

	}

}
