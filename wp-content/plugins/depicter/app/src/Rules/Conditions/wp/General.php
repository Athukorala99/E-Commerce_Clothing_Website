<?php
namespace Depicter\Rules\Conditions\wp;

use Depicter\Rules\Conditions\ConditionInterface;

class General implements ConditionInterface {

    public function getType(): string{
		  return 'general';
    }

    public function check( $condition ): bool{
		  // general condition means in entire site
		  return true;
    }
}
