<?php
namespace Depicter\Document\Models;


use Averta\Core\Hydrate\HydratableInterface;
use Averta\Core\Utility\Arr;
use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\Sanitize;
use Depicter\Document\CSS\Selector;
use Depicter\Document\Models\Common\Animation;
use Depicter\Document\Models\Options\Loading;
use Depicter\Document\Models\Options\Script;
use Depicter\Document\Models\Traits\UnPublishedNoticeTrait;
use Depicter\Html\Html;
use Depicter\Services\StyleGeneratorService;

class Document implements HydratableInterface
{
	use UnPublishedNoticeTrait;


	/**
	 * @var array
	 */
	public $sectionsList;

	/**
	 * @var Section[]
	 */
	public $sections;

	/**
	 * @var Element[]
	 */
	public $elements;

	/**
	 * @var array
	 */
	public $foregroundElements;

	/**
	 * @var array
	 */
	public $foregroundElementObjects = [];

	/**
	 * @var Options\All
	 */
	public $options;

	/**
	 * @var array
	 */
	public $meta;

	/**
	 * @var array
	 */
	public $env = [];

	/**
	 * Start from which section
	 *
	 * @var int
	 */
	public $startSection = 0;

	/**
	 * Document markup
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * Style generator class instance
	 *
	 * @var StyleGeneratorService
	 */
	protected $styleGenerator;

	/**
	 * Collected styles in a list
	 *
	 * @var array
	 */
	protected $stylesList = [];

	/**
	 * Collected fonts from belonging elements
	 *
	 * @var array
	 */
	private $fontsList = [];

	/**
	 * Link to download all used fonts in this document
	 *
	 * @var string|null
	 */
	private $fontLink;

	/**
	 * Extra document args
	 *
	 * @var array
	 */
	private $args = [];

	/**
	 * Check if document is ai generated or not
	 *
	 * @var boolean
	 */
	public $isBuildWithAI = false;

	/**
	 * Extract values for this class
	 *
	 * @return array
	 */
	public function getProperties()
	{
		return [
			'name'           => $this->getName(),
			'slug'           => $this->getSlug(),
			'sections_count' => $this->getSectionsCount(),
			'content' 		 => $this->get(),
			'status'  		 => 'published'
		];
	}

	/**
	 * Extract values for this class
	 *
	 * @return array
	 */
	public function extract()
	{
		// TODO: Implement extract() method.
	}

	/**
	 * Hydrate the class with the provided $data.
	 *
	 * @param array|object $data
	 */
	public function hydrate($data)
	{
		// TODO: Implement hydrate() method.
	}

	/**
	 * Prepare document for generating markup
	 *
	 * @return $this
	 */
	public function prepare()
	{
		$this->reorderSections();
		$this->setElementsForObjects();
		$this->setForegroundElementObjects();

		return $this;
	}

	public function render()
	{
		$documentAnimationAttrs = [];

		if ( $this->isDisplayExtension() ) {
			if ( !empty( $this->options->documentTypeOptions->displayOptions->animation ) ) {
				$documentAnimation = (new \JsonMapper())->map( $this->options->documentTypeOptions->displayOptions->animation, new Animation() );
				$documentAnimationAttrs = $documentAnimation->getAnimationAttrs();
			}
		}

		$this->html = Html::div( Arr::merge([
            'id'    => $this->getCssId(),
            'class' => $this->getClassNames()
        ], $documentAnimationAttrs ) );

		$this->renderNotice();
		$this->renderLoadingSymbol();
		$this->renderForegroundElements();
		$this->renderSectionsAndElements();
		$this->collectAndSetFontsData();
		$this->getSelectorAndCssList();
		$this->renderSymbols();

		return $this->html . $this->getInitScriptTag();
	}

	/**
	 * Check if document is display extensions or not
	 *
	 * @return bool
	 */
	public function isDisplayExtension(): bool{
		$displayExtensions = [
			'popup',
			'banner-bar'
		];

		return in_array( $this->getType(), $displayExtensions );
	}

