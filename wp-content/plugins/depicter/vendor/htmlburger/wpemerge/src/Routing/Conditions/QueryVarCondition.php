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
 * Check against a query var value.
 *
 * @codeCoverageIgnore
 */
class QueryVarCondition implements ConditionInterface {
	/**
	 * Query var name to check against.
	 *
	 * @var string|null
	 */
	protected $query_var = null;

	/**
	 * Query var value to check against.
	 *
	 * @var string|null
	 */
	protected $value = '';

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param string      $query_var
	 * @param string|null $value
	 */
	public function __construct( $query_var, $value = null ) {
		$this->query_var = $query_var;
		$this->value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSatisfied( RequestInterface $request ) {
		$query_var_value = get_query_var( $this->query_var, null );

		if ( $query_var_value === null ) {
			return false;
		}

		if ( $this->value === null ) {
			return true;
		}

		return (string) $this->value === $query_var_value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments( RequestInterface $request ) {
		return ['query_var' => $this->query_var, 'value' => $this->value];
	}
}
