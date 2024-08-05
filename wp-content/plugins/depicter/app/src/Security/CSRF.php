<?php
namespace Depicter\Security;

use WPEmerge\Csrf\Csrf as CsrfBase;


class CSRF extends CsrfBase
{
	const EDITOR_ACTION    = 'depicter-editor';
	const DASHBOARD_ACTION = 'depicter-dashboard';
	const ASSET_ACTION 	   = 'depicter-asset';
	const REPORT_ACTION    = 'depicter-report';

	/**
	 * Convenience header to check for the token.
	 *
	 * @var string
	 */
	protected $header = 'X-DEPICTER-CSRF';

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param string  $key
	 * @param integer $maximum_lifetime
	 */
	public function __construct( $key = 'depicter-csrf', $maximum_lifetime = 2 ) {
		$this->key = $key;
		$this->maximum_lifetime = $maximum_lifetime;
	}
}
