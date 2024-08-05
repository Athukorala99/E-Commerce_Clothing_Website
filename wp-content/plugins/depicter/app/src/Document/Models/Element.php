<?php
namespace Depicter\Document\Models;

use Averta\Core\Utility\Arr;
use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\CSS\Selector;
use Depicter\Document\Helper\Helper;
use Depicter\Document\Models\Traits\HasDataSheetTrait;
use Depicter\Document\Models\Traits\HasDocumentIdTrait;
use Depicter\Editor\Models\Common\Size;
use Depicter\Editor\Models\Common\Styles;
use Depicter\Html\Html;

class Element
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
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $section;

	/**
	 * @var array|null
	 */
	public $hideOnSections = [];

	/**
	 * @var string
	 */
	public $parent;

	/**
	 * @var \Depicter\Document\Models\Common\Position\States
	 */
	public $position;

	/**
	 * @var bool
	 */
	public $wrap;

	/**
	 * @var bool
	 */
	public $locked = false;

	/**
	 * @var object
	 */
	public $visible = true;

	/**
	 * @var bool|null
	 */
	public $keepAspectRatio;

	/**
	 * @var array|null
	 */
	public $children;

	/**
	 * @var array
	 */
	public $childrenObjects = [];

	/**
	 * @var \Depicter\Document\Models\Common\Size\States|null
	 */
	public $size;

	/**
	 * @var \Depicter\Document\Models\Common\Styles|null
	 */
	public $styles;

	/**
	 * @var \Depicter\Document\Models\Common\InnerStyles|null
	 */
	public $innerStyles;

	/**
	 * @var object|null
	 */
	public $options;

	/**
	 * @var \Depicter\Document\Models\Common\Animation|null
	 */
	public $animation;

	/**
	 * @var \Depicter\Document\Models\Common\Parallax|null
	 */
	public $parallax;

	/**
	 * @var array|null
	 */
	public $actions;

	/**
	 * @var int
	 */
	public $depth;

	/**
	 * @var array
	 */
	public $devices;

	/**
	 * Selector and CSS list
	 *
	 * @var array
	 */
	protected $selectorCssList = [];

	/**
	 * Instance of Element type or base Element class
	 *
	 * @var Element
	 */
	protected $alias;

	/**
	 * @var object|null
	 */
	public $cropData;

	/**
	 * @var \Depicter\Document\Models\Common\ResponsiveScale|null
	 */
	public $responsiveScale;

	/**
	 * @var string
	 */
	protected $markup;



	/**
	 * Element constructor.
	 */
	public function __construct() {
		$this->devices = Breakpoints::names();
	}

	/**
	 * get element number
	 *
	 * @return string
	 */
	public function getID() {
		return Helper::getElementIdFromSlug( $this->id );
	}

	/**
	 * get element slug
	 *
	 * @return string
	 */
	public function getSlug() {
		return $this->id;
	}

	/**
	 * Get element name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name ? $this->name : $this->id;
	}

	/**
	 * Get section ID number
	 *
	 * @return int
	 */
	public function getSectionID() {
		return Helper::getSectionIdFromSlug( $this->section );
	}

	/**
	 * Return child elements IDs for group type element
	 * @return array|null
	 */
	public function getElementIds() {
		return $this->children;
	}

	/**
	 * Sets belonging elements for group type element
	 *
	 * @param array $elementObjects
	 */
	public function setElementObjects( $elementObjects )
	{
		$this->childrenObjects = $elementObjects;
	}

	/**
	 * Prepare class for rendering markup
	 *
	 * @return Element
	 * @throws \JsonMapper_Exception
	 */
	public function prepare() {
		return $this->alias();
	}

	/**
	 * Check if unique element class exist then render element through that class
	 *
	 * @return Element
	 * @throws \JsonMapper_Exception
	 */
	public function alias(){
		if( $this->alias ){
			return $this->alias;
		}

		$className = '\\Depicter\\Document\\Models\\Elements\\' . ucfirst( $this->type );
		if ( class_exists( $className ) ) {
			$mapper = new \JsonMapper();
			$this->alias = $mapper->map( $this, new $className() );
			// pass the document ID to new class too
			$this->alias->setDocumentID( $this->getDocumentID() );

			return $this->alias;
		}

		return $this;
	}

	public function render() {}

	/**
	 * Get list of CSS class names of this element
	 *
	 * @return array
	 */
	public function getClassNamesList() {
		$classes = [];

		$classes[] = Selector::prefixify('element');
		$classes[] = Selector::prefixify('layer');

		$classes[] = $this->getSelector();

		if( $this->getDataSheetClassName() ){
			$classes[] = $this->getDataSheetClassName();
		}

		if( $this->options->className ?? 0 ){
			$classes[] = $this->options->className;
		}

		if ( isset( $this->visible->default ) && $this->visible->default === false ) {
			$classes[] = 'depicter-hide-on-desktop ';
		}

		if ( isset( $this->visible->tablet ) && $this->visible->tablet === false ) {
			$classes[] = 'depicter-hide-on-tablet ';
		}

		if ( isset( $this->visible->mobile ) && $this->visible->mobile === false ) {
			$classes[] = 'depicter-hide-on-mobile ';
		}

		return $classes;
	}

	/**
	 * Get CSS class names of this element
	 *
	 * @return string
	 */
	public function getClassNames() {
		return trim( implode(' ', $this->getClassNamesList() ) );
	}

	/**
	 * Get default data attributes
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getDefaultAttributes() {
		$dataAttrs = [
			'id'             => $this->getCssID(),
			'class'          => $this->getClassNames(),
			'data-type'      => $this->type,
			'data-wrap'      => !!$this->wrap ? "true" : "false",
			'data-name'		 => $this->getName()
		];

		if( ! empty( $this->position->getOffset("default" ) ) ){
			$dataAttrs['data-offset'] = $this->position->getOffset("default" );
		}
		if( ! empty( $this->position->getOffset("tablet" ) ) ){
			$dataAttrs['data-tablet-offset'] = $this->position->getOffset("tablet" );
		}
		if( ! empty( $this->position->getOffset("mobile" ) ) ){
			$dataAttrs['data-mobile-offset'] = $this->position->getOffset("mobile" );
		}

		if( $this->hideOnSections && $hideOnSections = Helper::getInvisibleSectionsCssIdList( $this->hideOnSections, $this->getDocumentID() ) ){
			$dataAttrs['data-hide-on-sections'] = $hideOnSections;
		}

		if ( $actions = Helper::getActions( $this->actions ) ) {
			$dataAttrs['data-actions'] = $actions;
		}
		if ( $this->animation && false !== $animationData = $this->animation->getAnimationAttrs() ) {
			$dataAttrs = Arr::merge( $dataAttrs, $animationData );
		}

		if ( $this->parallax && false !== $parallaxData = $this->parallax->getParallaxAttrs() ) {
			$dataAttrs = Arr::merge( $dataAttrs, $parallaxData );
		}

		if( ! empty( $this->size ) ){
			$dataAttrs['data-width' ] = implode( ',', $this->size->getResponsiveSizes( 'width' , true ) );
			$dataAttrs['data-height'] = implode( ',', $this->size->getResponsiveSizes( 'height', true ) );
		}

		$dataAttrs['data-responsive-scale' ] = ! empty( $this->responsiveScale ) ? implode( ',', $this->responsiveScale->getResponsiveSizes() ) : 'true,,';

		if( ! empty( $this->prepare()->styles->hover ) && $hoverOffDevices = $this->prepare()->styles->hover->getDisabledDeviceList() ){
			$dataAttrs['data-hover-off' ] = implode( ',', $hoverOffDevices );
		}

		if ( ! empty( $this->prepare()->styles->blendingMode ) ) {
			$dataAttrs['data-frame-class'] = $this->getSelector() . '-frame' ;
		}

		return $dataAttrs;
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
	 * Check if element has link
	 * @return false|\TypeRocket\Html\Html
	 */
	public function getLinkTag() {
		if ( !empty( $this->options->url->path ) ) {
			$urlPath = $this->maybeReplaceDataSheetTags( $this->options->url->path );
			$urlArgs = isset( $this->options->url->openInNewTab ) && empty( $this->options->url->openInNewTab ) ? [] : ['target' => '_blank'];
			return Html::a( '', $urlPath, $urlArgs );
		}

		return false;
	}

	/**
	 * Retrieves list of fonts used in typography options
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getFontsList()
	{
		$fontsList = ! empty( $this->prepare()->styles ) ? $this->prepare()->styles->getFontsList() : [];
		\Depicter::app()->documentFonts()->addFonts( $this->getDocumentID(), $fontsList, 'google' );

		return $fontsList;
	}

	/**
	 * Retrieves the content of element
	 *
	 * @return string
	 */
	protected function getContent(){
		return $this->options->content ?? '';
	}

	/**
	 * Get element CSS ID
	 *
	 * @return string
	 */
	public function getCssID() {
		$cssID = Selector::getFullSelectorPath( $this->getDocumentID(), null, $this->getID() );
		if( $dataSheetId = $this->getDataSheetID() ){
			$cssID .= "-{$dataSheetId}";
		}

		return $cssID;
	}

	/**
	 * Get element selector
	 *
	 * @return string
	 */
	public function getSelector() {
		return Selector::getUniqueSelector( $this->getDocumentID(), $this->getSectionID(), $this->getID(), Selector::ELEMENT_PREFIX );
	}


	/**
	 * Get element style selector
	 *
	 * @return string
	 */
	public function getStyleSelector() {
		return Selector::PREFIX_CSS . " ." . $this->getSelector();
	}

	/**
	 * Get element style selector
	 *
	 * @return string
	 */
	public function getStyleSelectorHoverEnabled() {
		return $this->getStyleSelector() . ':not(.depicter-hover-off)';
	}

	/**
	 * Get element frame selector
	 *
	 * @return string
	 */
	public function getFrameStyleSelector() {
		return Selector::PREFIX_CSS . ' .' . $this->getSelector() . '-frame';
	}

	/**
	 * Get list of selector and CSS for element
	 *
	 * @return array
	 * @throws \JsonMapper_Exception
	 */
	public function getSelectorAndCssList(){

		if( ! empty( $this->prepare()->styles ) ){
			$this->prepare()->styles->setDataSheet( $this->getDataSheet() );
			$this->selectorCssList[ '.' . $this->getStyleSelector() ] = $this->prepare()->styles->getGeneralCss('normal');

			$transitionCss = $this->prepare()->styles->getTransitionCss();
			$hoverCss = $this->prepare()->styles->getGeneralCss('hover');
			$transitionCss['hover'] = $hoverCss['hover'];

			$this->selectorCssList[ '.' . $this->getStyleSelectorHoverEnabled() ] = $transitionCss;

			if ( !empty( $this->prepare()->styles->blendingMode ) ) {
				$this->selectorCssList[ '.' . $this->getFrameStyleSelector() ] = $this->prepare()->styles->getBlendingModeStyle();
			}
		}

		if ( $this->getCustomStyles() ) {
			$this->selectorCssList[ '.' . $this->getStyleSelector() ]['customStyle'] = $this->getCustomStyles();
		}

		return $this->selectorCssList;
	}

	/**
	 * Retrieves custom styles of document
	 *
	 * @return string
	 */
	protected function getCustomStyles(){
		if ( ! is_null( $this->options ) && ! empty( $this->options->customStyle ) ) {
			$customStyles =  $this->options->customStyle;
			// replace "selector" with unique selector of section
			return str_replace('selector', '.'.$this->getStyleSelector(), $customStyles );
		}
		return '';
	}

	/**
	 * Get markup for preloading media urls
	 *
	 * @return string
	 */
	public function getPreloadTags(){
		if( method_exists( $this, 'getPreloadMarkup' ) ){
			return $this->getPreloadMarkup();
		}
		return '';
	}

}
