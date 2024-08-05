<?php
namespace Depicter\Controllers\Admin;

use WPEmerge\Requests\Request;
use WPEmerge\View\ViewInterface;

class DashboardController {
	/**
	 * Handle the index page.
	 *
	 * @param Request        $request
	 * @param string         $view     the view that WordPress was trying to load
	 * @return ViewInterface
	 */
	public function index( Request $request, $view ) {
		return \Depicter::view( 'admin/dashboard/index.php' )->with([]);
	}
}