	/**
	 * Render markup for possible notices
	 *
	 * @return void
	 */
	protected function renderNotice(){
		$notice = $this->getUnpublishedChangesNotice();
		$this->html->nest( "\n" . $notice );
	}

	/**
	 * Render markup for loading symbols
	 */
	protected function renderLoadingSymbol() {
		if ( empty( $this->options->loading ) ) {
			$this->options->loading = new Loading();
		}
		$this->html->nest( "\n" . $this->options->loading->render() );
	}

	/**
	 * Render symbols markup
	 */
	protected function renderSymbols() {
		$symbolsContent = \Depicter::symbolsProvider()->render();
		if ( !empty( $symbolsContent ) ) {
			$this->html->nest( "\n" . $symbolsContent );
		}
	}

	/**
	 * Render markup and collect styles of foreground elements.
	 */
	protected function renderForegroundElements(){

		$foregroundAttributes = [ 'class' => Selector::prefixify('overlay-layers') ];
		$elementsMarkup = "";

		foreach ( $this->foregroundElementObjects as $element ) {
			$this->stylesList = array_merge( $this->stylesList, $element->prepare()->getSelectorAndCssList() );
			$elementsMarkup .= $element->prepare()->render() . "\n";
		}

		if( $elementsMarkup ){
			$foregroundDiv = Html::div( $foregroundAttributes, "\n" . $elementsMarkup );
			$this->html->nest( "\n\n" . $foregroundDiv . "\n" );
		}
	}

	/**
	 * Get the font link for loading document fonts
	 *
	 * @return string|null
	 */
	public function getFontsLink()
	{
		if( is_null( $this->fontLink ) ){
			$this->collectAndSetFontsData();
		}
		return $this->fontLink;
	}

	/**
	 * Render markup and collect styles of sections and nested elements.
	 */
	protected function renderSectionsAndElements(){
		foreach ( $this->sections as $section ) {
			$this->html->nest( $section->render() . "\n" );
			$this->stylesList = array_merge( $this->stylesList, $section->getCss() );
		}
	}

	/**
	 * Collects fonts and generates a link for loading document fonts
	 */
	protected function collectAndSetFontsData(){
		if( !empty( $this->env['additionalFonts'] ) ){
			// convert to array recursively
			$this->env['additionalFonts'] = Data::cast( $this->env['additionalFonts'], 'array' );

			if( is_array( $this->env['additionalFonts'] ) ){
				foreach( $this->env['additionalFonts'] as $localFontName => $localFontInfo ){
					\Depicter::documentFonts()->addLocalFont( $this->getDocumentID(), $localFontName, $localFontInfo['variants'] );
				}
			}
		}

		foreach ( $this->sections as $section ) {
			// this method adds element fonts to documentFonts service
			$section->collectElementFonts();
		}
		$this->fontLink = \Depicter::documentFonts()->getFontsLink( $this->getDocumentID() );
	}

	/**
	 * Render document custom styles
	 */
	/**
	 * Get list of selector and CSS for section
	 *
	 * @return array
	 */
	protected function getSelectorAndCssList(){
		if( ! isset( $this->stylesList[ '.'. $this->getSelector() ] ) ){
			$this->stylesList[ '.'. $this->getSelector() ] = [];
		}

		$documentStyles = [
			'.'. $this->getStyleSelector() . ' .depicter-section' => $this->options->getSectionGeneralStyles(),
			'.'. $this->getStyleSelector() . ' .depicter-layers-wrapper' => $this->options->getLayersWrapperStyles()
		];
		// prepend document styles at the beginning of the styles
		$this->stylesList = $documentStyles + $this->stylesList;
		$backdropStyles = $this->options->getBackdropStyles();
		if ( ! empty( $backdropStyles ) ) {
			$this->stylesList = [
				'.' . $this->getDisplayStyleSelector() . ' .depicter-backdrop' => $backdropStyles
			] + $this->stylesList;
		}

		$this->stylesList[ '.'. $this->getStyleSelector() ]['customStyle'] = $this->getCustomStyles();

		// add before init styles separately to style list as well
		$this->stylesList[ '.'. $this->getStyleSelector() ]['beforeInitStyle'] = [
			'.'. $this->getStyleSelector() => $this->options->getStyles(),
			'.'. $this->getStyleSelector( true ) . ':not(.depicter-ready)' => $this->options->getBeforeInitStyles(), // styles to prevent FOUC. It should not have depicter-revert class in selector
		];

		return $this->stylesList;
	}

