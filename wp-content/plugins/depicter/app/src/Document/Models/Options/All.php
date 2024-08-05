<?php
namespace Depicter\Document\Models\Options;


use Averta\Core\Utility\Arr;

class All
{
	/**
	 * @var string
	 */
	public $sectionLayout;

	/**
	 * @var object
	 */
	public $sectionTransition;

	/**
	 * @var \Depicter\Document\Models\Common\Size\States
	 */
	public $wrapperSize;

	/**
	 * @var int
	 */
	public $wrapperSideSpace = 0;

	/**
	 * @var object|null
	 */
	public $documentTypeOptions;

	/**
	 * @var General
	 */
	public $general;

	/**
	 * @var Navigation
	 */
	public $navigation;

	/**
	 * @var object|null
	 */
	public $navigator;

	/**
	 * @var Animation
	 */
	public $slidingAnimation;

	/**
	 * @var Loading|null
	 */
	public $loading;

	/**
	 * @var Advanced
	 */
	public $advanced;

	/**
	 * @var Callback[]
	 */
	public $callbacks;

	/**
	 * @var Callback[]
	 */
	public $controls;

	/**
	 * List of option styles
	 *
	 * @var array
	 */
	protected $stylesList = [];


	/**
	 * Get document size
	 *
	 * @param      $sizeProp
	 *
	 * @param bool $includeUnit
	 *
	 * @return string
	 */
	public function getSize( $sizeProp, $includeUnit = false ) {
		return implode( ',', $this->wrapperSize->getResponsiveSizes( $sizeProp, $includeUnit ) );
	}

	/**
	 * Get document size
	 *
	 * @param      $sizeProp
	 * @param bool $includeUnit
	 *
	 * @return array
	 */
	public function getSizes( $sizeProp, $includeUnit = false ) {
		return $this->wrapperSize->getResponsiveSizes( $sizeProp, $includeUnit );
	}

	/**
	 * Get document layout
	 *
	 * @return string
	 */
	public function getLayout(){
		return isset( $this->sectionLayout ) ? $this->sectionLayout : 'fullwidth';
	}

	public function getStyles(){
		$this->stylesList = [];

		// Collect styles for general options
		if( $this->general ){
			$this->general->setAllOptions( $this );
			$this->stylesList = Arr::merge( $this->general->getStylesList(), $this->stylesList );
		}

		return $this->stylesList;
	}

	/**
	 * Get before init document styles
	 *
	 * @return array
	 */
	public function getBeforeInitStyles(){
		$this->general = $this->general ?? new General();
		$this->general->setAllOptions( $this );

		return $this->general->getBeforeInitStyles();
	}

	/**
	 * get styles for layers wrapper
	 *
	 * @return array
	 */
	public function getLayersWrapperStyles(){
		$styles = [];
		if( ! empty( $this->wrapperSideSpace ) ){
			$styles = [ 'default' => [ 'padding-left' => $this->wrapperSideSpace.'px', 'padding-right' => $this->wrapperSideSpace.'px' ] ];
		}

		return $styles;
	}

	/**
	 * Get general section styles
	 *
	 * @return array
	 */
	public function getSectionGeneralStyles(){
		$this->general = $this->general ?? new General();
		$this->general->setAllOptions( $this );

		return $this->general->getMinHeightStyles();
	}

	/**
	 * Generate backdrop styles for display extensions
	 *
	 * @return array|array[]
	 */
	public function getBackdropStyles(){
		if ( empty( $this->documentTypeOptions ) || empty( $this->documentTypeOptions->displayOptions ) ) {
			return [];
		}

		$displayOptions = $this->documentTypeOptions->displayOptions;
		if ( empty( $displayOptions->backdrop ) ) {
			return [];
		}

		$styles = [
			'default' => []
		];

		if ( ! empty( $displayOptions->backdropColor ) ) {
			$styles['default']['background-color'] = $displayOptions->backdropColor;
		}

		if ( ! empty( $displayOptions->backdropBlur ) ) {
			$styles['default']['backdrop-filter'] = 'blur( ' . $displayOptions->backdropBlur . 'px )';
		}

		return $styles;
	}

	/**
	 * get callbacks
	 * @param string $sliderName
	 *
	 * @return string
	 */
	public function getCallbacks( $sliderName = '' ) {
		$script = '';
		if ( !empty( $this->callbacks ) ) {
			foreach ( $this->callbacks as $callback ) {
				$callback->value = !empty( $sliderName ) ? str_replace( 'depicter.on', $sliderName . '.on', $callback->value ) : $callback->value;
				$script .= "\n\t$callback->value";
			}
			$script .= "\n";
		}

		return $script;
	}
}
