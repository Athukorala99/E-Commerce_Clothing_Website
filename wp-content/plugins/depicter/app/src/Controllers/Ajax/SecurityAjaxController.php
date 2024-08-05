<?php
namespace Depicter\Controllers\Ajax;

use Depicter\Utility\Sanitize;
use WPEmerge\Requests\RequestInterface;

class SecurityAjaxController
{

	public function generateCsrfToken( RequestInterface $request, $view ) {
		$action = Sanitize::key( $request->body('key') );
		$action = empty( $action ) ? -1 : $action;

		return \Depicter::csrf()->getToken( $action );
	}
}
