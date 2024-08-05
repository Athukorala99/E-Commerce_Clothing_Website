<?php
namespace Depicter\DataSources;


use Averta\Core\Utility\Arr;
use Averta\WordPress\Utility\JSON;

/**
 * DataSource which holds pre-defined content for replacement in document
 */
class Catalogs extends DataSourceBase implements DataSourceInterface
{
	/**
	 * DataSource name
	 *
	 * @var string
	 */
	protected $type = 'catalogs';

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
	protected $assetGroupNames = [ 'catalog' ];

	/**
	 * Retrieves the list of records based on query params
	 *
	 * @param $args
	 *
	 * @return array
	 */
	protected function getRecords( $args ){
		return $args['catalogs'] ?? [];
	}

	public function previewRecords( array $args = [] ) {
		$args = $this->prepare( $args );
		return $this->getRecords( $args );
    }

	/**
	 * Get list of datasheets and corresponding required arguments
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function getDataSheetArgs( array $args = [] ){
		// convert array of objects to associate array
		$sheets = $this->getRecords( $args );
		return JSON::decode( JSON::encode( $sheets ), true );
	}

	/**
	 * Get list of asset groups for this dataSource
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function getAssets( $args ){
		$assetGroupNames = $this->getAssetGroupNames();

		$groups = [];
		foreach( $assetGroupNames as $assetGroupName ){
			$groups[ $assetGroupName ] = $args;
		}

		return \Depicter::dataSource()->tagsManager()->getAssetsInGroups( $groups );
	}

}
