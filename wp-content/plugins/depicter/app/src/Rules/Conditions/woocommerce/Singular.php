<?php

namespace Depicter\Rules\Conditions\woocommerce;

class Singular implements \Depicter\Rules\Conditions\ConditionInterface
{

    /**
     * @inheritDoc
     */
    public function getType(): string{
        return 'wooSingle';
    }

    /**
     * @inheritDoc
     */
    public function check(string $condition): bool
    {
       if ( ! function_exists('is_product') || ! is_product() ) {
		   return false;
       }

	   /**
	    * $condition examples:
	    * product => all products
	    * product/ID
	    * product/inside/cat__slug => it means all product which are in specific category
	    * product/inside/tag__slug => it means all product which are in specific product tag
	    * product/outside/cat__slug => it means all product which are not in specific category
	    * product/outside/tag__slug => it means all product which are not in specific product tag
	    */

	   if ( !strpos( $condition, '/') ) {
		   return true;
	   }

	   $conditionParts = explode( '/', $condition );
	   if ( count( $conditionParts ) == 2 ) {
		   return is_single( $conditionParts[1] );
	   } else {
		   if ( strpos( $conditionParts[2], 'cat__' ) !== false ) {
			   $taxonomy = 'product_cat';
			   $slug = str_replace( 'cat__', '', $conditionParts[2] );
		   } else {
			   $taxonomy = 'product_tag';
			   $slug = str_replace( 'tag__', '', $conditionParts[2] );
		   }

		   $productID = get_the_ID();
		   if ( $conditionParts[1] == 'inside' ) {
			   return has_term( $slug, $taxonomy, $productID );
		   } else {
			   return ! has_term( $slug, $taxonomy, $productID );
		   }
	   }
    }
}
