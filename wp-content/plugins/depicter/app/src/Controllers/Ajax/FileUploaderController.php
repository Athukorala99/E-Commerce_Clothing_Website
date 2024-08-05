<?php
namespace Depicter\Controllers\Ajax;

use WPEmerge\Requests\RequestInterface;

class FileUploaderController
{
	public function uploadFile(RequestInterface $request, $view) {

		try{
			$files = $request->files();

			if ( empty( $files ) ) {
				return \Depicter::json([
					'errors' => [ 'No file provided to upload']
				])->withStatus(400 );
			}

			$results = \Depicter::fileUploader()->upload( $files );

			if ( ! empty( $results ) ) {
				return \Depicter::json([
					'hits' => $results
				])->withStatus(200 );
			} else {
				return \Depicter::json([
					'errors' => [ 'Failed to upload media files.']
				])->withStatus(400 );
			}

		} catch( \Exception $exception ){
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus(400 );
		}

	}
}
