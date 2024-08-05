<?php
namespace Depicter\Rules\Conditions\wp;

use Depicter\Rules\Conditions\ConditionInterface;

class Singular implements ConditionInterface {

    public function getType(): string{
		return 'singular';
    }

    public function check( $condition ): bool{
		if ( ! is_single() || ! is_page() ) {
			return false;
		}

		if ( strpos( $condition, '/') === false ) {
			switch( $condition ) {
				case 'page':
					$isSingular = is_page();
					break;
				case 'frontPage':
					$isSingular = is_front_page();
					break;
				default:
					// all
					$isSingular = true;
					break;
			}

			return $isSingular;
		}

	    // the condition format is like singularPost/$id
	    $singular = explode( '/', $condition );
	    switch( $singular[0] ) {
		    case 'page':
			    $isSingular = is_page( $singular[1] );
			    break;
		    default:
			    // singlePost/id
			    $isSingular = is_single( $singular[1] );
			    break;
	    }

	    return $isSingular;
    }
}
