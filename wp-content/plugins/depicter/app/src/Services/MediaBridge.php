<?php
namespace Depicter\Services;


use Depicter\Document\Helper\Helper;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Depicter\Media\Image\ImageEditor;

/**
 * A bridge for MediaLibrary Service and AssetsAPIService to retrieve medias from different services
 *
 * Class MediaBridge
 *
 * @package Depicter\Services
 */
class MediaBridge
{
	/**
	 * Empty image placeholder
	 */
	const IMAGE_PLACEHOLDER_SRC = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';


	/**
	 * Retrieves source of an asset by ID
	 *
	 * @param string|int $assetId
	 * @param string     $size
	 * @param bool       $forcePreview
	 *
	 * @return array|false|string
	 * @throws \Exception
	 */
	public function getSourceUrl( $assetId, $size = 'large', $forcePreview = false, $args = [] )
	{
		// if $assetId is an attachment ID
		if( $attachmentId = $this->getAttachmentId( $assetId ) ) {
			try {
				$mediaUrl = $this->library()->getSourceURL( $attachmentId, $size, $args );
			} catch( \Exception $e ) {
				// if asset is imported but deleted manually after a while
				if ( ! is_numeric( $assetId ) ) {
					$this->library()->importAsset( $assetId, true );
					$attachmentId = $this->getAttachmentId( $assetId );
					$mediaUrl = $this->library()->getSourceURL( $attachmentId, $size, $args );
				}
			}

		// If asset is not imported yet
		} else {
			$size = is_array( $size ) ? 'large' : $size;
			$mediaUrl = AssetsAPIService::getHotlink( $assetId, $size, ['forcePreview' => $forcePreview ] );
		}

		return $mediaUrl;
	}

	/**
	 * Shorthand method for calling resize and crop methods for an image.
	 *
	 * @param string|int $assetId
	 * @param int    $resizeW  Resize width
	 * @param int    $resizeH  Resize height
	 * @param int    $cropW    Crop with
	 * @param int    $cropH    Crop height
	 * @param array  $args     Resizing options
	 *
	 * @return mixed
	 */
	public function resizeSourceUrl( $assetId, $resizeW = null, $resizeH = null, $cropW = null, $cropH = null, $args = [] ){

		// if $assetId is an attachment ID
		if( $attachmentId = $this->getAttachmentId( $assetId ) ) {
			try {
				$mediaUrl = ImageEditor::process( $attachmentId, $resizeW, $resizeH, $cropW, $cropH, $args );
			} catch( \Exception $e ) {
				// if asset is imported but deleted manually after a while
				if ( ! is_numeric( $assetId ) ) {
					$this->library()->importAsset( $assetId, true );
					$attachmentId = $this->getAttachmentId( $assetId );
					$mediaUrl = ImageEditor::process( $attachmentId, $resizeW, $resizeH, $cropW, $cropH, $args );
				}
			}
		// If asset is not imported yet
		} else {
			$mediaUrl = AssetsAPIService::getHotlink( $assetId, 'large', [ 'forcePreview' => false ] );
		}

		return $mediaUrl;
	}

	/**
	 * Retrieves the MediaLibraryService
	 *
	 * @return MediaLibraryService
	 */
	public function library(){
		return \Depicter::resolve('depicter.media.library');
	}

	/**
	 * Retrieve asset hotlink
	 *
	 * @param  string|int $assetId
	 * @param  string  $size
	 * @param  false  $forcePreview
	 *
	 * @return mixed|string
	 */
	public function getHotLink( $assetId, $size = 'large', $forcePreview = false ) {
		return AssetsAPIService::getHotlink( $assetId, $size, [ 'forcePreview' => $forcePreview ]);
	}

