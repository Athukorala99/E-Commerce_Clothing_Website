<?php
namespace Depicter\Rules\Conditions\woocommerce;

use Depicter\Rules\Conditions\ConditionInterface;
use function is_archive;
use function is_author;
use function is_date;
use function is_post_type_archive;
use function is_search;
use function is_tax;

class Shop implements ConditionInterface {

	/**
	 * @inheritDoc
	 */
    public function getType(): string{
		return 'shop';
    }

	/**
	 * @inerhitDoc
	 */
    public function check( $condition ): bool{
		return function_exists('is_shop') && is_shop();
    }
}
