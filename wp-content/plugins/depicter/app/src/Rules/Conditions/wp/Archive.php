<?php
namespace Depicter\Rules\Conditions\wp;

use Depicter\Rules\Conditions\ConditionInterface;

class Archive implements ConditionInterface {

    public function getType(): string{
		return 'archive';
    }

    public function check( $condition ): bool{

		if ( !is_archive() ) {
			return false;
		}

		if ( false === strpos( $condition, '/') ) {
			// it could be 'date', 'all', $postType . '_archive' or is $tax;

			switch( $condition ) {
				case 'all':
					$isArchive = true;
					break;
				case 'date':
					$isArchive = is_date();
					break;
				case 'search':
					$isArchive = is_search();
					break;
				default:
					if ( strpos( $condition, '_archive' ) ) {
						$postType = str_replace( '_archive', '', $condition );
						$isArchive = is_post_type_archive( $postType );
					} else {
						$isArchive = is_tax( $condition );
					}
			}

			return $isArchive;

		}

		// the condition format is like archiveType/$id
	    $archive = explode( '/', $condition );
		switch( $archive[0] ) {
			case 'author':
				$isArchive = is_author( $archive[1] );
				break;
			default:
				// tax/termID
				$isArchive = is_tax( $archive[0], $archive[1] );
				break;
		}

		return $isArchive;

    }
}
