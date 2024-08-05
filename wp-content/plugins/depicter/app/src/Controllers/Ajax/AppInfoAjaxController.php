<?php
namespace Depicter\Controllers\Ajax;

use Averta\WordPress\Utility\JSON;
use Averta\WordPress\Utility\Sanitize;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\RequestInterface;

class AppInfoAjaxController {

	/**
	 * Retrieves Lists of all entries. (GET)
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function changelogs( RequestInterface $request, string $view)
	{
		try {
			$response = \Depicter::remote()->get( 'v1/core/changelogs', [ 'query' => $request->getQueryParams() ] );
			return \Depicter::json(
				JSON::decode( $response->getBody(), true )
			)->withStatus( 200 );

		} catch( \Exception $exception ) {
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus( 503 );
		}
	}

	/**
	 * Retrieves the existing promotion
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function getPromotion( RequestInterface $request, string $view)
	{
		try {
			$response = \Depicter::remote()->get( 'v1/core/announcements', [ 'query' => [ 'category' => 'promotions-wp-dash' ] ] );
			$promotions = JSON::decode( $response->getBody(), true );
			$currentPromotion = [];

			if( ! empty( $promotions['hits'] ) ){
				$currentPromotion = reset($promotions['hits'] );
			}
			return \Depicter::json( $currentPromotion )->withStatus( 200 );

		} catch( \Exception $exception ) {
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus( 503 );
		}
	}

	/**
	 * Retrieves the notifications
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function getNotifications( RequestInterface $request, string $view ) {
		$perPage = !empty( $request->query('perpage' ) ) ? Sanitize::int( $request->query( 'perpage' ) ) : 20;
		$page = !empty( $request->query('page' ) ) ? Sanitize::int( $request->query( 'page' ) ) : 1;

		try {
			$response = \Depicter::remote()->get( 'v1/core/notifications', [
				'query' => [
					'page' => $page,
					'perPage' => $perPage
				]
			]);

			$result = JSON::decode( $response->getBody(), true );

			$result['lastSeenID']   = \Depicter::options()->get('lastSeenNotificationID'  , '');
			$result['lastSeenDate'] = \Depicter::options()->get('lastSeenNotificationDate', '');

			return \Depicter::json( $result )->withStatus( 200 );

		} catch( \Exception $exception ) {
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus( 503 );
		}
	}

	/**
	 * Updates status info for notifications
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function updateNotificationsStatus( RequestInterface $request, string $view ) {
		$lastSeenID   = !empty( $request->body('lastSeenID'   ) ) ? Sanitize::int( $request->body( 'lastSeenID' ) ) : 0;
		$lastSeenDate = !empty( $request->body('lastSeenDate' ) ) ? Sanitize::textfield( $request->body( 'lastSeenDate' ) ) : '';

		if( empty( $lastSeenID ) && empty( $lastSeenDate ) ){
			return \Depicter::json([
				'errors' => [ 'Last seen ID or Date is required.' ]
			])->withStatus( 400 );
		}

		if( !empty( $lastSeenID ) ){
			\Depicter::options()->set('lastSeenNotificationID', $lastSeenID );
		}
		if( !empty( $lastSeenDate ) ){
			\Depicter::options()->set('lastSeenNotificationDate', $lastSeenDate );
		}

		return \Depicter::json( [ 'message' => "Last seen is successfully updated." ] )->withStatus( 200 );
	}

}