	/**
	 * Retrieves StyleGeneratorService
	 *
	 * @param bool $args
	 *
	 * @return StyleGeneratorService
	 */
	public function styleGenerator( $args = [] ){
		$args = Arr::merge( $args, [
			'forceRegenerateStyles' => false
		]);

		if( ! $this->styleGenerator ){
			$this->styleGenerator =  new StyleGeneratorService( $this->stylesList, $this->getDocumentID(), $args );
		}
		if( $args['forceRegenerateStyles'] ){
			$this->styleGenerator->setStylesList( $this->stylesList );
		}

		return $this->styleGenerator;
	}

	/**
	 * Saves generated css in file
	 *
	 * @param bool $forceRegenerateStyles  Whether regenerate styles or not
	 */
	public function saveCss( $forceRegenerateStyles = false ){
		$this->styleGenerator( ['forceRegenerateStyles' => $forceRegenerateStyles] )->saveCss( $forceRegenerateStyles = false );
	}

	/**
	 * Retrieves generated css
	 *
	 * @param bool $forceRegenerateStyles  Whether regenerate styles or not
	 *
	 * @return string  css styles
	 */
	public function getCss( $forceRegenerateStyles = false ){
		return $this->styleGenerator( ['forceRegenerateStyles' => $forceRegenerateStyles] )->getCss( $forceRegenerateStyles = false );
	}

	/**
	 * Retrieves before init CSS
	 *
	 * @param bool $forceRegenerateStyles  Whether regenerate before init styles or not
	 *
	 * @return string  css styles
	 */
	public function getBeforeInitCssAndTag( $forceRegenerateStyles = false ){
		return $this->styleGenerator( ['forceRegenerateStyles' => $forceRegenerateStyles] )->getBeforeInitCssAndTag( $forceRegenerateStyles = false );
	}

	/**
	 * Retrieves generated css wrapper in a style tag
	 *
	 * @param bool $forceRegenerateStyles  Whether regenerate styles or not
	 *
	 * @return string  css styles and style tag
	 */
	public function getInlineCssTag( $forceRegenerateStyles = false ){
		return $this->styleGenerator( ['forceRegenerateStyles' => $forceRegenerateStyles] )->getCssAndTag( $forceRegenerateStyles = false );
	}

	/**
	 * Retrieves custom css file of a document if exists
	 *
	 * @param bool $forceRegenerateStyles  Whether regenerate styles or not
	 *
	 * @return bool|string
	 */
	public function getCssFileUrl( $forceRegenerateStyles = false ){
		if( $forceRegenerateStyles ){
			$this->saveCss( $forceRegenerateStyles );
		}
		if( $cssFile = $this->styleGenerator()->getCssFileUrl() ){
			return $cssFile;
		}

		return false;
	}

	/**
	 * Generates all CSS classes of document wrapper tag
	 *
	 * @return string
	 */
	protected function getClassNames() {
		$classes = [ Selector::PREFIX_NAME, Selector::prefixify( Selector::DOCUMENT_PREFIX ), Selector::prefixify( 'revert' ) ];
		$classes[] = $this->getSelector();

		if ( $this->options->sectionLayout == 'fullscreen' ) {
			$classes[] = 'depicter-layout-fullscreen';
		}

		if ( $this->options->sectionLayout == 'fullwidth' ) {
			$classes[] = 'depicter-layout-fullwidth';
		}

		if ( $this->getCustomClassName() ) {
			$classes[] = $this->getCustomClassName();
		}

		if ( isset( $this->options->general->visible->default ) && $this->options->general->visible->default === false ) {
			$classes[] = 'depicter-hide-on-desktop';
		}

		if ( isset( $this->options->general->visible->tablet ) && $this->options->general->visible->tablet === false ) {
			$classes[] = 'depicter-hide-on-tablet';
		}

		if ( isset( $this->options->general->visible->mobile ) && $this->options->general->visible->mobile === false ) {
			$classes[] = 'depicter-hide-on-mobile';
		}

		if ( $this->isDisplayExtension() ){
			$classes[] = 'depicter-with-display';
		}

		return implode( ' ', $classes );
	}