	/**
	 * Retrieves source set of an asset by ID
	 *
	 * @param string|int $assetId
	 *
	 * @param string     $size
	 * @param array      $args
	 *
	 * @return bool|string
	 */
	public function getSrcSet( $assetId, $size = 'medium', $args = [] )
	{
		$assetId = is_numeric( $assetId ) ? $assetId : $this->library()->getAttachmentForImportedAsset( $assetId );
		$source = '';
		if ( is_numeric( $assetId ) ) {
			if ( is_array( $size ) && empty( $args['isSvg'] ) ) {

				// set device to get srcset for specific device
				if ( $args['device'] == 'mobile' && !empty( $args['cropData']->mobile ) ) {
					$device = 'mobile';
				} elseif( $args['device'] == 'tablet' && !empty( $args['cropData']->tablet ) ) {
					$device = 'tablet';
				} else {
					$device = 'default';
				}

				if ( !empty( $args['cropData']->{$device} ) && !empty( $args['cropData']->{$device}->focalPoint ) && !empty( $size[0] ) && !empty( $size[1] ) && empty( $args['fullSizeImageLoaded'] ) ) {

					$source = ImageEditor::process( $assetId,
					                                $args['cropData']->{$device}->mediaSize->width,
					                                $args['cropData']->{$device}->mediaSize->height,
					                                $size[0], $size[1],
					                                [
					                                	'focalX' => $args['cropData']->{$device}->focalPoint->x,
						                                'focalY' => $args['cropData']->{$device}->focalPoint->y,
						                                'upscale' => true,
						                                'dry'     => true
					                                ]
					);
				}
				if ( empty( $source ) ) {
					$source = wp_get_attachment_image_src( $assetId, $size )[0];
				}
				$sources = [
					$source
				];

				if ( !empty( $args['cropData']->{$device} ) && !empty( $args['cropData']->{$device}->focalPoint ) && empty( $args['fullSizeImageLoaded'] ) ) {
					// load 2x size
					if ( !empty( $size[1] ) ) {
						$highResWidth = $size[0] * 2;
						$highResHeight = $size[1] * 2;
					} else {
						$highResWidth = $size[0] * 2;
						$highResHeight = $size[1];
					}

					$highResUrl = ImageEditor::process( $assetId,
					                                    $args['cropData']->{$device}->mediaSize->width,
					                                    $args['cropData']->{$device}->mediaSize->height,
					                                    $highResWidth, $highResHeight,
					                                    [
						                                    'focalX' => $args['cropData']->{$device}->focalPoint->x,
						                                    'focalY' => $args['cropData']->{$device}->focalPoint->y,
						                                    'upscale' => true,
						                                    'dry'     => ! empty( $args['fullSizeImageLoaded'] ),
					                                    ]
					);

					if ( $highResUrl ) {
						$sources[] = $highResUrl;
					}
				}
				return implode( ', ', $sources );

			} else {
				return wp_get_attachment_image_srcset( $assetId, $size );
			}
		}
		return '';
	}

	/**
	 * Retrieves alternative text of an asset by ID
	 *
	 * @param string|int $assetId
	 *
	 * @return mixed|string
	 */
	public function getAltText( $assetId )
	{
		if ( is_numeric( $assetId ) ) {
			return get_post_meta( $assetId, '_wp_attachment_image_alt', true );
		}
		return '';
	}

	/**
	 * Get API link to hotlink to a media source
	 *
	 * @param string|int $assetId
	 * @param string     $size
	 * @param string      $forcePreview
	 *
	 * @return string
	 */
	public function getAjaxHotlink( $assetId, $size = 'large', $forcePreview = 'false' )
	{
		return add_query_arg(
			[
				'id'   => $assetId,
				'size' => $size,
				'forcePreview' => $forcePreview
			],
			\Depicter::routeUrl('getMedia')
		);
	}

	/**
	 * Process document content and download third party assets used in document.
	 *
	 * @param $content
	 *
	 * @throws GuzzleException
	 */
	public function importDocumentAssets( $content ){
		$assetIDs = Helper::extractAssetIds( $content );
		$this->library()->importAssets( $assetIDs );
	}

	/**
	 * Get attachment Id by asset id
	 *
	 * @param string|int $assetId
	 *
	 * @return int|null
	 */
	public function getAttachmentId( $assetId ){
		// if $assetId is an attachment ID
		if( is_numeric( $assetId ) ) {
			return $assetId;
		// If asset with $assetId was imported and registered before
		} elseif ( $attachmentId = $this->library()->getAttachmentForImportedAsset( $assetId ) ){
			return $attachmentId;
		}

		return null;
	}


	/**
	 * Remove attachment from imported media dictionary
	 *
	 * @param int $attachmentId
	 * @return void
	 */
	public function maybeRemoveAttachmentFromDictionary( $attachmentId ) {
		$imported_media_dictionary = \Depicter::options()->get( 'imported_assets', [] );
		if ( false !== $array_key = array_search( $attachmentId, $imported_media_dictionary ) ) {
			unset( $imported_media_dictionary[ $array_key ] );
			\Depicter::options()->set( 'imported_assets', $imported_media_dictionary );
		}		
	}
}
