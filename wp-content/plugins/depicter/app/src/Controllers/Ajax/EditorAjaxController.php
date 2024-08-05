<?php
namespace Depicter\Controllers\Ajax;


use Averta\Core\Controllers\RestMethodsInterface;
use Averta\Core\Utility\Arr;
use Averta\Core\Utility\Data;
use Averta\WordPress\Utility\JSON;
use Depicter;
use Depicter\Editor\EditorLocalization;
use Depicter\GuzzleHttp\Exception\ConnectException;
use Depicter\GuzzleHttp\Exception\GuzzleException;
use Depicter\Utility\Sanitize;
use Depicter\Utility\Http;
use Exception;
use GuzzleHttp\Psr7\UploadedFile;
use Psr\Http\Message\ResponseInterface;
use WPEmerge\Requests\RequestInterface;
use Psr\Http\Message\UploadedFileInterface;

//use Depicter\Editor\EditorData;

class EditorAjaxController implements RestMethodsInterface
{

	/**
	 * Retrieves Lists of all entries. (GET)
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return string
	 */
	public function index( RequestInterface $request, $view ){
		return "";
	}

	/**
	 * Adds a new entry. (POST)
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws Exception
	 * @throws GuzzleException
	 */
	public function store( RequestInterface $request, $view ){
		// fields to update
		$properties = [];

		// Get editor data
		$editor = $request->body( 'editor' );
		if( ! empty( $editor ) && JSON::isJson( $editor ) ){
			$properties['editor'] = $editor;
		}

		// Get document ID
		$id = Sanitize::int( $request->body( 'ID' ) );

		if( empty( $id ) ){
			return Depicter::json( [ 'errors' => [ 'Document "ID" is required.' ], ] )->withStatus( 400 );
		}

		// Get document name
		$name = Sanitize::textfield( $request->body( 'name' ) );

		if( ! empty( $name ) ){
			$properties['name'] = $name;
		}

		// Get document slug
		$slug = Sanitize::slug( $request->body( 'slug' ) );

		if( ! empty( $slug ) ){
			$properties['slug'] = $slug;
		}

		// Get document status
		$status               = Sanitize::textfield( $request->body( 'status' ) ) ?? 'draft';
		$properties['status'] = $status === 'published' ? 'publish' : $status;


		try{
			if( $properties['status'] == 'publish' ){

				if ( \Depicter::auth()->isPaid() && ! \Depicter::auth()->verifyActivation() ) {
					throw new Exception( esc_html__( 'License is not valid.', 'depicter' ) );
				}

				if( function_exists( 'get_filesystem_method' ) && get_filesystem_method() != 'direct' ){
					throw new Exception( esc_html__( 'Media files cannot be published due to lack of proper file permissions for uploads directory.', 'depicter' ) );
				}

				$editorRawData = $properties['editor'] ?? Depicter::document()->getEditorRawData( $id );

				// Download media if document published
				\Depicter::media()->importDocumentAssets( $editorRawData );
			}

			$result = Depicter::documentRepository()->saveEditorData( $id, $properties );

			if( false === $result ){
				return Depicter::json( [ 'errors' => [ 'Document does not exist.', $result ] ] )->withStatus( 404 );
			}

			// \Depicter::action()->do( 'depicter/editor/after/store', $id, $properties, $result );
			do_action( 'depicter/editor/after/store', $id, $properties, $result );

			$this->setDocumentPoster( $request, $id );

			return Depicter::json( [ 'hits' => [ 'status'      => $status,
			                                      'modifiedAt'  => $result['modifiedAt'],
			                                      'publishedAt' => $result['publishedAt'] ] ] )->withStatus( 200 );
		} catch( Exception $exception ){
			$error = Http::getErrorExceptionResponse( $exception );

			return \Depicter::json([
				'errors' => $error
			])->withStatus( $error['statusCode'] );
		}

	}

