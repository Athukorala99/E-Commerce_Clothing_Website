<?php
namespace Depicter\DataSources;

use Averta\Core\Utility\Arr;

class DataSourceBase
{
	/**
	 * DataSource name
	 *
	 * @var string
	 */
	protected $type = 'base';

	/**
	 * DataSource properties
	 *
	 * @var array
	 */
	protected $properties = [];

	/**
	 * Default input params for retrieving dataSource records
	 *
	 * @var array
	 */
	protected $defaultInputParams = [];

	/**
	 * Asset groups of this DataSource
	 *
	 * @var array
	 */
	protected $assetGroupNames = [];


	/**
	 * Prepares arguments for retrieving dataSource records
	 *
	 * @param array|object $args
	 *
	 * @return array
	 */
	protected function prepare( $args ){
		$defaultParams = Arr::merge( $this->defaultInputParams, $this->getProperties() );
		return Arr::merge( $args, $defaultParams );
	}

	/**
	 * Get a property value
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function getProperty( string $name ){
		return $this->properties[ $name ] ?? null;
	}

	/**
	 * Get all dataSource properties
	 *
	 * @return array|string[]
	 */
	public function getProperties(){
		return $this->properties;
	}

	/**
	 * Get asset groups of this DataSource
	 *
	 * @return array
	 */
	public function getAssetGroupNames(){
		return $this->assetGroupNames;
	}


}
