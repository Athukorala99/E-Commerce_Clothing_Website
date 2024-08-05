<?php
namespace Depicter\Controllers\Ajax;

use Depicter\GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class PluginDeactivationController
{
	/**
	 * Send deactivation feedback
	 *
	 * @return ResponseInterface
	 */
	public function sendFeedback(){

		if ( empty( $_POST['issueRelatesTo'] ) ) {
			return \Depicter::json([
				'errors'   => "Empty deactivation reason"
			])->withStatus(400 );
		}

		$feedback = [
			'issueType'         => 'deactivation',
			'issueRelatesTo'    => sanitize_text_field( $_POST['issueRelatesTo'] ),
			'userDescription'   => !empty( $_POST['userDescription'] ) ? sanitize_text_field( $_POST['userDescription'] ) : ''
		];

		try {
			if ( \Depicter::deactivationFeedback()->sendFeedback( $feedback ) ) {
				return \Depicter::json([
					"hits" => 1,
					'message'   => "Feedback has been sent successfully"
				])->withStatus(200 );
			} else {
				return \Depicter::json([
					'errors'   => "Error while sending feedback, please try again later"
				])->withStatus(400 );
			}
		} catch( GuzzleException $e ) {
			return \Depicter::json([
               'errors'   => "Error while sending feedback, connection error..."
            ])->withStatus(400 );
		}

	}
}
