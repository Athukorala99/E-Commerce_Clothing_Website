<?php
namespace Depicter\Document\Models;

use Averta\Core\Utility\Arr;
use Depicter\Document\CSS\Selector;
use Depicter\Document\Helper\Helper;
use Depicter\Document\Models\Traits\HasDataSheetTrait;
use Depicter\Document\Models\Traits\HasDocumentIdTrait;
use Depicter\Html\Html;

class Section
{
	use HasDocumentIdTrait;
	use HasDataSheetTrait;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * List of belonging element ids (assigns with jsonMapper)
	 *
	 * @var array
	 */
	public $elements;

	/**
	 * List of belonging elements
	 *
	 * @var array
	 */
	public $elementObjects;

	/**
	 * @var Common\Size\States
	 */
	public $wrapperSize;

	/**
	 * @var Common\Background
	 */
	public $background;

	/**
	 * @var object
	 */
	public $options;

	/**
	 * @var \Depicter\Document\Models\Common\Animation|null
	 */
	public $animation;

	/**
	 * @var Common\Parallax|null
	 */
	public $parallax;

	/**
	 * @var Common\KenBurns|null
	 */
	public $kenBurns;


	/**
	 * @var Common\DataSourceOptions|null
	 */
	public $dataSource = null;

	/**
	 * @var array|null
	 */
	public $actions;

	/**
	 * @var string
	 */
	public $className = '';

	/**
	 * @var array
	 */
	protected $styleList = [];

	/**
	 * @var string
	 */
	protected $preloadTags = '';

	/**
	 * @var string
	 */
	public $visibility = 'visible';

	/**
	 * @var object|null
	 */
	public $visibilitySchedule;


	/**
	 * get section ID
	 *
	 * @return string
	 */
	public function getID() {
		return Helper::getSectionIdFromSlug( $this->id );
	}

	/**
	 * get section slug
	 *
	 * @return string
	 */
	public function getSlug() {
		return $this->id;
	}

	/**
	 * Get section name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name ? $this->name : $this->id;
	}

	/**
	 * Gets belonging element ids
	 *
	 * @return array
	 */
	public function getElementIds()
	{
		return $this->elements;
	}

	/**
	 * Sets belonging elements
	 *
	 * @param array $elementIds
	 */
	public function setElementIds( $elementIds )
	{
		$this->elements = $elementIds;
	}

	/**
	 * Sets belonging elements
	 *
	 * @param array $elementObjects
	 */
	public function setElementObjects( $elementObjects )
	{
		$this->elementObjects = $elementObjects;
	}

	/**
	 * Renders the Section
	 */
	public function render() {
		if ( $this->visibility == 'hidden' ) {
			return '';
		}

		if ( $this->visibility == 'scheduled' && !is_null( $this->visibilitySchedule ) ) {
			if ( !empty( $this->visibilitySchedule->start )  && !\Depicter::schedule()->isDatePassed( $this->visibilitySchedule->start ) ) {
				\Depicter::schedule()->scheduleClearCacheEvent( $this->getDocumentID(), $this->visibilitySchedule->start );
				return '';
			}

			if ( !empty( $this->visibilitySchedule->end )  && ! \Depicter::schedule()->isDatePassed( $this->visibilitySchedule->end ) ) {
				\Depicter::schedule()->scheduleClearCacheEvent( $this->getDocumentID(), $this->visibilitySchedule->end );
			}

			if ( !empty( $this->visibilitySchedule->end )  && \Depicter::schedule()->isDatePassed( $this->visibilitySchedule->end ) ) {
				return '';
			}
		}

		// render section by dataSource if exists
		if( $this->dataSource && $sectionMarkup = $this->renderDataSource() ){
			return $sectionMarkup;
		}
		return $this->renderStatic();
	}

	/**
	 * Renders the Section from dataSource
	 */
	protected function renderDataSource() {
		$sectionMarkup = '';
		$dataSourceInstance = \Depicter::dataSource()->getByType( $this->dataSource->type );
		if( $dataSourceInstance ){
			$dataSheets = $dataSourceInstance->getDataSheetArgs( $this->dataSource->params );
			foreach( $dataSheets as $dataSheet ){
				$this->setDataSheet( $dataSheet );
				$sectionMarkup .= $this->renderStatic();
			}
		}
		return $sectionMarkup;
	}


