<?php
namespace Averta\Http\Rest;


class Methods
{
	/**
	 * Alias for GET transport method.
	 *
	 * @var string
	 */
	const READABLE = 'GET';

	/**
	 * Alias for POST transport method.
	 *
	 * @var string
	 */
	const CREATABLE = 'POST';

	/**
	 * Alias for POST, PUT, PATCH transport methods together.
	 *
	 * @var string
	 */
	const EDITABLE = 'POST, PUT, PATCH';

	/**
	 * Alias for DELETE transport method.
	 *
	 * @var string
	 */
	const DELETABLE = 'DELETE';

	/**
	 * Alias for GET, POST, PUT, PATCH & DELETE transport methods together.
	 *
	 * @var string
	 */
	const ANY = 'GET, POST, PUT, PATCH, DELETE';
}
