<?php
namespace Depicter\Services;

class DocumentFontsService
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

		foreach ( $this->fonts[ $documentId ][ $type ] as $fontFamily => $fontInfo ) {
			$weights = [];
			foreach ( $fontInfo['weights'] as $weight ) {
				$weight = 'regular' == strtolower($weight) ? '400' : $weight;

				// lets convert 300italic to 1,300
				if (strpos($weight, 'italic')) {
					$weightNumber = str_replace('italic', '', $weight);
					$italicNumber = '1'; // 0 means normal and 1 is italic
				} else {
					$weightNumber = $weight;
					$italicNumber = '0'; // 0 means normal and 1 is italic
				}
				$weights[$weightNumber] = $italicNumber;
			}

			// Sort weight number ascending
			ksort( $weights );

			// Make weight query string for current font
			$weightQuery = '';
			foreach ( $weights as $weightNumber => $italicNumber ){
				$weightQuery .= "{$italicNumber},{$weightNumber};";
			}
			$weightQuery = rtrim($weightQuery, ';');

			// Collect font name and weights in a query string
			$fontFaces[] = $fontInfo['face'] . ':ital,wght@' . $weightQuery;
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

		$fontName = trim( $fontName );
		$fontWeight = (array) $fontWeight;

		$this->initFontForDocument( $documentId );

		if ( isset( $this->fonts[ $documentId ][ $type ][ $fontName ] ) ) {
			foreach ( $fontWeight as $key => $weight ) {
				if ( !in_array( $weight, $this->fonts[ $documentId ][ $type ][ $fontName ]['weights'] ) ) {
					$this->fonts[ $documentId ][ $type ][ $fontName ]['weights'][] = $weight;
				}
			}
		} else {
			$this->fonts[ $documentId ][ $type ][ $fontName ] = [
				'face'      => str_replace( ' ', '+', $fontName ),
				'weights'   => $fontWeight
			];
		}
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
	 * Get fonts load link
	 *
	 * @param int    $documentId The document ID to get belonging faces
	 * @param string $type Repository type of the font. `google`, `typescript`, ..
	 *
	 * @return bool|string
	 */
	public function getFontsLink( $documentId, $type = 'google' ) {
		if ( $fontsQuery = $this->getFontsLinkQuery( $documentId, $type = 'google' ) ) {
			$fontsQuery = implode( "&family=", $fontsQuery );
			return '//fonts.googleapis.com/css2?family=' . $fontsQuery . '&display=swap';
		}

		return '';
	}
}
