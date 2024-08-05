<?php

namespace Depicter\Editor;

use Averta\Core\Utility\Arr;
use Averta\WordPress\Utility\JSON;
use Depicter\Document\Helper\Helper;

class EditorData {

	/**
	 * Duplicates and returns a section and corresponding elements with new IDs assigned
	 *
	 * @param string $sectionId Section ID
	 * @param array $editorData EditorData in associative array
	 *
	 * @return array
	 */
	public function duplicateAndReturnSectionWithElements( $sectionId = '', $editorData = [] ): array{

	    $clonedElements = [];
		$newSectionID = $this->getNextAvailableSectionId( $editorData );
		$newSection = [
			$newSectionID => $editorData['sections'][ $sectionId ]
		];
        $newSection[ $newSectionID ]['id'] = $newSectionID;

	    foreach( $editorData['sections'][ $sectionId ]['elements'] as $elementID ) {
		    $clonedElements[ $elementID ] = $editorData['elements'][ $elementID ];
	    }

		$IdListOfNewElements = [];
		$duplicatedElements = [];

		$newElementIDNumber = $this->getNextAvailableElementNumber( $editorData );

		foreach( $clonedElements as $element ) {
			$duplicatedElements[ 'element-' . $newElementIDNumber ] = $element;

			if ( isset( $duplicatedElements[ 'element-' . $newElementIDNumber ]['section'] ) ) {
				$duplicatedElements[ 'element-' . $newElementIDNumber ]['section'] = $newSectionID;
			}
			if ( isset( $duplicatedElements[ 'element-' . $newElementIDNumber ]['parent'] ) ) {
				$duplicatedElements[ 'element-' . $newElementIDNumber ]['parent'] = $newSectionID;
			}
			if ( isset( $duplicatedElements[ 'element-' . $newElementIDNumber ]['id'] ) ) {
				$duplicatedElements[ 'element-' . $newElementIDNumber ]['id'] = 'element-' . $newElementIDNumber;
			}

			$IdListOfNewElements[] =  'element-' . $newElementIDNumber;
			++$newElementIDNumber;
		}

		$newSection[ $newSectionID ]['elements'] = $IdListOfNewElements;

		return [
            'sections' => $newSection,
            'elements' => $duplicatedElements
        ];
	}

	/**
	 * Duplicates a section and corresponding elements with new IDs assigned in EditorData
	 *
	 * @param string $sectionId Section ID
	 * @param array $editorData EditorData in associative array
	 * @param int $numberOfSections
	 *
	 * @return array
	 */
	public function duplicateSectionWithElements( $sectionId, $editorData = [], $numberOfSections = 1 ): array{
		if ( !is_null( $sectionId ) && !empty( $numberOfSections ) && $numberOfSections > 1 ) {
			for ( $i = 1; $i < $numberOfSections; $i++ ) {

				$duplicates = $this->duplicateAndReturnSectionWithElements( $sectionId, $editorData );

				if( ! empty( $duplicates['sections'] ) ){
					foreach ( $duplicates['sections'] as $sectionID => $elements ) {
						$editorData['sectionsList'][] = $sectionID;
					}
					$editorData['sections'] = Arr::merge( $duplicates['sections'], $editorData['sections'] );
				}
				if( ! empty( $duplicates['elements'] ) ){
					$editorData['elements'] = Arr::merge( $duplicates['elements'], $editorData['elements'] );
				}
			}
		}

		return $editorData;
	}

	/**
	 * Get a new unreserved section ID
	 *
	 * @param array $editorData  EditorData in associative array
	 *
	 * @return string  New section ID
	 */
	protected function getNextAvailableSectionId( $editorData = [] ){

		$sectionIDs = [];
		foreach( $editorData['sections'] as $sectionID => $elementsID ) {
			$sectionIDs[] = str_replace( 'section-', '', $sectionID );
		}

		$sectionMaxID = max( $sectionIDs ) + 1;
		return 'section-' . $sectionMaxID;
	}

	/**
	 * Get a new unreserved element ID
	 *
	 * @param array $editorData  EditorData in associative array
	 *
	 * @return string  New element ID
	 */
	protected function getNextAvailableElementId( $editorData = [] ){
		return 'element-' . $this->getNextAvailableElementNumber( $editorData );
	}

	/**
	 * Get a new unreserved element index number
	 *
	 * @param array $editorData  EditorData in associative array
	 *
	 * @return string  New element ID
	 */
	protected function getNextAvailableElementNumber( $editorData = [] ){
		$elementsID = [];
		foreach( $editorData['elements'] as $elementID => $element ) {
			$elementsID[] = str_replace( 'element-','', $elementID );
		}
		return max( $elementsID ) + 1;
	}
}
