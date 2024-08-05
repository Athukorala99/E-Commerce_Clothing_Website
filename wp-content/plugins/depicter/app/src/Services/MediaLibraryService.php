<?php
namespace Depicter\Services;


use Averta\Core\Utility\Arr;
use Averta\WordPress\Handler\Error;
use Averta\WordPress\Utility\Sanitize;
use Depicter;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Depicter\GuzzleHttp\TransferStats;
use Depicter\Media\Image\ImageEditor;
use SimpleXMLElement;

class MediaLibraryService
{
	/**
	 * Query the database
	 *
	 * @param array $queryParams
	 *
	 * @return \WP_Query
	 */
	public function query( $queryParams = [] ){
		return new \WP_Query( $queryParams );
	}


	/**
	 * Generate output for attachments
	 *
	 * @param \WP_Query $attachments
	 *
	 * @param string    $assetType
	 *
	 * @return array
	 */
	public function getQueryOutput( $attachments, $assetType = '' ){
		$result = [];

		if ( $attachments->have_posts() ) {
			$hits = [];

			foreach ( $attachments->posts as $key => $attachment ) {
				$attachmentMeta = wp_get_attachment_metadata( $attachment->ID );
				$mimType = get_post_mime_type( $attachment->ID );
				$hits[ $key ] = [
					"id"          => $attachment->ID . "",
					"type"        => $assetType,
					"mimeType"    => $mimType,
					"sourceType"  => "library",
					"title"       => $attachment->post_title,
					"description" => $attachment->post_content,
				];

				if ( $mimType == 'image/svg+xml' ) {
					$fileContent = @file_get_contents(get_attached_file( $attachment->ID ));
					if( empty( trim( $fileContent ) ) ){
						continue;
					}
					$svg = new SimpleXMLElement( $fileContent );
					$hits[ $key ] = Arr::merge( $hits[ $key ], [
						'width'  => (int) $svg['width'],
						'height' => (int) $svg['height'],
					]);
				} else if ( strpos( $mimType, 'audio' ) !== 0  ) {
					$hits[ $key ] = Arr::merge( $hits[ $key ], [
						'width'  => $attachmentMeta['width'],
						'height' => $attachmentMeta['height'],
						"thumb"  => wp_get_attachment_image_src( $attachment->ID, 'depicter-thumbnail' )[0]
					]);
				}
			}

			$result = [
				'page'          => $attachments->query['paged'],
				'perpage'       => $attachments->query['posts_per_page'],
				'totalPages'    => $attachments->max_num_pages,
				'total'			=> $attachments->found_posts,
				'hasMore'       => $attachments->query['paged'] < $attachments->max_num_pages,
				'hits'          => $hits
			];

		}

		return $result;
	}


	/**
	 * Get the direct link to media source
	 *
	 * @param $id
	 * @param $size
	 * @param $args
	 *
	 * @return false|string
	 * @throws \Exception
	 */
	public function getSourceURL( $id, $size = 'full', $args = [] ) {

		if( empty( $id ) ){
			throw new \Exception('Media ID is required.');
		}

		$available_sizes = [
			'screen'    => 'full',
			'full'      => 'full',
			'large'     => 'full',
			'medium'    => 'medium_large',
			'small'     => 'medium',
			'thumb'     => 'thumbnail'
		];

		if ( ! is_array( $size ) ) {
			$mediaSize = ! in_array( $size, array_keys( $available_sizes ) ) ? 'full' : $available_sizes[ $size ];
		} else {
			$mediaSize = 'full';
		}

		$mime_type = get_post_mime_type( $id );

		// If media not found
		if( false === $mime_type ){
			throw new \Exception('Media does not exists.');
		}
		// If mime type was not detected try to retrieve original attachment url
		if( empty( $mime_type ) && $attachment_url = wp_get_attachment_url( $id ) ){
			return $attachment_url;
		}

		// If it was video mime type
		if( 0 === strpos( $mime_type, 'video' ) ){
			$attachment_url = wp_get_attachment_url( $id );
			return $attachment_url;
		}

		// If it was image  mime type
		if ( $media = wp_get_attachment_image_src( $id, $mediaSize ) ) {
			if ( is_array( $size ) ) {
				if ( $media[1] < $size[0] * 2 ){
					return $media[0];
				}
				$url = ImageEditor::resize( $media[0], $size[0], $size[1], $args );
				return $url ? $url : $media[0];
			}
			return $media[0];
		}

		throw new \Exception('Media not found.');
	}

