<?php
namespace Depicter\Services;


use Averta\Core\Utility\Arr;
use Averta\WordPress\Utility\JSON;
use Depicter\GuzzleHttp\Client;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Performs an HTTP request and returns its response.
 *
 * @package Depicter\Services
 */
class RemoteAPIService
{
	/**
	 * List of endpoints
	 *
	 * @var array
	 */
	protected $endpoints = [
		"1" => "https://wp-api.depicter.com/",
		"2" => "https://pre.wp-api.depicter.com/",
		"4" => "https://aid.wp-api.depicter.com/",
		"5" => "https://api.my.depicter.com/mx/"
	];


	/**
	 * Retrieves an endpoint by number
	 *
	 * @param int    $endpointNumber
	 *
	 * @param string $branch  The relative path to be appended to endpoint url
	 *
	 * @return mixed|string
	 */
	public function endpoint( $endpointNumber = 1, $branch = '' ){
		if( ! empty( $this->endpoints[ $endpointNumber ] ) ){
			return $this->endpoints[ $endpointNumber ] . $branch;
		}
		return '';
	}


	private function isAbsoluteUrl( $url ){
		return strpos($url,'://') !== false;
	}

	/**
	 * Get default options for requests
	 *
	 * @return array
	 * @throws GuzzleException
	 */
	private function getDefaultOptions() {
		global $wp_version;

		$token = \Depicter::cache('base')->get( 'access_token', '' );

		return [
			'headers' => [
				'user-agent'      => 'WordPress/'. $wp_version .'; '. get_home_url(),
				'timeout'         => 30,
				'X-DEPICTER-CKEY' => \Depicter::auth()->getClientKey(),
				'X-DEPICTER-VER'  => DEPICTER_VERSION,
				'X-DEPICTER-TIER' => \Depicter::auth()->getTier(),
				'Authorization'   => 'Bearer ' . $token
			]
		];
	}

	/**
	 * Create and send an HTTP GET request to specified API endpoints.
	 *
	 * @param string $endpoint URI object or string.
	 * @param array  $options  Request options to apply.
	 * @param int    $endpointNumber  Endpoint number
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function get( $endpoint, $options = [], $endpointNumber = 1 )
	{
		// Maybe convert branch to absolute endpoint url
		if( ! $this->isAbsoluteUrl( $endpoint ) ){
			$endpoint = $this->endpoint( $endpointNumber, $endpoint );
		}

		$client = new Client([
			'verify' => ABSPATH . WPINC . '/certificates/ca-bundle.crt'
		]);

		$optionsWithAuth = Arr::merge( $options, $this->getDefaultOptions() );
		$response = $client->get( $endpoint, $optionsWithAuth );

		// try to get refresh token on failure
		if ( $response->getStatusCode() == 401 ) {
			\Depicter::cache('base')->delete('access_token');
			$optionsWithAuth = Arr::merge( $options, $this->getDefaultOptions() ); // refresh token
			$response = $client->get( $endpoint, $optionsWithAuth );
		}

		return $response;
	}

	/**
	 * Create and send an HTTP POST request to specified API endpoints.
	 *
	 * @param string $endpoint URI object or string.
	 * @param array  $options  Request options to apply.
	 * @param int    $endpointNumber  Endpoint number
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function post( $endpoint, $options = [], $endpointNumber = 1 )
	{
		// Maybe convert branch to absolute endpoint url
		if( ! $this->isAbsoluteUrl( $endpoint ) ){
			$endpoint = $this->endpoint( $endpointNumber, $endpoint );
		}

		$client = new Client([
			'verify' => ABSPATH . WPINC . '/certificates/ca-bundle.crt'
		]);

		$optionsWithAuth = Arr::merge( $options, $this->getDefaultOptions() );
		$response = $client->post( $endpoint, $optionsWithAuth );

		// try to get refresh token on failure
		if ( $response->getStatusCode() == 401 ) {
			\Depicter::cache('base')->delete('access_token');
			$optionsWithAuth = Arr::merge( $options, $this->getDefaultOptions() ); // refresh token
			$response = $client->post( $endpoint, $optionsWithAuth );
		}

		return $response;
	}

	/**
	 * Whether remote api address are accessible or not
	 *
	 * @return bool
	 */
	public static function isAccessible()
	{
		return true;
	}

	/**
	 * Get auth token from server
	 *
	 * @return mixed|string
	 * @throws GuzzleException
	 */
	public function getAuthToken() {
		$client = new Client();
		$response = $client->post( $this->endpoint(), $this->getDefaultOptions() );

		$body = JSON::decode( $response->getBody(), true );

		if ( !empty( $body['success']) ) {
			return $body['success'];
		}
		return '';
	}
}
