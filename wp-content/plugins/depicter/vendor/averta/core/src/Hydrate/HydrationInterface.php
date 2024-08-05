<?php
namespace Averta\Core\Hydrate;

interface HydrationInterface
{
    /**
	 * Hydrate the class with the provided $data.
	 *
	 * @param  array|object $data
     */
    public function hydrate( $data );
}