	public function getCssId(){
		return Selector::getFullSelectorPath( $this->getDocumentID() );
	}

	/**
	 * Retrieves custom class name of document
	 *
	 * @return string
	 */
	protected function getCustomClassName(){
		if ( ! empty( $this->options->advanced->className ) ) {
			return $this->options->advanced->className;
		}
		return '';
	}

	/**
	 * Retrieves custom styles of document
	 *
	 * @return string
	 */
	protected function getCustomStyles(){
		if ( ! empty( $this->options->advanced->customStyle ) ) {
			$customStyles =  $this->options->advanced->customStyle;
			// replace "selector" with unique selector of document
			return str_replace('selector', '.'.$this->getStyleSelector(), $customStyles );
		}
		return '';
	}

	/**
	 * Retrieves list of all generated custom css files
	 *
	 * @param array|string $fileKeysToInclude  Array of file keys or 'all', '*' to retrieve all
	 *
	 * @return array
	 */
	public function getCustomCssFiles( $fileKeysToInclude = 'all' ){
        $documentCustomStyles = [];

        if( is_string( $fileKeysToInclude ) && in_array( $fileKeysToInclude, [ 'all', '*'] )  ){
        	$fileKeysToInclude = ['google-font', 'custom'];
        }

        if( in_array('google-font', $fileKeysToInclude) && $fontLink = $this->getFontsLink() ){
			$useGoogleFonts = \Depicter::options()->get('use_google_fonts', 'on');

			if ( $useGoogleFonts === 'save_locally' ) {
				$downloadedFonts = \Depicter::googleFontsService()->download( $fontLink );

				foreach( $downloadedFonts as $fontSlug => $fontUrl ) {
					$documentCustomStyles[ \Depicter::googleFontsService()->getCssIdForLocalFont( $this->getDocumentID(), $fontSlug ) ] =  $fontUrl;
				}
			} elseif( $useGoogleFonts === 'on' ) {
				$documentCustomStyles[ "depicter-{$this->getDocumentID()}-google-font" ] =  $fontLink;
			}
        }
        if( in_array('custom', $fileKeysToInclude) && $customCssFileUrl = $this->getCssFileUrl() ){
        	$documentCustomStyles[ "depicter--{$this->getDocumentID()}-custom" ] =  $customCssFileUrl;
        }

        return $documentCustomStyles;
    }

	/**
	 * Retrieves unique selector of document
	 *
	 * @return string
	 */
	public function getSelector(){
		return Selector::getUniqueSelector( $this->getDocumentID() );
	}

	/**
	 * Get style selector
	 *
	 * @param bool $excludePrefix Whether to exclude selector prefix or not
	 *
	 * @return string
	 */
	public function getStyleSelector( $excludePrefix = false ) {
		return ( $excludePrefix ? '' : Selector::PREFIX_CSS . "." ) . $this->getSelector();
	}

	/**
	 * Get display style selector
	 *
	 * @return string
	 */
	public function getDisplayStyleSelector() {
		return $this->getSelector() . '-display';
	}

	/**
	 * Order sections based on sectionsList
	 */
	protected function reorderSections(){
		$ordered_sections = [];
		foreach ( $this->sectionsList as $section_id ) {
			if( isset( $this->sections[ $section_id ] ) ){
				$ordered_sections[ $section_id ] = $this->sections[ $section_id ];
			}
		}
		$this->sections = $ordered_sections;
	}

