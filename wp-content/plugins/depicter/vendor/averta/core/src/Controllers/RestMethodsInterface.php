<?php
namespace Averta\Core\Controllers;

use WPEmerge\Requests\RequestInterface;
use WPEmerge\View\ViewInterface;

interface RestMethodsInterface
{
	/**
	 * Retrieves Lists of all entries. (GET)
	 *
	 * @param RequestInterface $request
	 * @param string  $view
	 *
	 * @return array|ViewInterface
	 */
	public function index( RequestInterface $request, $view );

	/**
	 * Adds a new entry. (POST)
	 *
	 * @param RequestInterface $request
	 * @param string  $view
	 *
	 * @return array
	 */
	public function store( RequestInterface $request, $view );

	/**
	 * Displays an entry. (GET)
	 *
	 * @param RequestInterface $request
	 * @param string  $view
	 *
	 * @return array|ViewInterface
	 */
	public function show( RequestInterface $request, $view );

	/**
	 * Updates an entry. (PUT/PATCH)
	 *
	 * @param RequestInterface $request
	 * @param string  $view
	 *
	 * @return array|ViewInterface
	 */
	public function update( RequestInterface $request, $view );

	/**
	 * Removes an entry. (DELETE)
	 *
	 * @param RequestInterface $request
	 * @param string  $view
	 *
	 * @return array|ViewInterface
	 */
	public function destroy( RequestInterface $request, $view );
}