	/**
	 * Renders the Section
	 */
	protected function renderStatic() {

		// default Section properties
		$args = [
			'id'          => $this->getCssID(),
			'class'	      => $this->getClassNames(),
			'data-name'   => $this->getName()
		];

		if ( !empty( $this->wrapperSize ) ) {
			$sectionWidth = $this->wrapperSize->getResponsiveSizes( "width"  );
			if ( ! $this->wrapperSize->hasNoResponsiveSize( $sectionWidth ) ) {
				$args[ 'data-wrapper-width'] = implode( ',', $sectionWidth );
				$args[ 'data-wrapper-width'] = str_replace( 'auto', '', $args[ 'data-wrapper-width'] );
				if ( $args[ 'data-wrapper-width'] == ',,' ) {
					unset( $args[ 'data-wrapper-width'] );
				}
			}
			$sectionHeight = $this->wrapperSize->getResponsiveSizes( "height"  );
			if ( ! $this->wrapperSize->hasNoResponsiveSize( $sectionHeight ) ) {
				$args['data-wrapper-height'] = implode( ',', $sectionHeight );
				$args[ 'data-wrapper-height'] = str_replace( 'auto', '', $args[ 'data-wrapper-height'] );
				if ( $args[ 'data-wrapper-height'] == ',,' ) {
					unset( $args[ 'data-wrapper-height'] );
				}
			}
		}

		if ( !empty( $this->options ) ) {
			// check for autoplay option
			if ( !empty( $this->options->slideshowDuration ) ) {
				$args['data-slideshow-duration'] = $this->options->slideshowDuration;
			}
			// check for autoplay option
			if ( !empty( $this->options->pauseSlideshow ) ) {
				$args['data-slideshow-pause'] = $this->options->pauseSlideshow ? "true" : "false";
			}
		}

		if ( $this->background->hasBackground() && $this->parallax && false !== $parallaxData = $this->parallax->getParallaxAttrs() ) {
			$args = Arr::merge( $args, $parallaxData );
		}

		$actions = Helper::getActions( $this->actions );
		if ( !empty( $actions ) ) {
			$args = Arr::merge( $args, [
				'data-actions' => $actions
			]);
		}

		if ( $this->animation && false !== $animationData = $this->animation->getAnimationAttrs() ) {
			$args = Arr::merge( $args, $animationData );
		}

		$div = Html::div($args);

		// check if section has link
		if ( $this->hasEnabledLink() ) {
			// Section link anchor element should always have target="_black" unless openInNewTab is false.
			$urlArgs = isset( $this->options->url->openInNewTab ) && ! $this->options->url->openInNewTab ? [] : ['target' => '_blank'];
			$urlArgs['class'] = Selector::prefixify('section-link');

			if ( $urlPath = $this->getSectionUrl() ) {
				$div->nest( Html::a( '', $urlPath, $urlArgs ) . "\n" );
			}
		}
		if( $sectionBackground = $this->background->render() ){
			$this->background->setDataSheet( $this->getDataSheet() );

			if ( $this->kenBurns && false !== $kenBurnsData = $this->kenBurns->getKenBurnsAttrs() ) {
				$this->background->setKenBurnsData( $kenBurnsData );
			}

			$div->nest( "\n" . $this->background->render() . "\n\n" );
		}

		$this->styleList = Arr::merge( $this->styleList, $this->getSelectorAndCssList() );

		if ( !empty( $this->elementObjects ) ) {
			foreach ( $this->elementObjects as $elementObject ) {
				// if dataSheet is available for current section, assign it elements of this section as well
				if( $this->getDataSheet() ){
					$elementObject->prepare()->setDataSheet( $this->getDataSheet() );
				}
				// Get element style
				$this->styleList = Arr::merge( $this->styleList, $elementObject->prepare()->getSelectorAndCssList() );

				$div->nest( "\n" . $elementObject->prepare()->render() );

				// generate and retrieve media files of elements for preloading
				$this->preloadTags .= $elementObject->prepare()->getPreloadTags();
			}
		}

		return "\n" . $div;
	}

