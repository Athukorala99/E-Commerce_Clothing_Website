<?php
namespace Depicter\Controllers\Ajax;

use Depicter\Utility\Sanitize;
use WPEmerge\Requests\RequestInterface;

class ReportIssueAjaxController
{

	public function sendIssue( RequestInterface $request, $view ) {

		$bodyParams = [
			'issueType'         => Sanitize::textfield( $request->body('issueType') ),
			'issueRelatesTo'    => Sanitize::textfield( $request->body('issueRelatesTo') ),
			'userDescription'   => Sanitize::editor( $request->body('userDescription') ),
			'userEmail'         => Sanitize::email( $request->body('userEmail') )
		];

		try {
			if ( \Depicter::client()->reportIssue( $bodyParams ) ) {
				return \Depicter::json([
					"hits" => 1,
					'message'   => "Feedback has been sent successfully"
				])->withStatus(200 );
			} else {
				return \Depicter::json([
					'errors'   => "Error while sending feedback, please try again later"
				])->withStatus(400 );
			}
		} catch ( \Exception $exception ){
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			]);
		}
	}

	public function sendError( RequestInterface $request, $view ) {
		$bodyParams = [
			'crashReport'   => Sanitize::textarea( $request->body('crashReport') ),
			'userComment'   => Sanitize::editor( $request->body('userComment') ),
			'envInfo'       => Sanitize::textarea( $request->body('envInfo') ),
			'userEmail'     => Sanitize::email( $request->body('userEmail') ),
			'userDocumentData'  => Sanitize::textarea( $request->body('userDocumentData') )
		];

		try {
			if ( \Depicter::client()->reportError( $bodyParams ) ) {
				return \Depicter::json([
					"hits" => 1,
					'message'   => "Error report has been sent successfully"
				])->withStatus(200 );
			} else {
				return \Depicter::json([
					'errors'   => "Error while sending report, please try again later"
				])->withStatus(400 );
			}
		} catch ( \Exception $exception ){
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			]);
		}
	}
}
