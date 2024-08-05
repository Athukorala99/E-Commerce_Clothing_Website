<?php
namespace Depicter\Services;

use Averta\Core\Utility\Arr;
use Averta\WordPress\Utility\Sanitize;

class DocumentFontsV1Service
{
	/**
	 * @var array[]
	 */
	private $fonts = [];

	/**
	 * Get font families
	 *
	 * @param int      $documentId  The document ID to get belonging faces
	 * @param string   $type        Repository type of the font. `google`, `typescript`, ..
	 *
	 * @return int[]|string[]
	 */
	public function getFamilies( $documentId, $type = 'google' ) {
		return array_keys( $this->getFontsInfo( $documentId, $type ) );
	}

	/**
	 * Get collected fonts list
	 *
	 * @param int      $documentId  The document ID to get belonging faces
	 * @param string   $type        Repository type of the font. `google`, `typescript`, ..
	 *
	 * @return int[]|string[]
	 */
	public function getFontsInfo( $documentId, $type = 'google' ) {
		return ! empty( $this->fonts[ $documentId ][ $type ] ) ? $this->fonts[ $documentId ][ $type ] : [];
	}

	/**
	 * Get font faces
	 *
	 * @param int      $documentId  The document ID to get belonging faces
	 * @param string  $type        Repository type of the font. `google`, `typescript`, ..
	 *
	 * @return array
	 */
	public function getFontsLinkQuery( $documentId, $type = 'google' ) {
		if( empty( $this->fonts[ $documentId ][ $type ] ) ){
			return [];
		}

		$fontFaces = [];

		foreach ( $this->fonts[ $documentId ][ $type ] as $fontNameSlug => $fontInfo ) {

			if ( $fontNameSlug == 'inherit' ) {
				continue;
			}

			if( 'google' === $type && isset( $this->fonts[ $documentId ]['local'][ $fontNameSlug ] ) ){
				continue;
			}

			$weights = [];
			foreach ( $fontInfo['weights'] as $key => $weight ) {
				$weight = 'regular' == strtolower($weight) ? '400' : $weight;
				$weights[ $key ] = $weight;
			}

			// Sort weight number ascending
			ksort( $weights );

			// Make weight query string for current font
			$weightQuery = '';
			foreach ( $weights as $weightKey => $weight ){
				$weightQuery .= $weight . ',';
			}
			$weightQuery = rtrim($weightQuery, ',');

			// Collect font name and weights in a query string
			$fontFaces[] = $fontInfo['face'] . ':' . $weightQuery;
		}

		return $fontFaces;
	}

	/**
	 * Add a font to fonts list
	 *
	 * @param int          $documentId  The document ID that the font belongs to
	 * @param string       $fontName    Font family name
	 * @param string|array $fontWeight  Font weight
	 * @param string       $type        Repository type of the font. `google`, `typescript`, ..
	 */
	public function addFont( $documentId, $fontName, $fontWeight, $type = 'google' ) {
		if ( empty( $fontName ) ) {
			return;
		}

		$systemFonts = $this->systemFonts();
		if ( in_array( $fontName, $systemFonts ) ) {
			$type = 'system';
		}

		$fontName = str_replace( ['\"', '"'], ['', ''], trim( $fontName ) );
		$fontNameSlug = Sanitize::slug( $fontName );
		$fontWeight = (array) $fontWeight;

		$this->initFontForDocument( $documentId );

		if ( isset( $this->fonts[ $documentId ][ $type ][ $fontNameSlug ] ) ) {
			foreach ( $fontWeight as $weight ) {
				if ( !in_array( $weight, $this->fonts[ $documentId ][ $type ][ $fontNameSlug ]['weights'] ) ) {
					$this->fonts[ $documentId ][ $type ][ $fontNameSlug ]['weights'][] = $weight;
				}
			}
		} else {
			$this->fonts[ $documentId ][ $type ][ $fontNameSlug ] = [
				'family'    => $fontName,
				'face'      => str_replace( ' ', '+', $fontName ),
				'weights'   => $fontWeight
			];
		}
	}

