<?php
namespace Depicter\Document\CSS;


use Averta\Core\Utility\Str;

class Selector
{
	const PREFIX_NAME = 'depicter';
	const PREFIX = 'depicter-';
	const PREFIX_CSS = 'depicter-revert';

	const DOCUMENT_PREFIX = 'document';
	const SECTION_PREFIX  = 'section';
	const ELEMENT_PREFIX  = 'element';

	/**
	 * Retrieves raw selector path for a tag.
	 *
	 * @param int      $documentId Document ID
	 * @param int|null $sectionId  Section ID
	 * @param int|null $elementId  Element ID
	 *
	 * @return string
	 */
	public static function getSelectorPath( $documentId, $sectionId = null, $elementId = null )
	{
		$selectorPath = "{$documentId}";
		if( $sectionId ){
			$selectorPath .= "-" . self::SECTION_PREFIX . '-' . $sectionId;
		}
		if( $elementId ){
			$selectorPath .= "-" . self::ELEMENT_PREFIX . '-' . $elementId;
		}

		return $selectorPath;
	}

	/**
	 * Retrieves selector path with prefix for a tag.
	 *
	 * @param int      $documentId  Document ID
	 * @param int|null $sectionId   Section ID
	 * @param int|null $elementId   Element ID
	 * @param string   $typePrefix  Target type ( document, section, element )
	 *
	 * @return string
	 */
	public static function getFullSelectorPath( $documentId, $sectionId = null, $elementId = null, $typePrefix = '' )
	{
		return self::prefixify( $typePrefix ) . ( $typePrefix ? '-' : '' ) . self::getSelectorPath( $documentId, $sectionId, $elementId );
	}

	public static function getUniqueSelector( $documentId, $sectionId = null, $elementId = null, $typePrefix = '' )
	{
		// return self::getHashedSelector( self::getSelectorPath( $documentId, $sectionId, $elementId ), $typePrefix );
		$sectionId = $typePrefix == 'element' ? null : $sectionId;
		$elementId = $typePrefix == 'section' ? null : $elementId;

		return self::getFullSelectorPath( $documentId, $sectionId, $elementId );
	}

	public static function getHashedSelector( $selector, $typePrefix = '' )
	{
		return self::prefixify( $typePrefix ) . ( $typePrefix ? '-' : '' ) . Str::shortHash( $selector );
	}

	public static function prefixify( $string = '' )
	{
		return self::PREFIX . $string;
	}

}
