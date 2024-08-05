<?php
namespace Depicter\Controllers\Admin;

use WPEmerge\Requests\Request;
use WPEmerge\View\ViewInterface;

class EditorController {

	/**
	 * Handle the index page.
	 *
	 * @param Request        $request
	 * @param string         $view     the view that WordPress was trying to load
	 * @return ViewInterface
	 */
	public function open( Request $request, $view ) {
		return \Depicter::view( 'admin/editor/open/content.php' )->with([]);
	}

	/**
	 * Handle the index page.
	 *
	 * @param Request        $request
	 * @param string         $view     the view that WordPress was trying to load
	 * @return ViewInterface
	 */
	public function preview( Request $request, $view ) {
		return \Depicter::view( 'admin/editor/preview.php' )->with([]);
	}

}