	/**
	 * Whether linking slide ro url is enabled or not
	 *
	 * @return bool
	 */
	public function hasEnabledLink(){
		return $this->isLinkedToDataSheet() || !empty( $this->options->url->enable );
	}

	/**
	 * Retrieves markup for preloading media files belonging to this section
	 *
	 * @return string
	 */
	public function getPreloadTags(){
		return $this->preloadTags;
	}

	/**
	 * Retrieves section url if exists
	 *
	 * @return string
	 */
	public function getSectionUrl(){
		if( $this->isLinkedToDataSheet() && $url = $this->getDataSheetUrl() ){
			return $url;
		} elseif( ! empty( $this->options->url->path ) ){
			return $this->options->url->path;
		}
		return '';
	}

	/**
	 * Collect element font and add it to document font service
	 */
	public function collectElementFonts(){
		if ( !empty( $this->elementObjects ) ) {
			foreach ( $this->elementObjects as $elementObject ) {
				$elementObject->prepare()->getFontsList();
			}
		}
	}

	/**
	 * Get section class names.
	 *
	 * @return string
	 */
	protected function getClassNames(){
		$classes = [];

		$classes[] = Selector::prefixify(Selector::SECTION_PREFIX );
		$classes[] = $this->getSelector();
		$classes[] = $this->getDataSheetClassName();
		$classes[] = $this->getCustomClassName();

		return trim( implode(' ', $classes) );
	}

	/**
	 * Get section CSS ID for data sheet if dataSource is assigned
	 *
	 * @return string
	 */
	public function getDataSheetClassName() {
		if( $this->getDataSheetID() ){
			return $this->getCssID();
		}
		return '';
	}

	/**
	 * Retrieves custom class name of document
	 *
	 * @return string
	 */
	protected function getCustomClassName(){
		if ( ! empty( $this->className ) ) {
			return $this->className;
		}
		return '';
	}

	/**
	 * Retrieves custom styles of document
	 *
	 * @return string
	 */
	protected function getCustomStyles(){
		if ( ! empty( $this->options->customStyle ) ) {
			$customStyles =  $this->options->customStyle;
			// replace "selector" with unique selector of section
			return str_replace('selector', '.'.$this->getStyleSelector(), $customStyles );
		}
		return '';
	}

	/**
	 * Get Elements Style that presented in this section.
	 *
	 * @return array
	 */
	public function getCss() {
		return $this->styleList;
	}

	/**
	 * Get section CSS ID
	 *
	 * @return string
	 */
	public function getCssID() {
		$cssID = Helper::getSectionCssId( $this->getDocumentID(), $this->getID() );
		if( $dataSheetId = $this->getDataSheetID() ){
			$cssID .= "-{$dataSheetId}";
		}

		return $cssID;
	}

	/**
	 * Get section selector
	 *
	 * @return string
	 */
	public function getSelector() {
		return Selector::getUniqueSelector( $this->getDocumentID(), $this->getID(),  null, Selector::SECTION_PREFIX );
	}

	/**
	 * Get section style selector
	 *
	 * @return string
	 */
	public function getStyleSelector() {
		return Selector::PREFIX_CSS . " ." . $this->getSelector();
	}

	/**
	 * Get list of selector and CSS for section
	 *
	 * @return array
	 */
	protected function getSelectorAndCssList(){
		$styleList = [];

		$styleList[ '.' . $this->getStyleSelector() . ' .' . $this->background->getContainerClassName() ] = array_merge_recursive(
			$this->background->getColor(),
			$this->background->getSectionBackgroundFilter()
		);
		$styleList[ '.' . $this->getStyleSelector() . ' .' . $this->background->getContainerClassName() . '::after' ] = $this->background->getOverlayStyles();

		if ( $this->getCustomStyles() ) {
			$styleList[ '.' . $this->getStyleSelector() ]['customStyle'] = $this->getCustomStyles();
		}

		return $styleList;
	}
}
