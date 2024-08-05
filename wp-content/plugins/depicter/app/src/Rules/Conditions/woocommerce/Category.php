<?php

namespace Depicter\Rules\Conditions\woocommerce;

class Category implements \Depicter\Rules\Conditions\ConditionInterface
{

    /**
     * @inheritDoc
     */
    public function getType(): string{
        return 'wooCategory';
    }

    /**
     * @inheritDoc
     */
    public function check(string $condition): bool
    {
		if ( !function_exists('is_product_category') ) {
			return false;
		}

	    if ( strpos( $condition,'/') ) {
		    list( $taxonomy, $termSlug ) = explode( '/', $condition );
		    return is_product_category( $termSlug );
	    } else {
		    return is_product_category();
	    }
    }
}
