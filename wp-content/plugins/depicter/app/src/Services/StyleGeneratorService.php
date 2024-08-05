<?php
namespace Depicter\Services;

use Averta\Core\Utility\Arr;
use Averta\WordPress\Utility\Sanitize;
use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\CSS\Selector;
use Depicter\Document\Models\Traits\HasDocumentIdTrait;
use Depicter\Html\Tag;

class StyleGeneratorService
{
	use HasDocumentIdTrait;

	/**
	 * @var string
	 */
	protected $css;

	/**
	 * Document raw styles list
	 * @var array
	 */
	private $stylesList;

	/**
	 * Is filesystem writable or not
	 *
	 * @var bool
	 */
	protected $isWritable = null;

	/**
	 * Before init styles of document
	 *
	 * @var string
	 */
	protected $beforeInitStyle = '';

	/**
	 * Class extra options
	 *
	 * @var array
	 */
	protected $args = [];


	public function __construct( $stylesList = [], $documentID = 0, $args = [] ) {
		$this->setDocumentId( $documentID );
		$this->setStylesList( $stylesList );

		$this->args = Arr::merge( $args, [
			'addImportant' => false
		]);
	}

	public function setStylesList( $stylesList ){
		if ( $stylesList ) {
			$this->stylesList = $stylesList;
		}
	}

	/**
	 * Generate Css Styles
	 *
	 * @param array $stylesList
	 *
	 * @return string
	 */
	protected function generateCss( $stylesList = [] ) {

		if ( empty( $stylesList ) ) {
			return '';
		}

		$css     = '';
		$devices = Breakpoints::names();
		$breakpoints = Breakpoints::all();

		$default = "\n";
		$tablet  = '';
		$mobile  = '';
		$custom_style = '';
		$maybeImportantSuffix = $this->args['addImportant'] ? ' !important' : '';

		foreach ( $stylesList as $selector => $cssProperties ) {

			if ( !empty( $cssProperties['customStyle'] ) ) {
				$custom_style .= $cssProperties['customStyle'] . "\n";
			}

			if ( !empty( $cssProperties['beforeInitStyle'] ) && is_array( $cssProperties['beforeInitStyle'] ) ) {
				$this->beforeInitStyle = $this->generateCss( $cssProperties['beforeInitStyle'] );
			}

			foreach ( $devices as $device ) {
				if ( !empty( $cssProperties[ $device ] ) ) {
					$$device .= $selector . "{\n";
					foreach ( $cssProperties[ $device ] as $property => $value ) {
						$$device .= "\t{$property}:{$value}{$maybeImportantSuffix};\n";
					}
					$$device .= "}\n";
				}

				// check for hover styles
				if ( !empty( $cssProperties['hover'][ $device ] ) ) {
					$$device .= $selector . ":hover {\n";
					foreach ( $cssProperties['hover'][ $device ] as $property => $value ) {
						$$device .= "\t{$property}:{$value}{$maybeImportantSuffix};\n";
					}
					$$device .= "}\n";
				}
			}
		}

		if( $tablet ){
			$tablet = "\n/***** Tablet *****/\n@media screen and (max-width: {$breakpoints['tablet']}px){\n\n{$tablet}\n}";
		}
		if( $mobile ){
			$mobile = "\n/***** Mobile *****/\n@media screen and (max-width: {$breakpoints['mobile']}px){\n\n{$mobile}\n}";
		}

		$css = $default . $tablet . $mobile;

		if( $custom_style ){
			$css .= "\n/*** Custom styles ***/\n$custom_style";
		}

		return $css;
	}

	/**
	 * Retrieves CSS with style tag
	 *
	 * @param bool $forceRegenerateStyles
	 *
	 * @return string
	 */
	public function getCssAndTag( $forceRegenerateStyles = false ) {
		$attributes = [ 'id' =>  Selector::prefixify( $this->getDocumentID() ) . '-inline-css' ];
		return $this->wrapStyleWithTag( $this->getCss( $forceRegenerateStyles ), $attributes );
	}

	/**
	 * Get generated CSS
	 *
	 * @param bool $forceRegenerateStyles
	 *
	 * @return string
	 */
	public function getCss( $forceRegenerateStyles = false ) {
		if( ! $this->css || $forceRegenerateStyles  ){
			$this->css = $this->generateCss( $this->stylesList );
		}
		return $this->css . "\n";
	}

	/**
	 * Get before init CSS
	 *
	 * @param bool $forceRegenerateStyles
	 *
	 * @return string
	 */
	public function getBeforeInitCss( $forceRegenerateStyles = false ) {
		if( ! $this->beforeInitStyle|| $forceRegenerateStyles  ){
			$this->generateCss( $this->stylesList );
		}
		return $this->beforeInitStyle . "\n";
	}

	/**
	 * Get before init CSS
	 *
	 * @param bool $forceRegenerateStyles
	 *
	 * @return string
	 */
	public function getBeforeInitCssAndTag( $forceRegenerateStyles = false ) {
		$attributes = [ 'id' =>  Selector::prefixify( $this->getDocumentID() ) . '-inline-pre-css' ];
		return $this->wrapStyleWithTag( $this->getBeforeInitCss( $forceRegenerateStyles ), $attributes );
	}

	/**
	 * Save CSS in upload folder
	 *
	 * @param bool $forceRegenerateStyles
	 *
	 * @return StyleGeneratorService
	 */
	public function saveCss( $forceRegenerateStyles = false ) {
		$this->isWritable =\Depicter::storage()->filesystem()->write( $this->getCssFilePath(), $this->getCss( $forceRegenerateStyles ) );

		return $this;
	}

	/**
	 * Retrieves the path to generated css file for current document
	 *
	 * @param bool $checkExistence
	 *
	 * @return bool|string   false on not founding the file
	 */
	public function getCssFilePath( $checkExistence = false ){
		$cssFilePath = \Depicter::storage()->getCssUploadsDirectory() . '/' . $this->getDocumentID() . '.css';
		if( $checkExistence ){
			return file_exists( $cssFilePath ) ? $cssFilePath : false;
		}
		return $cssFilePath;
	}

	/**
	 * Retrieves the url of generated css file for current document
	 *
	 * @return bool|string  false on not founding the file
	 */
	public function getCssFileUrl(){
		$cssFilePath = $this->getCssFilePath( true );

		if( $cssFilePath !== false ){
			return \Depicter::storage()->getCssUploadsUrl() . '/' . $this->getDocumentID() . '.css';
		}

		return false;
	}

	/**
	 * Whether filesystem is writable or not
	 *
	 * @return bool
	 */
	public function isWritable(){
		return $this->isWritable;
	}

	/**
	 * Wraps style within a style tag
	 *
	 * @return string
	 */
	protected function wrapStyleWithTag( $style, $attributes = [] ) {
		return Tag::el('style', $attributes, "\n" . $style ) . "\n";
	}
}