	public function addLocalFont( $documentId, $fontName, $variants = [] ) {
		if ( empty( $fontName ) ) {
			return;
		}

		$fontName = str_replace( ['\"', '"'], ['', ''], trim( $fontName ) );
		$fontNameSlug = Sanitize::slug( $fontName );
		$variants = (array) $variants;

		if ( isset( $this->fonts[ $documentId ][ 'local' ][ $fontNameSlug ] ) ) {
			$this->fonts[ $documentId ][ 'local' ][ $fontNameSlug ]['variants'] = Arr::merge( $this->fonts[ $documentId ][ 'local' ][ $fontNameSlug ]['variants'], $variants );
		} else {
			$this->fonts[ $documentId ][ 'local' ][ $fontNameSlug ] = [
				'family'    => $fontName,
				'variants'  => $variants
			];
		}
	}

	/**
	 * Generate Css for loading local css fonts
	 *
	 * @param $documentId
	 *
	 * @return string
	 */
	public function getLocalFontsCss( $documentId ){
		$fontsCss = "";

		if( ! empty( $this->fonts[ $documentId ][ 'local' ] ) && is_array( $this->fonts[ $documentId ][ 'local' ] ) ){
			foreach( $this->fonts[ $documentId ][ 'local' ] as $fontInfo ){
				if( is_array( $fontInfo['variants'] ) ){
					foreach( $fontInfo['variants'] as $fontVariant ){
						$fontsCss .= "@font-face{\n";
						$fontsCss .= !empty( $fontInfo['family']   ) ? "\tfont-family:\"" . $fontInfo['family']    ."\";\n" : "";
						$fontsCss .= !empty( $fontVariant['style'] ) ? "\tfont-style:"    . $fontVariant['style']  .";\n" : "";
						$fontsCss .= !empty( $fontVariant['weight'] ) ? "\tfont-weight:"  . $fontVariant['weight'] .";\n" : "";
						$fontsCss .= "\tfont-display:fallback;\n";
						$fontsCss .= !empty( $fontVariant['src'] ) ? "\tsrc:". str_replace( '"', "'", $fontVariant['src'] ) .";\n" : "";
						$fontsCss .= "\tfont-stretch:normal;\n}\n";
					}
				}
			}
		}

		return $fontsCss;
	}

	protected function initFontForDocument( $documentId ){
		if( ! isset( $this->fonts[ $documentId ] ) ){
			$this->fonts[ $documentId ] = [
				'google' => []
			];
		}
	}

	/**
	 * Add list of fonts to fonts list
	 *
	 * @param int    $documentId  The document ID that fonts belong to
	 * @param array  $fontList
	 * @param string $type
	 */
	public function addFonts( $documentId, $fontList, $type = 'google' ) {
		if ( empty( $fontList ) ) {
			return;
		}

		foreach ( $fontList as $fontName => $fontWeight ){
			$this->addFont( $documentId, $fontName, $fontWeight, $type );
		}
	}

	/**
	 * Get system Fonts
	 *
	 * @return array
	 */
	public function systemFonts() {
		return [
			'Arial',
			'Helvetica',
			'Times New Roman',
			'Georgia',
			'Courier New',
			'Verdana',
			'Tahoma'
		];
	}

	/**
	 * Get fonts load link
	 *
	 * @param int    $documentId The document ID to get belonging faces
	 * @param string $type Repository type of the font. `google`, `typescript`, ..
	 *
	 * @return bool|string
	 */
	public function getFontsLink( $documentId, $type = 'google' ) {
		if ( $fontsQuery = $this->getFontsLinkQuery( $documentId, $type = 'google' ) ) {
			$fontsQuery = implode( "|", $fontsQuery );
			return 'https://fonts.googleapis.com/css?family=' . $fontsQuery . '&display=swap';
		}

		return '';
	}
}
