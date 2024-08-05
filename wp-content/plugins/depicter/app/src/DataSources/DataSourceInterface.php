<?php

namespace Depicter\DataSources;

interface DataSourceInterface {

	/**
	 * Renders preview for query params
	 *
	 * @param array $args
	 *
	 * @return array
	 */
    public function previewRecords( array $args = [] );

	/**
	 * Get list of asset groups for this dataSource
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function getAssets( array $args );
}
