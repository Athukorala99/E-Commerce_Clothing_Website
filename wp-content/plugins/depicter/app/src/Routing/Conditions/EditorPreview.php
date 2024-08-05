<?php
namespace Depicter\Routing\Conditions;

use WPEmerge\Requests\RequestInterface;
use WPEmerge\Routing\Conditions\ConditionInterface;

class EditorPreview implements ConditionInterface {
	public function isSatisfied( RequestInterface $request ) {
		return true;
	}

	public function getArguments( RequestInterface $request ) {
         return [];
	}
 }
