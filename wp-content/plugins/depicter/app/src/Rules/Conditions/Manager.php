<?php
namespace Depicter\Rules\Conditions;

class Manager {

	/**
	 * Get a condition instance
	 *
	 * @param string $condition
	 *
	 * @return false|mixed|null
	 */
	public function getConditionInstance( string $condition ) {

		return \Depicter::resolve('depicter.conditions.' . $condition );
	}

	/**
	 * Get a condition instance
	 *
	 * @param ListConditions $condition
	 *
	 * @return false|mixed|null
	 */
	public function listConditions():ListConditions {

		return \Depicter::resolve('depicter.conditions.list');
	}

	/**
	 * Get query values
	 * @param $query
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getConditionOptions( $query ) {
		$queryParts = explode( ':', $query );
		switch( $queryParts[0] ) {
			case 'wp':
				return \Depicter::resolve( 'depicter.conditions.wp.query.values' )->get( $query );
				break;
			case 'wc':
				return \Depicter::resolve( 'depicter.conditions.wc.query.values' )->get( $query );
			default:
				throw new \Exception( __( 'Error while fetching dynamic items', 'depicter' ) );
				break;
		}
	}

	/**
	 * Get documents for a specific document type
	 *
	 * @param $type
	 *
	 * @return array
	 */
    public function getDocumentsWithCondition( $type ): array{
        $builderConditions = \Depicter::options()->get( 'builder_conditions', [] );
		return $builderConditions[ $type ] ?? [];
    }


	/**
	 * Get document conditions
	 *
	 * @param $documentID
	 *
	 * @return false|mixed
	 */
    public function getDocumentConditions( $documentID ) {
        return \Depicter::metaRepository()->get( $documentID, 'conditions', '');
    }

	/**
	 * Set conditions for document
	 *
	 * @param int    $documentID
	 * @param array  $conditions
	 * @param string $documentType
	 *
	 * @return void
	 */
	public function setDocumentConditions( int $documentID, array $conditions, string $documentType ) {
		\Depicter::metaRepository()->update( $documentID, 'conditions', $conditions );
		$builderConditions = \Depicter::options()->get( 'builder_conditions', []);
		if ( ! empty( $conditions ) ) {
			$builderConditions[ $documentType ][ $documentID ] = $conditions;
		} else {
			if ( isset( $builderConditions[ $documentType ][ $documentID ] ) ) {
				unset( $builderConditions[ $documentType ][ $documentID ] );
			}
		}

		\Depicter::options()->set( 'builder_conditions', $builderConditions );
	}

	/**
	 * Check if rendering is verified for document based on its condition or not
	 *
	 * @param int   $documentID
	 * @param array $groups
	 *
	 * @return bool
	 */
	public function canRender( int $documentID, array $groups = [] ): bool{
		if ( empty( $groups ) ) {
			$groups = \Depicter::metaRepository()->get( $documentID, 'condition', [] );
			if ( empty( $groups ) ) {
				return false;
			}
		}

		// sorting the groups based on the order user selected for groups
		usort($groups, function($a, $b) {
			return $a['order'] <=> $b['order'];
		});


		$i = 0;
		$canRender = false;
		$operators = [];
		foreach( $groups as $key => $group ) {
			$operators[] = $group['operator'];
			$allConditions = [];

			// sorting the conditions based on the order user selected for conditions in this group
			usort($group['conditions'], function($a, $b) {
				return $a['order'] <=> $b['order'];
			});
			foreach( $group['conditions'] as $conditionID => $condition ) {
				$conditionInstance = $this->getConditionInstance( $condition['type'] );

				if ( $conditionInstance->check( $condition['options'] ) ) {

					if ( $condition['selectionMode'] == 'exclude' ) {
						$allConditions[] = false;
					} else {
						$allConditions[] = true;
					}

					if ( $group['matchingMode'] == 'any' ) {
						if ( $condition['selectionMode'] == 'exclude' ) {
							$groupCanRenderDocument = false;
						} else {
							$groupCanRenderDocument = true;
						}
						break;
					}
				}
			}

			// $allConditions can only have value of false or true so that we can find out if document can render based of each condition or not
			$allConditions = array_unique( $allConditions );
			if ( $group['matchingMode'] == 'all'  ) {
				if ( empty( $allConditions ) ) {
					// if is empty it means that none of the conditions meet in this group so that based of this group we could not render the document
					$groupCanRenderDocument = false;
				} else if ( count( $allConditions ) > 1 ) {
					// if count of all conditions is more than 1, it means that all conditions have false and true inside, so that base of some conditions we can render the document and based of some other we can not, so this group could not render the document
					$groupCanRenderDocument = false;
				} else {
					// if there in only one child in all conditions array, that child determines that if this group can render the document or not
					$groupCanRenderDocument = $allConditions[0];
				}
			}

			// check relation between groups here
			if ( $i == 0 ) {
				// the first group
				if ( $group['operator'] == 'or' && $groupCanRenderDocument ) {
					$canRender = true;
					break;
				}
			} else {
				if ( $operators[ $i - 1 ] == 'and' ) {
					$canRender  = $canRender && $groupCanRenderDocument;
				} else {
					$canRender  = $canRender || $groupCanRenderDocument;
				}

				// check operator between this group and next one, if its OR and canRender is true till here then break the loop
				if ( $group['operator'] == 'or' && $canRender ) {
					break;
				}
			}

			++$i;
		}

		return $canRender;
	}

}
