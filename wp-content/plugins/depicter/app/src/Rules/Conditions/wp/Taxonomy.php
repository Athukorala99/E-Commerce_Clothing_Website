<?php

namespace Depicter\Rules\Conditions\wp;

class Taxonomy implements \Depicter\Rules\Conditions\ConditionInterface
{

    /**
     * @inheritDoc
     */
    public function getType(): string{
        return 'taxonomy';
    }

    /**
     * @inheritDoc
     */
    public function check(string $condition): bool
    {
        if ( strpos( $condition,'/') ) {
			list( $taxonomy, $termID ) = explode( '/', $condition );
			return is_tax( $taxonomy, $termID );
        } else {
			return is_tax( $condition );
        }
    }
}
