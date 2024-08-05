<?php
namespace Averta\Core\Hydrate;

interface ExtractionInterface
{
    /**
	 * Extract values for this class
	 *
	 * @return array
	 */
    public function extract();
}
