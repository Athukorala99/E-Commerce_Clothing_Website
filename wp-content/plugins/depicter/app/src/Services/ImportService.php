<?php
namespace Depicter\Services;

use Averta\WordPress\Utility\Sanitize;
use GuzzleHttp\Psr7\UploadedFile;

class ImportService
{

	protected $importFolderName = 'import';

	protected $assetsFolderName = 'assets';

	/**
	 * Extract uploaded zip file and import slider
	 * @param $file
	 *
	 * @return bool
	 */
	public function unpack( $file ) {

		$wp_upload_dir = \Depicter::storage()->uploads();
		$fileSystem = \Depicter::storage()->filesystem();

		$depicterUploadPath = \Depicter::storage()->getPluginUploadsDirectory() . '/';

		try{

			if ( $file instanceof UploadedFile ) {
				$uploadedZipFilePath = \Depicter::storage()->getPluginUploadsDirectory() . '/' . $file->getClientFilename();
				if ( !is_dir( $depicterUploadPath ) ) {
					$fileSystem->mkdir( $depicterUploadPath );
				}

				// move uploaded zip file from temp directory to depicter folder inside wp uploads directory and extract it
				$file->moveTo( $uploadedZipFilePath );

			} else {
				$uploadedZipFilePath = $file;
				if ( ! $fileSystem->isFile( $file ) ) {
					return false;
				}
			}

			$zipFile = new \ZipArchive();
			$zipFile->open( $uploadedZipFilePath );
			$zipFile->extractTo($depicterUploadPath . $this->importFolderName );
			$zipFile->close();

			$importedAssetIDs = $this->importAssets( $fileSystem, $wp_upload_dir );
			$sliderID = $this->importSlider( $importedAssetIDs, $depicterUploadPath );

			if ( $file instanceof UploadedFile ) {
				unlink( $uploadedZipFilePath );
			}
			$fileSystem->rmdir( $depicterUploadPath . $this->importFolderName, true );

			if ( \Depicter::options()->get('use_google_fonts', 'on') === 'save_locally' ) {
				$documentModel = \Depicter::document()->getModel( $sliderID )->prepare();
				\Depicter::googleFontsService()->download( $documentModel->getFontsLink() );
			}

			return $sliderID;

		} catch( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Import available assets inside assets directory
	 * @param $fileSystem
	 * @param $uploadDirectory
	 *
	 * @return array $importedIDs
	 */
	protected function importAssets( $fileSystem, $uploadDirectory ) {
		$allowedMimeTypes = array_values( get_allowed_mime_types() );
		$importedIDs = [];
		$uploadPath = \Depicter::storage()->getPluginUploadsDirectory() . '/';

		// scan assets directory to import assets
		$assets = $fileSystem->scan( $uploadPath . $this->importFolderName . '/' . $this->assetsFolderName );
		if ( $assets ) {
			foreach( $assets as $asset ) {
				$assetMimeType = wp_check_filetype( $asset['name'] )['type'];
				if ( !in_array( $assetMimeType, $allowedMimeTypes ) ) {
					continue;
				}
				$sanitizedFileName = Sanitize::fileName( $asset['name'] );
				$fileSystem->move( $uploadPath . $this->importFolderName . '/' . $this->assetsFolderName . '/' . $asset['name'], $uploadDirectory->getPath() . "/" . $sanitizedFileName );
				$attachmentTitle = preg_replace( '/\.[^.]+$/', '', $sanitizedFileName );
				$attachment = array(
					'guid'           => $uploadDirectory->getUrl() . '/' . $sanitizedFileName,
					'post_mime_type' => $assetMimeType,
					'post_title'     => $attachmentTitle,
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				$attachID = wp_insert_attachment( $attachment, $sanitizedFileName );
				if ( !is_wp_error( $attachID ) ) {
					// generate meta data for the inserted attachment
					$attachment_metadata = wp_generate_attachment_metadata( $attachID, $uploadDirectory->getPath() . "/" . $sanitizedFileName );
					wp_update_attachment_metadata( $attachID, $attachment_metadata );
					update_attached_file( $attachID, $uploadDirectory->getPath() . "/" . $sanitizedFileName );

					$attachmentTitleParts = explode( '-', $attachmentTitle );
					$oldID = end( $attachmentTitleParts );
					$importedIDs[ $oldID ] = $attachID;
				}
			}
		}

		return $importedIDs;
	}

	/**
	 * Import Slider
	 *
	 * @param $importedIDs
	 * @param $uploadPath
	 *
	 * @return mixed|null
	 * @throws \Exception
	 */
	protected function importSlider( $importedIDs, $uploadPath ) {
		$content = file_get_contents( $uploadPath . $this->importFolderName . '/data.json' );
		$content = preg_replace( '/"activeBreakpoint":".+?"/', '"activeBreakpoint":"default"', $content );
		preg_match_all( '/\"(source|src)\":\"(\d+)\"/', $content, $assets, PREG_SET_ORDER );
		if ( !empty( $assets ) ) {
			foreach( $assets as $asset ) {
				if ( !empty( $asset[2] ) && !empty( $importedIDs[ $asset[2] ] ) ) {
					$content = str_replace( $asset[0], '"' . $asset[1] . '":"'. $importedIDs[ $asset[2] ] .'"', $content );
				}
			}
		}

		$document = \Depicter::documentRepository()->create();
		$document->update([
			'content' => $content,
			'status' => 'publish'
		]);

		return $document->id;
	}
}
