<?php
namespace Depicter\Services;

use Averta\Core\Utility\Media;
use Averta\WordPress\Utility\JSON;
use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Helper\Helper;
use Depicter\Utility\Http;

/**
 * AI Wizard Service
 *
 * @package Depicter\Services
 */
class AIWizardService
{

	/**
	 * Make editor data array
	 * @param $editorData
	 * @param $data
	 *
	 * @return array
	 */
    public function updateEditorDataByAiContent( $editorData, $AiGeneratedData ) {
		$numberOfSections = count( $AiGeneratedData['sections'] ?? [] );

	    if ( $numberOfSections > 0 ) {
			$firstSectionID = key( $editorData['sections'] );
		    $editorData = \Depicter::editorData()->duplicateSectionWithElements( $firstSectionID, $editorData, $numberOfSections );

			$i = 0;
			foreach ( $editorData['sections'] as $section ) {
				$editorData['sections'][ $editorData['sectionsList'][ $i ] ] = $this->searchAndReplaceInObject( $editorData['sections'][ $editorData['sectionsList'][ $i ] ], $AiGeneratedData['sections'][ $i ] );
				foreach( $section['elements'] as $elementID ) {
					if ( $editorData['elements'][ $elementID ]['type'] == 'image' ) {
						if ( empty( $editorData['elements'][ $elementID ]['options']['className'] ) || false === strpos( $editorData['elements'][ $elementID ]['options']['className'], 'targetImage' ) ) {
							continue;
						}
					}

					$editorData['elements'][ $elementID ] = $this->searchAndReplaceInObject( $editorData['elements'][ $elementID ], $AiGeneratedData['sections'][ $i ] );
				}
				++$i;
			}
	    }

		// replace special colors with color palette colors
		$editorDataInString = is_array( $editorData ) ? JSON::encode( $editorData ) : $editorData;
	    $definedColors = ['#012d13', '#112d13', '#212d13', '#312d13', '#412d13'];
	    $editorDataInString = str_ireplace( $definedColors, $AiGeneratedData['colorPalette'], $editorDataInString );

		return JSON::decode( $editorDataInString, true );
    }


	/**
	 * search and replace data in duplicated elements
	 *
	 * @param $item
	 * @param $searchReplaceData
	 *
	 * @return mixed
	 */
    public function searchAndReplaceInObject( $item, $searchReplaceData ) {
        $item = is_array( $item ) ? JSON::encode( $item ) : $item;

        foreach ( $searchReplaceData as $search => $replace ) {
			if ( $search == 'imageData' ) {
				$assetIDs = Helper::extractAssetIds( $item );
				foreach( $assetIDs as $assetID ) {
					$item = str_replace( $assetID, $replace['id'], $item );
				}
			} else {
				$item = str_ireplace( '{{{'. $search .'}}}' , $replace, $item );
			}
        }

        return JSON::decode( $item, true );
    }

	/**
	 * @param $editorData
	 *
	 * @return false|string
	 */
	public function fixMediaSizes( $editorData ) {
		$devices = Breakpoints::names();
		if ( JSON::isJson( $editorData ) ) {
			$editorData = JSON::decode( $editorData, true );
		}

		foreach ( $editorData['sections'] as $section ) {
			foreach( $section['elements'] as $elementID ) {
				 if ( $editorData['elements'][ $elementID ]['type'] == 'image' ) {
					 $imageID = \Depicter::media()->getAttachmentId( $editorData['elements'][ $elementID ]['options']['source'] );
					 $attachment = wp_get_attachment_image_src( $imageID, 'full' );
					 if ( !$attachment ) {
						 continue;
					 }
					 $originalMediaWidth  = $attachment[1] ?: null;
					 $originalMediaHeight = $attachment[2] ?: null;

					 foreach( $devices as $device ) {
						 $resizeW = $editorData['elements'][ $elementID ]['cropData'][ $device ]['mediaSize']['width'] ?? null;
						 $resizeH = $editorData['elements'][ $elementID ]['cropData'][ $device ]['mediaSize']['height'] ?? null;

						 [ $mediaWidth, $mediaHeight ] = Media::fitInBox( 'contain', $resizeW, $resizeH, $originalMediaWidth, $originalMediaHeight );
						 $editorData['elements'][ $elementID ]['cropData'][ $device ]['mediaSize']['width'] = $mediaWidth;
						 $editorData['elements'][ $elementID ]['cropData'][ $device ]['mediaSize']['height'] = $mediaHeight;
					 }
				 }
			}
		}

		return JSON::encode( $editorData );
	}
}
