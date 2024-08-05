<?php

namespace Depicter\Rules\Conditions\woocommerce;

class Tag implements \Depicter\Rules\Conditions\ConditionInterface
{

    /**
     * @inheritDoc
     */
    public function getType(): string{
        return 'wooTag';
    }

    /**
     * @inheritDoc
     */
    public function check(string $condition): bool
    {
		if ( !function_exists('is_product_tag') ) {
			return false;
		}

	    if ( strpos( $condition,'/') ) {
		    list( $taxonomy, $termSlug ) = explode( '/', $condition );
		    return is_product_tag( $termSlug );
	    } else {
		    return is_product_tag();
	    }
    }
}