	/**
	 * Displays an entry. (GET)
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function show( RequestInterface $request, $view ){
		if( ! $documentId = Sanitize::int( $request->query( 'ID' ) ) ){
			return Depicter::json( [ 'errors' => [ 'Document ID is required.' ], ] )->withStatus( 400 );
		}

		if( ! $document = Depicter::documentRepository()->findById( $documentId ) ){
			return Depicter::json( [ 'errors' => [ 'Document ID not found.', $documentId ] ] )
			            ->withHeader( 'X-Document-ID', $documentId )
			            ->withStatus( 404 );
		}

		return Depicter::json( [ 'hits' => Arr::camelizeKeys( $document->getApiProperties(), '_' ) ] )
		                ->withHeader( 'X-Document-ID', $documentId )
		                ->withStatus( 200 );
	}

	/**
	 * Renames a document name.
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws Exception
	 */
	public function checkSlug( RequestInterface $request, $view ){
		// Get document ID
		$slug = Sanitize::textfield( $request->body( 'slug' ) );

		if( is_null( $slug ) ){
			return Depicter::json( [ 'errors' => [ 'Document slug is required.' ], ] )->withStatus( 400 );
		}

		$result = Depicter::documentRepository()->checkSlug( $slug );

		if( $result ){
			return Depicter::json( [ "errors" => [ "Taken!" ] ] )
			                ->withHeader( 'X-Taken-Slug', $slug )
			                ->withHeader( 'X-Available-Slug', Depicter::documentRepository()->makeSlug() )
			                ->withStatus( 423 );
		}

		return Depicter::json( [ 'hits' => $slug ] )->withStatus( 200 );
	}

	/**
	 * Retrieves editor and dashboard localized texts
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
	public function getLocalization( RequestInterface $request, $view ){
		return Depicter::json(
			EditorLocalization::getTranslateList()
		)->withHeader('Access-Control-Allow-Origin' , '*')->withStatus( 200 );
	}

	/**
	 * Renders a document markup.
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 * @throws Exception
	 */
	public function render( RequestInterface $request, $view ){
		if( ! $documentId = Sanitize::int( $request->query( 'ID' ) ) ){
			return Depicter::json( [ 'errors' => [ 'Document ID is required.' ], ] )->withStatus( 400 );
		}

		$args = [];
		if ( Data::isBool( $request->query( 'addImportant' ) ) ) {
			$args['addImportant'] = Sanitize::textfield( $request->query( 'addImportant' ) );
		}

		return Depicter::output( Depicter::front()->render()->document( $documentId, $args ) );
	}

	/**
	 * Outputs markup to preview a document
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
	public function preview( RequestInterface $request, $view ){
		$gutenberg = $request->query( 'gutenberg', '' );
		$documentId = Sanitize::int( $request->query( 'ID' ) );
		if( ! $documentId && empty( $gutenberg ) ){
			return Depicter::json( [ 'errors' => [ 'Document ID is required.' ], ] )->withStatus( 400 );
		}

		$status = Sanitize::textfield( $request->query( 'status' ) );

		$previewArgs = [ 'status' => ! empty( $status ) ? $status : 'auto',
		                 'start'  => Sanitize::int( $request->query( 'startSection' ) ),
						 'gutenberg' => !empty( $gutenberg ) ];

		return Depicter::output( Depicter::front()->preview()->document( $documentId, $previewArgs ) );
	}

	/**
	 * Retrieves object of document editor data
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function getEditorData( RequestInterface $request, $view ){
		if( ! $documentId = Sanitize::int( $request->query( 'ID' ) ) ){
			return Depicter::json( [ 'errors' => [ 'Document ID is required.' ], ] )->withStatus( 400 );
		}

		$output = '';

		try{
			$output = Depicter::document()->getEditorData( $documentId );
		} catch( Exception $exception ){
			return Depicter::json( [ 'errors' => [ $exception->getMessage() ] ] )
			                ->withHeader( 'X-Document-ID', $documentId )
			                ->withStatus( 404 );
		}

		return Depicter::json( $output );
	}


	/**
	 * Reverts a document to previous snapshots
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function revert( RequestInterface $request, $view ){
		if( ! $documentId = Sanitize::int( $request->body( 'ID' ) ) ){
			return Depicter::json( [ 'errors' => [ 'Document ID is required.' ], ] )->withStatus( 400 );
		}
		$to = Sanitize::textfield( $request->body( 'to' ) );

		try{
			$output = Depicter::document()->repository()->revert( $documentId, $to );
			return Depicter::json( $output );

		} catch( Exception $exception ){
			return Depicter::json( [ 'errors' => [ $exception->getMessage() ] ] )
			                ->withHeader( 'X-Document-ID', $documentId )
			                ->withStatus( 404 );
		}
	}

	/**
	 * Updates an entry. (PUT/PATCH)
	 *
	 * @param RequestInterface $request
	 * @param string           $view
	 *
	 * @return ResponseInterface
	 */
	public function update( RequestInterface $request, $view ){
		return Depicter::json( [ 'hits' => [] ] )->withStatus( 200 );
	}