	/**
	 * Assigns belonging elements of all section
	 * Here Objects can be element or section
	 */
	protected function setElementsForObjects(){
		foreach ( $this->sections as $section ){
			// Assign document ID to all sections
			$section->setDocumentID( $this->getDocumentID() );
			$this->setElementsForOneObjects( $section );
		}

		foreach ( $this->elements as $element ){
			// Assign document ID to all elements
			$element->setDocumentID( $this->getDocumentID() );
			$this->setElementsForOneObjects( $element );
		}
	}

	/**
	 * Assigns belonging elements of a section
	 *
	 * @param $object
	 */
	protected function setElementsForOneObjects( &$object ){
		if ( $elementIds = $object->getElementIds() ) {
			$elements = $this->sortElementsByDepth( $elementIds );
			$object->setElementObjects( $elements );
		}
	}

	/**
	 * Assigns belonging elements of a section
	 */
	protected function setForegroundElementObjects(){
		if ( $elementIds = $this->foregroundElements ) {
			$this->foregroundElementObjects = $this->sortElementsByDepth( $elementIds );
		}
	}

	/**
	 * Sorts elements by depth
	 *
	 * @param array $elementIds
	 *
	 * @return array|Element[]
	 */
	protected function sortElementsByDepth( $elementIds ){
		if( empty( $elementIds ) ){
			return [];
		}

		$elementsByDepth = [];
		$elements = [];

		// sort this group of elements in depth
		foreach ( $elementIds as $elementId ) {
			$element = $this->elements[ $elementId ];
			$elementsByDepth[ $element->depth ][] = $element;
		}

		// sort by ascending depth
		ksort( $elementsByDepth );

		// collect these elements by depth
		foreach ( $elementsByDepth as $elementsInADepth ){
			foreach ( $elementsInADepth as $element ){
				$elements[] = $element;
			}
		}

		return $elements;
	}

	/**
	 * @return array|object
	 */
	public function getMeta()
	{
		return $this->meta ?? [];
	}

	/**
	 * @return Options
	 */
	public function getOptions()
	{
		return $this->options ?? [];
	}

	/**
	 * Get teh name of document
	 *
	 * @return string
	 */
	public function getName()
	{
		return isset( $this->meta['name'] ) ? $this->meta['name'] : '';
	}

	/**
	 * Get document slug
	 *
	 * @return mixed|string
	 */
	public function getSlug()
	{
		return isset( $this->meta['slug'] ) ? $this->meta['slug'] : '';
	}

	/**
	 * Get document type
	 *
	 * @return string
	 */
	public function getType()
	{
		return !empty( $this->meta['documentType'] ) ? $this->meta['documentType'] : 'slider';
	}

	/**
	 * Get sections count
	 *
	 * @return int|void
	 */
	public function getSectionsCount()
	{
		return is_array( $this->sectionsList ) ? count( $this->sectionsList ) : 0;
	}

	/**
	 * Gel list of section objects
	 *
	 * @return Section[]
	 */
	public function getSections()
	{
		return $this->sections ?? [];
	}

	/**
	 * Get a section object by section ID
	 *
	 * @param string $sectionId  The section ID
	 *
	 * @return Section|null
	 */
	public function getSectionById( $sectionId )
	{
		return $this->sections[ $sectionId ] ?? null;
	}

	/**
	 * Get a section by section number. starts from 1
	 *
	 * @param int $sectionNumber  The section number
	 *
	 * @return Section|null
	 */
	public function getSectionNth( $sectionNumber )
	{
		if( ! $this->getSections() ){
			return null;
		}

		$sectionIndex = $sectionNumber > 0 ? $sectionNumber - 1 : 1;
		return array_values( $this->getSections() )[ $sectionIndex ] ?? null;
	}

	/**
	 * Get init script
	 *
	 * @return string
	 */
	public function getInitScript() {
		return (new Script())->getDocumentInitScript( $this );
	}

	/**
	 * Get init script tag
	 */
	public function getInitScriptTag() {
		return "\n" . Html::script( [], $this->getInitScript() );
	}

	/**
	 * Print init script tag
	 */
	public function printInitScriptTag() {
		echo Sanitize::html( $this->getInitScriptTag() );
	}

}
