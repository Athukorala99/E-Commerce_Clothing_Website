<?php
namespace Depicter\Document\Models\Traits;


trait HasDocumentIdTrait
{
	/**
	 * @var int|null
	 */
	protected $documentID;

	/**
	 * Gets document ID
	 *
	 * @return int|null
	 */
	public function getDocumentID() {
		return $this->documentID;
	}

	/**
	 * Sets document ID
	 *
	 * @param int $documentID
	 *
	 * @return mixed
	 */
	public function setDocumentID( $documentID = 1 ) {
		if( $documentID ){
			$this->documentID = $documentID;
		}
		return $this;
	}
}
