<?php

namespace Depicter\Document\Models\Traits;

trait HasDataSheetTrait {

	/**
	 * Current dataSource record (dataSheet)
	 * Only available if dataSource is assigned to the section
	 *
	 * @var array|null
	 */
	protected $dataSheet = [];


	/**
	 * Retrieves the dataSource record (dataSheet)
	 *
	 * @return array|null
	 */
	public function getDataSheet() {
		return $this->dataSheet;
	}

	/**
	 * Whether dataSheet is attached for this class or not
	 *
	 * @return bool
	 */
	public function hasDataSheet() {
		return !empty( $this->dataSheet );
	}

	/**
	 * Sets dataSheet
	 *
	 * @param array $dataSheet
	 */
	public function setDataSheet( $dataSheet ) {
		$this->dataSheet = $dataSheet;
	}

	/**
	 * Retrieves the current dataSheet ID if dataSource exits
	 *
	 * @return string
	 */
	public function getDataSheetID() {
		if( $dataSheet = $this->getDataSheet() ){
			if( !empty( $dataSheet['id'] ) ){
				return $dataSheet['id'];
			} elseif( !empty( $dataSheet['uuid'] ) ){
				return $dataSheet['uuid'];
			}
		}
		return $this->maybeReplaceDataSheetTags('{{{uuid}}}', '' );
	}

	/**
	 * Retrieves data tag value if exists
	 *
	 * @param $tag
	 *
	 * @return mixed|string
	 */
	public function getDataSheetTagValue( $tag ) {
		return $this->hasDataSheet() ? \Depicter::dataSource()->tagsManager()->convert( $tag, $this->getDataSheet() ) : '';
	}

	/**
	 * Retrieves url of this dataSheet
	 *
	 * @return string
	 */
	public function getDataSheetUrl() {
		if( $dataSheet = $this->getDataSheet() ){
			if( !empty( $dataSheet['url'] ) ){
				return $dataSheet['url'];
			}
		}
		return $this->maybeReplaceDataSheetTags('{{{url}}}', '');
	}

	/**
	 * Whether section is linked to corresponding dataSheet or not
	 *
	 * @return false
	 */
	public function isLinkedToDataSheet(){
		return $this->maybeReplaceDataSheetTags('{{{linkSlides}}}', false);
	}

	/**
	 * Replace dataSheets tags if exits
	 *
	 * @param $variable
	 * @param $default
	 *
	 * @return array|mixed|string|string[]
	 */
	protected function maybeReplaceDataSheetTags( $variable, $default = null, $args = [] ){
		if( $dataSheet = $this->getDataSheet() ){
			$dataSheet = !empty( $args ) ? array_merge( $dataSheet, $args ) : $dataSheet;
			return \Depicter::dataSource()->tagsManager()->convert( $variable, $dataSheet );
		}
		if( ! is_null( $default ) ){
			return $default;
		}
		return $variable;
	}
}
