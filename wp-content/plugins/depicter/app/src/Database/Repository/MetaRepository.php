<?php
namespace Depicter\Database\Repository;

use Depicter\Database\Entity\Meta;
use Exception;

class MetaRepository
{
	/**
	 * @var Meta
	 */
	private $meta;


	public function __construct(){
		$this->meta = New Meta();
	}

	/**
	 * @return Meta
	 *
	 * @throws Exception
	 */
	public function meta(): Meta{
		return new Meta();
	}

	/**
	 * Removes a meta.
	 *
	 * @param $id
	 *
	 * @return int
	 */
	public function delete( $id )
	{
		try {
			if( $meta = $this->meta()->findById( $id ) ){
				return $meta->delete();
			}
		} catch( Exception $e ) {
			error_log( $e->getMessage(), 0);
		}

		return false;

	}

    /**
	 * Retrieves default fields
	 *
	 * @return array
	 */
	public function defaultFields()
	{
		return [
			'relation' => 'document',
		];
	}

	/**
	 * Create a meta record for a relation with relation ID
	 *
	 * @param $relation_id
	 * @param $key
	 * @param $value
	 * @param $relation
	 *
	 * @return mixed
	 */
	public function add( $relation_id, $key, $value, $relation = 'document' ) {
		try {
			if ( is_array( $value ) ) {
				$value = maybe_serialize( $value );
			}
			return $this->meta()->create([
	             'relation' => $relation,
	             'relation_id' => $relation_id,
	             'meta_key' => $key,
	             'meta_value' => $value
			]);
		} catch( Exception $e ) {
			error_log( $e->getMessage(), 0);
		}

		return false;
	}

    /**
	 * Update a meta by relation, relation ID and meta key
	 *
     * @param int           $relationID   Relation ID of meta table
	 * @param string        $key          Meta key
	 * @param array|string  $value        Meta value
	 * @param string        $relation     Relation type
	 *
	 * @return mixed
	 */
	public function update( $relationID, $key, $value, $relation = 'document' ) {
		try {
			if ( is_array( $value ) ) {
				$value = maybe_serialize( $value );
			}

			$meta =  $this->meta()->where([
                  [
                      'column' => 'relation',
                      'operator' => '=',
                      'value' => $relation
                  ],
                  'AND',
                  [
                      'column' => 'relation_id',
                      'operator' => '=',
                      'value' => $relationID
                  ],
                  'AND',
                  [
                      'column' => 'meta_key',
                      'operator' => '=',
                      'value' => $key
                  ]
            ])->get();

			if ( $meta && $meta->count() ){
				return $meta->first()->update([
                    'meta_value' => $value
                ]);
			} else {
				return $this->add( $relationID, $key, $value, $relation );
			}
		} catch( Exception $e ) {
			error_log( $e->getMessage(), 0 );
		}

		return false;
	}

    /**
	 * Get meta value by relation, relation ID and meta key
     *
     * @param int     $relationID   Relation ID of meta table
	 * @param string  $key          Meta key
	 * @param bool    $default      Default value
	 * @param string  $relation     Relation type
	 *
	 * @return false|mixed
	 */
	public function get( $relationID, $key, $default = false, $relation = 'document' ) {
		try {
			$meta  = $this->meta()->where([
	              [
                      'column' => 'relation',
                      'operator' => '=',
                      'value' => $relation
                  ],
                  'AND',
                  [
                      'column' => 'relation_id',
                      'operator' => '=',
                      'value' => $relationID
                  ],
                  'AND',
                  [
                      'column' => 'meta_key',
                      'operator' => '=',
                      'value' => $key
                  ]
			])->get();

			return $meta ? maybe_unserialize( $meta->first()->toArray()['meta_value'] ) : $default;
		} catch( Exception $e ) {
			error_log( $e->getMessage(), 0);
		}

		return $default;
	}
}
