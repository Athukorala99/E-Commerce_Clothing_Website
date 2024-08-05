<?php
namespace Depicter\WordPress;

use Averta\WordPress\File\UploadsDirectory;
use GuzzleHttp\Psr7\UploadedFile;

class FileUploaderService
{
	public function upload( array $files ) {
		$results = [];
		$wp_upload_dir = new UploadsDirectory();
		$allowedMimeTypes = array_values( get_allowed_mime_types() );
		foreach( $files as $file ) {
			if ( ! $file instanceof  UploadedFile ) {
				continue;
			}

			if ( $file->getError() ) {
				$results[ $file->getClientFilename() ] = [
					'attachment'    => 0,
					'errors'        => [
						sprintf( __( 'Cannot upload the file, because max permitted file upload size is %s.', 'depicter' ), ini_get('upload_max_filesize') )
					]
				];
				continue;
			}

			if ( !in_array( $file->getClientMediaType(), $allowedMimeTypes ) ) {
				$results[ $file->getClientFilename() ] = [
					'attachment'    => 0,
					'errors'        => [
						sprintf( __( 'Cannot upload the file, uploading %s files are not allowed.', 'depicter' ), $file->getClientMediaType() )
					]
				];
				continue;
			}
			
			$filename = $wp_upload_dir->getPath() . "/" . $file->getClientFilename();
			$file->moveTo( $filename );
			$attachment = array(
				'guid'           => $wp_upload_dir->getUrl() . '/' . basename( $filename ),
				'post_mime_type' => $file->getClientMediaType(),
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $filename );

			if ( !is_wp_error( $attach_id ) ) {
				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				$results[ $file->getClientFilename() ] = [
					'attachment'    => $attach_id,
					'errors'        => []
				];
			} else {
				$results[ $file->getClientFilename() ] = [
					'attachment'    => 0,
					'errors'        => [
						$attach_id['error']
					]
				];
			}
		}

		return $results;
	}
}
