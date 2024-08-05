<?php
namespace Depicter\Services;

use Averta\WordPress\Utility\JSON;
use Depicter\GuzzleHttp\Exception\GuzzleException;

class ClientService
{
	/**
	 * ClientService constructor.
	 */
	public function __construct(){
		if ( empty( \Depicter::options()->get( 'version_initial' ) ) ) {
			\Depicter::options()->set( 'version_initial', DEPICTER_VERSION );
		}
	}

	/**
	 * Register client info
	 *
	 * @return void
	 */
	public function authorize() {

		if (
			\Depicter::cache('base')->get( 'is_client_registered' ) &&
			\Depicter::auth()->getClientKey()
		) {
			return;
		}

		$params = [
			'form_params' => [
				'version'           => DEPICTER_VERSION,
				'version_initial'   => \Depicter::options()->get('version_initial'),
				'info'              => \Depicter::options()->get('info')
			]
		];

		try{
			$response = \Depicter::remote()->post( 'v2/client/register', $params );

			$payload = JSON::decode( $response->getBody(), true );

			if ( ! empty( $payload['client_key'] ) ) {
				\Depicter::options()->set( 'client_key', $payload['client_key'] );
			} elseif ( ! empty( $payload['errors'] ) ) {
				\Depicter::options()->set('register_error_message', $payload['errors'] );
			}

			\Depicter::cache('base')->set( 'is_client_registered', true, DAY_IN_SECONDS );

		} catch ( GuzzleException|\Exception $e ) {
			\Depicter::options()->set('register_error_message', $e->getMessage() );
		}
	}

	/**
	 * Get refresh token 
	 *
	 * @param bool $force_check
	 * @return string|bool
	 */
	public function getRefreshToken( $force_check = false ) {

		$refreshToken =  \Depicter::cache('base')->get( 'refresh_token', '' );
		if ( !empty( $refreshToken ) && ! $force_check ) {
			return $refreshToken;
		}

		try{

			$params = [
				'form_params' => [
					'version'           => DEPICTER_VERSION,
					'version_initial'   => \Depicter::options()->get('version_initial'),
				]
			];

			$response = \Depicter::remote()->post( 'v2/token/refresh', $params );

			$payload = JSON::decode( $response->getBody(), true );

			if ( ! isset( $payload['errors'] ) && !empty( $payload['refreshToken'] ) ) {
				if ( ! empty( $payload['clientKey'] ) ) {
					\Depicter::options()->set( 'client_key', $payload['clientKey'] );
					\Depicter::cache('base')->set( 'is_client_registered', true, DAY_IN_SECONDS );
				}
				\Depicter::cache('base')->set( 'refresh_token',  $payload['refreshToken'], DAY_IN_SECONDS );
				return $payload['refreshToken'];
			}
		} catch ( GuzzleException|\Exception $e ) {
			\Depicter::options()->set('refresh_token_error_message', $e->getMessage() );
		}

		return false;
	}

	/**
	 * Get access token 
	 *
	 * @return string|bool
	 */
	public function getAccessToken() {

		$accessToken =  \Depicter::cache('base')->get( 'access_token', '' );
		if ( !empty( $accessToken ) ) {
			return $accessToken;
		}
		
		try{

			$response = \Depicter::remote()->post( 'v2/token/access', [
				'form_params' => [
					'refresh_token' => $this->getRefreshToken()
				]
			] );

			$payload = JSON::decode( $response->getBody(), true );

			if ( ! isset( $payload['errors'] ) && !empty( $payload['accessToken'] ) ) {
				\Depicter::cache('base')->set( 'access_token',  $payload['accessToken'], DAY_IN_SECONDS );
				return $payload['accessToken'];
			}
		} catch ( GuzzleException|\Exception $e ) {
			\Depicter::options()->set('access_token_error_message', $e->getMessage() );
		}

		return false;
	}

	/**
	 * Get id token 
	 *
	 * @return string|bool
	 */
	public function getIdToken() {

		$idToken =  \Depicter::cache('base')->get( 'id_token', '' );
		if ( !empty( $idToken ) ) {
			return $idToken;
		}
		
		try{

			$response = \Depicter::remote()->post( 'v2/token/id' );

			$payload = JSON::decode( $response->getBody(), true );

			if ( ! isset( $payload['errors'] ) && !empty( $payload['idToken'] ) ) {
				\Depicter::cache('base')->set( 'id_token',  $payload['idToken'], MONTH_IN_SECONDS );
				return $payload['idToken'];
			}
		} catch ( GuzzleException|\Exception $e ) {
			\Depicter::options()->set('id_token_error_message', $e->getMessage() );
		}

		return false;
	}

	/**
	 * Send user feedback
	 *
	 * @param  array  $bodyParams
	 *
	 * @return bool
	 * @throws GuzzleException
	 */
	public function reportIssue( $bodyParams = [] ) {
		$response = \Depicter::remote()->post( 'v1/report/issue', [
			'form_params' => $bodyParams
		]);
		return $response->getStatusCode() == 200;
	}

	/**
	 * Send user error reports
	 *
	 * @param  array  $bodyParams
	 *
	 * @return bool
	 * @throws GuzzleException
	 */
	public function reportError( $bodyParams = [] ) {
		$response = \Depicter::remote()->post( 'v1/report/error', [
			'form_params' => $bodyParams
		]);
		return $response->getStatusCode() == 200;
	}

	/**
	 * send subscriber
	 * @param  array  $bodyParams
	 *
	 * @return bool
	 * @throws GuzzleException
	 */
	public function subscribe( $bodyParams = [] ) {
		$response = \Depicter::remote()->post( 'v1/subscriber/store', [
			'form_params' => $bodyParams
		]);
		return $response->getStatusCode() == 200;
	}


	/**
	 * Validate user activation status
	 */
	public function validateActivation() {
		try {
			$response = \Depicter::remote()->post( 'v1/client/validate/activation' );
			$info = JSON::decode( $response->getBody(), true);

			if( is_null( $info['success'] ) ){
				\Depicter::options()->set('activation_error_message', '' );

			} elseif ( ! empty( $info['data'] ) ) {
				\Depicter::options()->set('subscription_status', $info['data']['status'] );
				\Depicter::options()->set('subscription_expires_at', $info['data']['expires_at'] );
				\Depicter::options()->set('user_tier', $info['data']['user_tier'] );
				\Depicter::options()->set('activation_error_message', '' );

				if ( $info['data']['status'] == 'active' ) {
					return true;
				}
			} elseif( $info['success'] == 1 ){
				return true;
			}

			if( ! empty( $info['log'] ) ) {
				\Depicter::options()->set('activation_log_message', $info['log'] );
			}

		} catch ( GuzzleException $exception ) {
			\Depicter::options()->set('activation_error_message'  , $exception->getMessage() );
			\Depicter::options()->set('connection_error_message'  , $exception->getMessage() );
		}

		return false;
	}

}
