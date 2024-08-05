<?php

namespace Depicter\Document\Models\Traits;

trait EntityPropertiesTrait {

	/**
	 * Document entity params
	 *
	 * @var array
	 */
	private $entityProperties = [];


		/**
	 * Sets entity properties
	 *
	 * @param array $properties
	 *
	 * @return void
	 */
	public function setEntityProperties( $properties = [] )
	{
		$this->entityProperties = $properties;
	}

	/**
	 * Retrieves entity properties
	 *
	 * @return array
	 */
	public function getEntityProperties()
	{
		return $this->entityProperties;
	}

	/**
	 * Retrieves value of an entity property
	 *
	 * @return string
	 */
	public function getEntityProperty( $propertyName = '' )
	{
		return $this->entityProperties[ $propertyName ] ?? '';
	}

	/**
	 * Sets an entity property
	 *
	 * @return string
	 */
	public function setEntityProperty( $propertyName = '', $propertyValue = '' )
	{
		return $this->entityProperties[ $propertyName ] = $propertyValue;
	}

}