	/**
	 * Imports an asset to media library
	 *
	 * @param      $assetID
	 *
	 * @param bool $forceToDownloadAgain
	 *
	 * @return false|int  Attachment ID or false on failure
	 * @throws GuzzleException
	 */
	public function importAsset( $assetID, $forceToDownloadAgain = false ) {

		// Check if this asset id is imported before or not
		// Useful while user publishes document during edit process multiple times
		$attachmentId = $this->getAttachmentForImportedAsset( $assetID );
		if( ! $forceToDownloadAgain && $attachmentId && get_attached_file( $attachmentId )  ){
			return $attachmentId;
		}

		$args = [
			'forcePreview' 	=> false,
			'event'			=> 'download'
		];
		$mediaHotlinkUrl = AssetsAPIService::getHotlink( $assetID, 'large', $args );

		$assetFileName = Sanitize::fileName( $assetID );

		$response = Depicter::remote()->get( $mediaHotlinkUrl, [
			'on_stats' => function (TransferStats $stats) use (&$url) {
				$url = $stats->getEffectiveUri();
			}
		]);

		$type = $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'

		if ( ! $type || $response->getStatusCode() == 404 ){
			return false;
		}

		if ( $type == 'image/jpeg' ){
			$filename = $assetFileName . '.jpg';
		} elseif ( $type == 'image/png' ) {
			$filename = $assetFileName . '.png';
		} elseif ( $type == 'image/bmp' ) {
			$filename = $assetFileName . '.bmp';
		} elseif ( $type == 'image/svg+xml' ) {
			$filename = basename( $url );
		} else {
			// $parts = parse_url( $url );
			// parse_str( $parts['query'], $query);
			// $filename = isset( $query['filename'] ) ? $query['filename'] : $assetFileName . '.mp4';
			$filename = $assetFileName . '.mp4';
		}

		$fileSystem = Depicter::storage()->filesystem();

		$file = Depicter::storage()->uploads()->getPath() . '/' . $filename;
		$fileType = wp_check_filetype( $filename, null );

		if( $fileSystem->exists( $file ) ){
			$fileUrl = Depicter::storage()->uploads()->getUrl() . '/' . $filename;
			$attachmentId = attachment_url_to_postid( $fileUrl );
			if ( $attachmentId ) {
				$this->registerImportedAsset( $assetID, (int) $attachmentId );
				return $attachmentId;
			}

			return $this->insertAttachment( $assetID, $file, $fileType['type'], $assetFileName );
		}

		$isFileDownloaded = $fileSystem->write(
			$file,
			$response->getBody()->getContents()
		);

		if ( ! $isFileDownloaded ) {
			return false;
		}

		return $this->insertAttachment( $assetID, $file, $fileType['type'], $assetFileName );
	}

	/**
	 * Insert Attachment
	 *
	 * @param string $assetID
	 * @param string $filePath
	 * @param string $fileType
	 * @param string $fileName
	 *
	 * @return false|int
	 */
	public function insertAttachment( string $assetID, string $filePath, string $fileType, string $fileName = '') {
		$attachment = array(
			'post_mime_type' => $fileType,
			'post_title' => !empty( $fileName ) ? $fileName : basename( $filePath ),
			'post_content' => '',
			'post_status' => 'inherit'
		);

		$attachmentId = wp_insert_attachment( $attachment, $filePath );

		if( is_wp_error( $attachmentId ) || ! $attachmentId ){
			error_log( 'Error while inserting asset with ID of ' . $assetID, 0 );
			return false;
		}

		wp_update_attachment_metadata( $attachmentId, wp_generate_attachment_metadata( $attachmentId, $filePath ) );

		$this->registerImportedAsset( $assetID, (int) $attachmentId );

		return $attachmentId;
	}
	/**
	 * Imports list of assets to media library
	 *
	 * @param $assetIDs
	 * @param $forceToDownloadAgain
	 *
	 * @throws GuzzleException
	 */
	public function importAssets( $assetIDs, $forceToDownloadAgain = false )
	{
		foreach( $assetIDs as $ID ){
			$this->importAsset( $ID, $forceToDownloadAgain );
		}
	}

	/**
	 * Retrieves an attachment ID for an asset if it was imported and registered before.
	 *
	 * @param string $assetId
	 *
	 * @return string|bool    False if attachment ID does not exists for asset ID
	 */
	public function getAttachmentForImportedAsset( $assetId )
	{
		if( empty( $assetId ) ){
			Error::trigger('Asset ID is not valid.');
			return false;
		}

		$dictionary = $this->getImportedAssetsDictionary();
		return isset( $dictionary[ $assetId ] ) ? $dictionary[ $assetId ] : false;
	}

	/**
	 * Retrieves supported mime types for a media type
	 *
	 * @param string $mediaType  Media type. image, photo, video, audio, vector, svg
	 *
	 * @return mixed|string
	 */
	public function getSupportedMimeTypes( $mediaType = '' ){
		$mimeTypes = [
			'image'  => ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon', 'image/webp'],
			'photo'  => ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon', 'image/webp'],
			'video'  => 'video',
			'audio'  => 'audio',
			'vector' => 'image/svg+xml',
			'svg'    => 'image/svg+xml'
		];

		if( ! empty( $mimeTypes[ $mediaType ] ) ){
			return $mimeTypes[ $mediaType ];
		}

		return '';
	}

	/**
	 * Registers the attachment ID and belonging asset ID in a dictionary.
	 *
	 * @param string $assetId
	 * @param int    $attachmentId
	 *
	 * @return bool    False on failure
	 */
	private function registerImportedAsset( $assetId, $attachmentId )
	{
		if( empty( $assetId ) || empty( $attachmentId ) ){
			Error::trigger('Asset ID or attachment ID is not valid.');
			return false;
		}

		$dictionary = $this->getImportedAssetsDictionary();
		$dictionary[ $assetId ] = $attachmentId;

		return \Depicter::options()->set( 'imported_assets',  $dictionary );
	}

	/**
	 * Retrieves list of all assets which were imported and registered before.
	 *
	 * @return array
	 */
	private function getImportedAssetsDictionary(){
		return \Depicter::options()->get('imported_assets', []);
	}

}
