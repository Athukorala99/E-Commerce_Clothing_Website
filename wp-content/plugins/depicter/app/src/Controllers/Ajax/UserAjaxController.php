<?php
namespace Depicter\Controllers\Ajax;

use Averta\WordPress\Utility\Sanitize;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Depicter\Services\UserAPIService;
use WPEmerge\Requests\RequestInterface;

class UserAjaxController {

	/**
	 * User login
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
    public function login( RequestInterface $request, $view ) {
        $email = $request->body( 'email' , '' );
        $password = $request->body( 'password', '' );

        if ( empty( $email ) || empty( $password ) ) {
            return \Depicter::json([
                'errors' => [ __( 'Both email and password required to login', 'depicter' ) ]
            ])->withStatus(403);
        }

	    try{
			return \Depicter::json( UserAPIService::login( $email, $password ) )->withStatus( 200 );
	    } catch( GuzzleException $e ){
			return \Depicter::json([
				'errors' => [ $e->getMessage() ]
			])->withStatus( 401 );
	    } catch( \Exception $e ){
		    return \Depicter::json([
				'errors' => [ $e->getMessage() ]
            ])->withStatus( 401 );
	    }
    }

	/**
	 * User register
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function register( RequestInterface $request, $view ) {
		$email = Sanitize::email( $request->body('email', '') );
		$password = Sanitize::textfield( $request->body('password', '') );

		$fields = [
			'firstName' => Sanitize::textfield( $request->body('firstName', '') ),
			'lastName'  => Sanitize::textfield( $request->body('lastName', '') ),
		];

		if ( empty( $email ) || empty( $password ) ) {
			return \Depicter::json([
				'errors' => [ __( 'Both email and password required to register', 'depicter' ) ]
			])->withStatus(403);
		}

		try{
			return \Depicter::json( UserAPIService::register( $email, $password, $fields ) )->withStatus( 200 );
		} catch( GuzzleException $e ){
			return \Depicter::json([
				'errors' => [ $e->getMessage() ]
            ])->withStatus( 401 );
		} catch( \Exception $e ){
			return \Depicter::json([
				'errors' => [ $e->getMessage() ]
			])->withStatus( 401 );
		}
	}

	/**
	 * Get google client id
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function googleClientID( RequestInterface $request, $view ) {
		try {
			return \Depicter::json( UserAPIService::googleClientID() )->withStatus( 200 );
		} catch( GuzzleException $e ){
			return \Depicter::json([
                'errors' => [ $e->getMessage() ]
            ])->withStatus( 401 );
		} catch( \Exception $e ){
			return \Depicter::json([
                'errors' => [ $e->getMessage() ]
            ])->withStatus( 401 );
		}

	}

	/**
	 * Login by Google
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function googleLogin( RequestInterface $request, $view ) {
		$token = Sanitize::textfield( $request->body('accessToken', '') );

		if ( !empty( $token ) ) {
			try{
				return \Depicter::json( UserAPIService::googleLogin( $token ) )->withStatus( 200 );
			} catch( GuzzleException $e ){
				return \Depicter::json([
                    'errors' => [ $e->getMessage() ]
                ])->withStatus( 401 );
			} catch( \Exception $e ){
				return \Depicter::json([
					'errors' => [ $e->getMessage() ]
				])->withStatus( 401 );
			}
		} else {
			return \Depicter::json([
                'errors' => [ __( 'Token required!' ) ]
            ])->withStatus(400);
		}
	}

	/**
	 * Logout a registered user
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function logout( RequestInterface $request, $view ) {
		try {
			UserAPIService::logout();
			return \Depicter::json( [ "success" => true, "message" => "Logged out successfully" ] )->withStatus( 200 );
		} catch( GuzzleException $e ){
			return \Depicter::json([
                'errors' => [ $e->getMessage() ]
            ])->withStatus( 401 );
		} catch( \Exception $e ){
			return \Depicter::json([
                'errors' => [ $e->getMessage() ]
            ])->withStatus( 401 );
		}

	}
}
