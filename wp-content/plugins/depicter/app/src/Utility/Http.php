<?php
namespace Depicter\Utility;

use Psr\Http\Message\ResponseInterface;


class Http {

	public static function getErrorExceptionResponse( \Exception $exception ){
		$isConnectionError = method_exists( $exception, 'getHandlerContext' );
		$errorClassInstance = get_class( $exception );

		$error = [
			'message'    => $exception->getMessage(),
			'detail'     => $exception->getMessage(),
			'errCode'    => $exception->getCode(),
			'group'      => 'Exception', // name of exception group
			'type'       => basename( str_replace('\\', '/',  $errorClassInstance )), // unqualified (short) exception class name
			'instance'   => $errorClassInstance,
			'context'    => null,
			'statusCode' => 500
		];

		if( $isConnectionError ){
			$error['message']    = __( 'Connection Error ..', 'depicter' );
			$error['group']      = 'RequestException';
			$error['context']    = $exception->getHandlerContext();
			$error['statusCode'] = 503;
		}

		if( ! empty( $error['context']['error'] ) ){
			$error['detail'] = $error['context']['error'];
		}
		if( ! empty( $error['context']['errno'] ) ){
			$error['errCode'] = $error['context']['errno'];
		}
		if( $error['errCode'] == 401 ){
			$error['message'] = "You are not authorized to access this resource.";
		}

		return $error;
	}

	/**
	 * Generate a Json response based on errors variable
	 *
	 * @param $errors
	 *
	 * @return ResponseInterface
	 */
	public static function getErrorJson( $errors ){
		$errors = (array) $errors;
		$statusCode = (int) ( !empty( $errors['code'] ) ? $errors['code'] : 200 );

		return \Depicter::json([
			'errors' => $errors
		])->withStatus( max( $statusCode, 200 ) );
	}
}
