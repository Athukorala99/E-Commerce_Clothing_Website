<?php
namespace Depicter\Rules\Conditions;

interface ConditionInterface {

    /**
	 * Get Condition Type
	 *
	 *
	 * @return string
	 */
    public function getType();

	/**
	 * Check if condition passed or not
	 *
	 * @param string $condition
	 * @return bool
	 */
	public function check( string $condition ): bool;

}
