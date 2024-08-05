<?php
namespace Depicter\Services;

use Averta\WordPress\Utility\JSON;
use Depicter\Exception\EntityException;

class ExportService
{

	/**
	 * Create zip file from slider data
	 *
	 * @param $documentID
	 *
	 * @return false|string
	 * @throws EntityException
	 */
	public function pack( $documentID ) {

		$sliderData = $this->sliderData( $documentID );

		$zip = new \ZipArchive();
		$tmp = tempnam('temp','zip');
		$zip->open( $tmp, \ZipArchive::OVERWRITE );
		$zip->addFromString( 'data.json', $sliderData['data'] );
		if ( !empty( $sliderData['assets'] ) ){
			foreach( $sliderData['assets'] as $assetID ){
				$attachmentUrl = wp_get_attachment_url( $assetID );
				$attachmentName = pathinfo( $attachmentUrl, PATHINFO_BASENAME );
				if ( strpos( $attachmentName, ' ' ) !== false ) {
					$attachmentUrl = str_replace( $attachmentName, rawurlencode( $attachmentName ), $attachmentUrl );
				}
				$zip->addFromString( 'assets/' . get_the_title( $assetID ) . '-' . $assetID . '.' . pathinfo( $attachmentUrl, PATHINFO_EXTENSION ), file_get_contents( $attachmentUrl ) );
			}
		}
		$zip->close();
		return $tmp;
	}

	/**
	 * Get slider data
	 *
	 * @param $documentID
	 *
	 * @return array
	 * @throws EntityException
	 */
	protected function sliderData( $documentID ) {
		$jsonContent = \Depicter::document()->getEditorData( $documentID );
		$jsonContent = JSON::encode( $jsonContent );
		$assetIDs = [];
		preg_match_all( '/\"(source|src)\":\"(\d+)\"/', $jsonContent, $assets, PREG_SET_ORDER );
		if ( !empty( $assets ) ) {
			foreach( $assets as $asset ) {
				if ( !empty( $asset[2] ) ) {
					$assetIDs[] = $asset[2];
				}
			}
		}
		return [
			'data' => $jsonContent,
			'assets' => $assetIDs
		];
	}
}
