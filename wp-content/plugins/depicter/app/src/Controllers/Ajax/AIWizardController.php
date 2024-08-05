<?php

namespace Depicter\Controllers\Ajax;

use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\JSON;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Depicter\Services\AssetsAPIService;
use Depicter\Utility\Http;
use Depicter\Utility\Sanitize;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\RequestInterface;

class AIWizardController {

	/**
	 * Generate keywords by AI
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function generateKeywords( RequestInterface $request, $view )
	{
		$args = [];

		if( ! Data::isNullOrEmptyStr( $request->body('description') ) ){
			$args['description'] = Sanitize::textarea( $request->body('description') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('number') ) ){
			$args['number'] = Sanitize::int( $request->body('number') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('model') ) ){
			$args['model'] = Sanitize::textfield( $request->body('model') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('debug') ) ){
			$args['debug'] = Sanitize::textfield( $request->body('debug') );
		}

		try {
			$response = \Depicter::remote()->post( 'v1/ai/text/wizard/keywords', [
				'form_params' => $args
			]);
			$result = JSON::decode( $response->getBody(), true );
			return \Depicter::json( $result )->withStatus(200);

		} catch ( \Exception  $exception ) {
			$error = Http::getErrorExceptionResponse( $exception );

			return \Depicter::json([
				'errors' => $error
			])->withStatus( $error['statusCode'] );
		}

	}

	/**
	 * Generate content for slides
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function wizardComplete( RequestInterface $request, $view ) {

		$args   = [];

		if( ! Data::isNullOrEmptyStr( $request->body('category') ) ){
			$args['category'] = Sanitize::textfield( $request->body('category') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('description') ) ){
			$args['description'] = Sanitize::textarea( $request->body('description') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('keywords') ) ){
			$args['keywords'] = Sanitize::textfield( $request->body('keywords') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('numberOfSections') ) ){
			$args['numberOfSections'] = Sanitize::int( $request->body('numberOfSections') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('headingCharLength') ) ){
			$args['headingCharLength'] = Sanitize::int( $request->body('headingCharLength') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('subheadingCharLength') ) ){
			$args['subheadingCharLength'] = Sanitize::int( $request->body('subheadingCharLength') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('descriptionCharLength') ) ){
			$args['descriptionCharLength'] = Sanitize::int( $request->body('descriptionCharLength') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('ctaCharLength') ) ){
			$args['ctaCharLength'] = Sanitize::int( $request->body('ctaCharLength') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('temperature') ) ){
			$args['temperature'] = Sanitize::textfield( $request->body('temperature') );
		}
		if( ! Data::isNullOrEmptyStr( $request->body('debug') ) ){
			$args['debug'] = Sanitize::textfield( $request->body('debug') );
		}

		try {
			$response = \Depicter::remote()->post( 'v1/ai/text/wizard/complete', [
				'form_params' => $args
			]);
			$result = JSON::decode( $response->getBody(), true );
			return \Depicter::json( $result )->withStatus(200);

		} catch ( \Exception  $exception ) {
			$error = Http::getErrorExceptionResponse( $exception );

			return \Depicter::json([
				'errors' => $error
			])->withStatus( $error['statusCode'] );
		}
	}

	/**
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface|void
	 * @throws GuzzleException
	 */
	public function importAIDocumentTemplate( RequestInterface $request, $view ) {

		$data = $request->body('data');
		$templateId = $request->body('ID', '');

		if ( empty( $templateId ) ) {
			return \Depicter::json([
               'errors' => ['Template ID is required.']
           ])->withStatus(400);
		}

		if ( !JSON::isJson( $data ) ) {
			return \Depicter::json([
				'errors' => ['Invalid JSON format.']
			])->withStatus(400);
		}

		$data = JSON::decode( $data, true );

		if ( empty( $data['colorPalette'] ) || count( $data['colorPalette'] ) < 5 ) {
			return \Depicter::json([
               'errors' => ['colorPalette should have 5 colors']
           ])->withStatus(400);
		}

        $result = AssetsAPIService::getDocumentTemplateData( $templateId, [ 'directory' => 4 ] );

        if ( !empty( $result->errors ) ) {
            return Http::getErrorJson( $result->errors );

        } elseif ( !empty( $result->hits ) ) {
			$editorData = JSON::encode( $result->hits );
            $editorData = JSON::decode( $editorData, true );
	        $editorData = JSON::encode( \Depicter::AIWizard()->updateEditorDataByAiContent( $editorData, $data ) );

			$editorData = preg_replace( '/"activeBreakpoint":".+?"/', '"activeBreakpoint":"default"', $editorData );
			$document = \Depicter::documentRepository()->create();

			$updateData = [];
			if ( !empty( $result->title ) ) {
				$updateData['name'] = __("AI-Aided Slider") . ' ' . $document->getID();
			}

			if ( !empty( $result->image ) ) {
				$previewImage = file_get_contents( $result->image );
				\Depicter::storage()->filesystem()->write( \Depicter::documentRepository()->getPreviewImagePath( $document->getID() ) , $previewImage );
			}

			\Depicter::media()->importDocumentAssets( $editorData );
			$updateData['content'] = \Depicter::AIWizard()->fixMediaSizes( $editorData );
	        \Depicter::documentRepository()->update( $document->getID(), $updateData );

			return \Depicter::json([
				'hits' => [
					'documentID' => $document->getID()
				]
			]);
        } else {
            // Return the error message received from server
            return \Depicter::json( $result );
        }
	}
}
