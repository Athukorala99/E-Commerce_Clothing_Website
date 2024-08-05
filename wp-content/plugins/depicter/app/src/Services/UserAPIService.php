<?php
namespace Depicter\Services;

use Averta\WordPress\Utility\JSON;
use Depicter\GuzzleHttp\Exception\GuzzleException;

class UserAPIService {

	/**
	 * User Login
	 *
	 * @var string $email
	 * @var string $password
	 *
	 * @throws \Depicter\GuzzleHttp\Exception\GuzzleException
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public static function login( $email, $password ) {
		$response = \Depicter::remote()->post( 'v1/remote/user/login', [
			'form_params' => [
				'email' => $email,
				'password' => $password
			]
		]);

		$response = JSON::decode( $response->getBody(), true );
		if ( !empty( $response['errors'] ) ) {
			throw new \Exception( $response['errors'][0] );
		}

		\Depicter::cache('base')->set( 'access_token', $response['accessToken'], DAY_IN_SECONDS );
		\Depicter::cache('base')->set( 'id_token', $response['idToken'], MONTH_IN_SECONDS );

		return $response;
    }

	/**
	 * User Logout
	 *
	 * @return bool
	 */
	public static function logout() {
		return \Depicter::cache('base')->delete( 'id_token' );
	}

	/**
	 * User Register
	 *
	 * @param $email
	 * @param $password
	 * @param $fields
	 *
	 * @return mixed
	 *
	 * @throws GuzzleException
	 * @throws \Exception
	 */
	public static function register( $email, $password, $fields = [] ) {

		$options = array_merge( [ 'email' => $email, 'password' => $password ], $fields );
		$response = \Depicter::remote()->post( 'v1/remote/user/register', [ 'form_params' => $options ] );

		$response = JSON::decode( $response->getBody(), true );

		if ( !empty( $response['errors'] ) ) {
			throw new \Exception( $response['errors'][0] );
		}

		\Depicter::cache('base')->set( 'access_token', $response['accessToken'], DAY_IN_SECONDS );
		\Depicter::cache('base')->set( 'id_token', $response['idToken'], MONTH_IN_SECONDS );

		return $response;
	}

	/**
	 * Renew access and refresh tokens
	 *
	 * @return void
	 */
	public static function renewTokens() {
		\Depicter::client()->getAccessToken();
	}

	/**
	 * Get google client id
	 *
	 * @return mixed
	 * @throws GuzzleException
	 * @throws \Exception
	 */
	public static function googleClientID() {

		if ( false !== $response = \Depicter::cache('base')->get( 'googleClientId') ) {
			return $response;
		}

		$response = \Depicter::remote()->get( 'v1/remote/auth/google/id' );
		$response = JSON::decode( $response->getBody(), true );

		if ( !empty( $response['errors'] ) ) {
			throw new \Exception( $response['errors'][0] );
		}

		if ( empty( $response[0] ) ) {
			throw new \Exception( "Cannot get a valid auth id" );
		}

		\Depicter::cache('base')->set( 'googleClientId', $response[0], 2 * DAY_IN_SECONDS );
		return $response[0];
	}

	/**
	 * Login by google
	 *
	 * @param $accessToken
	 *
	 * @return mixed
	 * @throws GuzzleException
	 * @throws \Exception
	 */
	public static function googleLogin( $accessToken ) {

		$response = \Depicter::remote()->post( 'v1/remote/user/login/google', [
			'form_params' => [
				'accessToken' => $accessToken
			]
		] );

		$response = JSON::decode( $response->getBody(), true );

		if ( !empty( $response['errors'] ) ) {
			throw new \Exception( $response['errors'][0] );
		}

		error_log( print_r( $response, true ) );

		\Depicter::cache('base')->set( 'access_token', $response['accessToken'], DAY_IN_SECONDS );
		//\Depicter::cache('base')->set( 'refresh_token', $response['refreshToken'], DAY_IN_SECONDS );
		\Depicter::cache('base')->set( 'id_token', $response['idToken'], MONTH_IN_SECONDS );

		return $response;
	}
}
