<?php
namespace Depicter\WordPress;

class SystemCheckService {

	/**
	 * SystemCheckService constructor.
	 */
	public function __construct(){
		add_filter( 'site_status_tests', array( $this, 'system_status_check' ) );
	}

	/**
	 * Add extra checking for system health
	 *
	 * @param $tests
	 *
	 * @return mixed
	 */
	public function system_status_check( $tests ) {

		$tests['direct']['depicter_curl_check'] = array(
			'label' => __( 'Check to access depicter resources servers.', 'depicter' ),
			'test'  => array( $this, 'check_server_connection' )
		);

		return $tests;
	}

	/**
	 * @throws \Depicter\GuzzleHttp\Exception\GuzzleException
	 */
	public function check_server_connection() {
		$result = array(
			'label'       => __( 'Connecting to depicter resources servers passed successfully', 'depicter' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance' ),
				'color' => 'blue',
			),
			'actions'     => '',
			'test'        => 'check_server_connection',
			'description' => __( 'Connecting to depicter resources servers passed successfully', 'depicter' )
		);

		try {
			$response = \Depicter::remote()->get( \Depicter::remote()->endpoint() . 'v1/core/version-check/latest' );

			if ( $response->getStatusCode() != 200 ) {
				$result['status'] = 'critical';

				$result['description'] = $this->getErrorOuput();
				$result['label'] = __( 'Error while trying to connect to depicter resources servers', 'depicter' );
			}
		} catch( \Exception $exception ) {
			$result['status'] = 'critical';

			$result['description'] = $this->getErrorOuput();
			$result['label'] = __( 'Error while trying to connect to depicter resources servers', 'depicter' );
		}
		return $result;
	}

	/**
	 * Get error output
	 *
	 * @return string
	 */
	public function getErrorOuput() {
		$screen_reader = __( 'Error', 'depicter' );
		$message       = __( 'Your site cannot communicate securely with depicter services. Contact your host provider and ask them to whitelist <code>depicter.com</code> to gain access to assets library', 'depicter' );
		$message       = "<span class='dashicons error'><span class='screen-reader-text'>$screen_reader</span></span> $message";

		$output = '<ul>';
		$output .= sprintf(
			'<li>%s</li>',
			$message
		);
		$output .= '</ul>';

		return $output;
	}
}
