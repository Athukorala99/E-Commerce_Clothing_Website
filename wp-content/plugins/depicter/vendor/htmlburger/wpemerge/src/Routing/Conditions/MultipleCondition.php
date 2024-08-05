<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Routing\Conditions;

use WPEmerge\Requests\RequestInterface;

/**
 * Check against an array of conditions in an AND logical relationship.
 */
class MultipleCondition implements ConditionInterface {
	/**
	 * Array of conditions to check.
	 *
	 * @var ConditionInterface[]
	 */
	protected $conditions = [];

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param ConditionInterface[] $conditions
	 */
	public function __construct( $conditions ) {
		$this->conditions = $conditions;
	}

	/**
	 * Get all assigned conditions
	 *
	 * @codeCoverageIgnore
	 * @return ConditionInterface[]
	 */
	public function getConditions() {
		return $this->conditions;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSatisfied( RequestInterface $request ) {
		foreach ( $this->conditions as $condition ) {
			if ( ! $condition->isSatisfied( $request ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments( RequestInterface $request ) {
		$arguments = [];

		foreach ( $this->conditions as $condition ) {
			$arguments = array_merge( $arguments, $condition->getArguments( $request ) );
		}

		return $arguments;
	}
}