	/**
	 * Upload cover photo of slider
	 *
	 * @param RequestInterface $request
	 * @param int              $id
	 *
	 * @return bool
	 */
	public function setDocumentPoster( RequestInterface $request, int $id ){
		$uploadedFiles = $request->getUploadedFiles();

		if( empty( $uploadedFiles['previewImage'] ) || empty( $id ) ){
			return false;
		}
		$previewImages = $uploadedFiles['previewImage'];

		if ( ! is_array( $previewImages ) ) {
			/* @var $previewImages UploadedFileInterface */
			if( $previewImages->getError() || ! $previewImages->getSize() ){
				return false;
			}
			return $this->uploadPreviewImage( $previewImages, $id );

		} else {
			/* @var $previewImages UploadedFileInterface[] */
			foreach( $previewImages as $key => $image ) {
				if( empty( $image ) || $image->getError() || ! $image->getSize() ){
					continue;
				}
				if( $key === 0 ){
					$this->uploadPreviewImage( $image, $id );
					$this->uploadPreviewImage( $image, $id . '-1' );
				} else {
					$fileName = $id . '-' . ($key + 1);
					$this->uploadPreviewImage( $image, $fileName );
				}
			}

			return true;
		}
	}

	/**
	 * Upload preview image to depicter upload folder
	 *
	 * @param UploadedFileInterface $imageFile
	 * @param string                $fileName
	 *
	 * @return bool
	 */
	public function uploadPreviewImage( UploadedFileInterface $imageFile, string $fileName ): bool{
		$mediaType = $imageFile->getClientMediaType();

		if( false === strpos( $mediaType, 'image/' ) ){
			// Not an image file
			return false;
		}

		return Depicter::storage()->filesystem()->write( Depicter::storage()->getPluginUploadsDirectory() . '/preview-images/' . $fileName . '.png', (string) $imageFile->getStream() );
	}

	/**
	 * Upload multiple document posters
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return ResponseInterface
	 */
	public function uploadDocumentPosters( RequestInterface  $request, $view ) {

		$documentId = Sanitize::textfield( $request->body('ID', '' ) );

		try {
			$coverPhoto = $request->getUploadedFiles();

			if( empty( $documentId ) ){
				throw new \Exception(__( 'Document ID is required.', 'depicter' ), 400 );
			}
			if( empty( $coverPhoto ) ){
				throw new \Exception(__( 'Cover image not found.', 'depicter' ), 400 );
			}
			if ( ! $this->setDocumentPoster( $request, $documentId ) ) {
				throw new \Exception(__( 'Error occurred! Cannot upload cover photos.', 'depicter' ), 400 );
			}

		} catch ( \Exception $exception ) {
			return Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus( $exception->getCode() );
		};

		return Depicter::json([ 'success' => true, "message" => "Cover images uploaded successfully." ])->withStatus( 200 );
	}

	/**
	 * Get Document Status
	 *
	 * @param RequestInterface $request
	 * @param string $view
	 *
	 * @return ResponseInterface
	 */
	public function getDocumentStatus( RequestInterface $request, $view ) {
		$documentId = absint( $request->query( 'ID', 0 ) );
		try {
			return Depicter::json([
				'status' => Depicter::documentRepository()->getStatus( $documentId )
			])->withStatus(200);
		} catch ( \Exception $exception ) {
			return Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus( 400 );
		};
	}

	public function destroy( RequestInterface $request, $view ){
		// TODO: Implement destroy() method.
	}
}
