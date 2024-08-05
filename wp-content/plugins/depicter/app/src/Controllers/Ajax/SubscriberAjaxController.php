<?php
namespace Depicter\Controllers\Ajax;

use Averta\WordPress\Utility\Sanitize;
use WPEmerge\Requests\RequestInterface;

class SubscriberAjaxController
{
	/**
	 * store subscriber
	 * @param  RequestInterface  $request
	 * @param $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \Depicter\GuzzleHttp\Exception\GuzzleException
	 */
	public function store( RequestInterface $request, $view ) {

		$bodyParams = [
			'email'    => Sanitize::email( $request->body('email') ),
			'group'    => Sanitize::textfield( $request->body('group') ),
			'name'    => Sanitize::textfield( $request->body('name') ),
		];

		try {
			if ( \Depicter::client()->subscribe( $bodyParams ) ) {
				// Set the user is subscribed to the newsletter
				\Depicter::options()->set('has_subscribed', 1);

				return \Depicter::json([
					"hits" => 1,
					'message'   => "You are successfully subscribed to the newsletter."
				])->withStatus(200 );
			} else {
				return \Depicter::json([
					'errors'   => "Error while sending subscriber, please try again later"
				])->withStatus(400 );
			}
		} catch ( \Exception $exception ){
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus(400);
		}
	}
}
