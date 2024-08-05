<?php
namespace Depicter\Database\Repository;

use Averta\Core\Utility\Arr;
use Averta\Core\Utility\JSON;
use Depicter;
use Depicter\Database\Entity\Document;
use Depicter\Exception\DocumentNotFoundException;
use Exception;
use TypeRocket\Database\Results;
use TypeRocket\Models\Model;

class DocumentRepository
{
	/**
	 * @var Document
	 */
	private $document;


	public function __construct(){
		$this->document = New Document();
	}

	/**
	 * @return Document
	 *
	 * @throws Exception
	 */
	public function document()
	{
		return new Document();
	}


	/**
	 * Save changes directly
	 *
	 * @param int   $id
	 * @param array $properties
	 *
	 * @return array
	 * @throws Exception
	 */
	public function saveEditorData( int $id = 0, array $properties = [] )
	{
		if( isset( $properties['editor'] ) ){
			$editor = $properties['editor'];
			unset( $properties['editor'] );

			$parsedObject = $this->getParsedEditorContent( $editor );

			$properties['sections_count' ] = $parsedObject->getSectionsCount();
			$properties['content' ] = JSON::normalize( $editor );
		}

		// validate if the slug is not taken before
		if( isset( $properties['slug' ] ) ){
			$properties['slug' ] = trim( $properties['slug' ] );

			if( empty( $properties['slug' ] ) ){
				throw new Exception('Slug cannot be empty.');
			}
			if( $this->checkSlug( $properties['slug' ], $id ) ){
				throw new Exception('The slug is already taken by another document.');
			}
		}

		// name cannot be empty string
		if( isset( $properties['name' ] ) ){
			$properties['name' ] = trim( $properties['name' ] );

			if( empty( $properties['name' ] ) ){
				throw new Exception('Name cannot be empty.');
			}
		}

		if ( $properties['status'] == 'unpublished' ) {
			$properties['status'] = 'draft';
			$this->changeRevisionsStatus( $id, 'draft');
		}

		$result = $this->update( $id, $properties, true );

		if( $properties['status'] == 'publish' ){
			$this->addRevision( $id, $properties );
		}

		return [
			'result' => $result,
			'modifiedAt'    => $this->document->getApiProperties()['modified_at'],
			'publishedAt'    => $this->document->getLastPublishedAt()
		];
	}

	/**
	 * @param $editorContent
	 *
	 * @return mixed
	 */
	private function getParsedEditorContent( $editorContent ){
		// Get editor data
		$documentMapper = \Depicter::resolve('depicter.document.mapper');
		return $documentMapper->hydrate( $editorContent )->get();
	}

	/**
	 * Creates new revision for a document
	 *
	 * @param int   $id
	 * @param array $fields
	 *
	 * @return mixed
	 * @throws Exception
	 */
	protected function addRevision( int $id = 0, array $fields = [] )
	{
		$fields['created_at'] =  $this->document->getDateTime();
		$fields['parent'] =  $id;

		// check if revisions limit exceed than 15 number then remove the oldest one
		if( DEPICTER_REVISIONS ){
			$this->checkRevisionsLimit( $id );
		}

		$documentId = $this->findOrCreate(0, $fields);

		return $documentId;
	}

	/**
	 * change status of revisions
	 *
	 * @param int $id
	 * @param string $status
	 * @return void
	 */
	public function changeRevisionsStatus( int $id, string $status ) {
		$revisions = $this->document()->where( 'parent', $id)->findAll();
		if ( !empty( $revisions ) ) {
			$revisions = $revisions->get();
			foreach( $revisions as $revision ) {
				$revision->update(['status' => $status ]);
			}
		}
	}

	/**
	 * Reverts to a previously published checkpoint
	 *
	 * @param        $documentId
	 * @param string $revisionId
	 *
	 * @return array|bool|false|int|object|Model|null
	 * @throws Exception
	 */
	public function revert( $documentId, string $revisionId = '~1' ){

		if( ! $parentDocument = $this->findOne( $documentId ) ){
			throw new Exception('Document does not exist.');
		}
		// Set a valid $revisionId
		if( "" === $revisionId || is_null( $revisionId ) ){
			$revisionId = "~1";
		}

		$lastRevision = null;

		// Reverting the status back by step number, prefixed by `~`
		if( false !== strpos( $revisionId, '~' ) ){
			$revisionOffset = (int) ltrim( $revisionId, '~' );
			$lastRevision = $this->getRecentRevision( $documentId, $revisionOffset - 1 );
		// revert by revision document ID
		} elseif( is_numeric( $revisionId ) ){
			$lastRevision = $this->findOne( $revisionId );
		}

		if( ! empty( $lastRevision ) ){
			// Throw error if revision does not belong to the document
			if( $parentDocument->getID() != $lastRevision->parent() ){
				throw new Exception('Revision ID does not belong to this document.');
			}
			// Set revision editor data for document
			$parentDocument->save( ['content' => $lastRevision->content() ] );

		} else {
			throw new Exception("Couldn't revert to the revision. No revision found for this checkpoint.");
		}

		return $lastRevision;
	}

	/**
	 * Check for revisions limit number
	 *
	 * @param $id
	 *
	 * @throws Exception
	 */
	protected function checkRevisionsLimit( $id ) {
		$revisions = $this->document()->select('id')->where('parent', $id)->findAll();
		if ( $revisions->count() >= DEPICTER_REVISIONS ) {
			$this->document()->findById( $revisions->first()->id )->delete();
		}
	}

	/**
     * Save changes directly to current document
     *
     * @param array $fields
     *
     * @return array|Model|object|Results|null
	 */
	public function all( array $fields = [] )
	{
		return $this->document->findAll()->get();
	}

	/**
	 * Save changes directly to current document
	 *
	 * @param array $fields
	 * @param array $args
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getList( array $fields = [], $args = [] )
	{
		$columnsName = !empty( $fields ) ? $fields : ['id', 'name', 'slug', 'type', 'author', 'sections_count', 'created_at', 'modified_at', 'thumbnail', 'status'];
		$numberOfPages = '';
		if ( !empty( $args['orderBy'] )  && !empty( $args['order'] ) ) {
			$documents = $this->select( $columnsName )->orderBy( $args['orderBy'], $args['order'] );
		} else {
			$documents = $this->select( $columnsName );
		}

		if ( !empty( $args['s'] ) ) {
			$documents = $documents->where( 'name', 'like', '%' . $args['s'] . '%' );
		}

		if ( !empty( $args['page'] ) && !empty( $args['perPage'] ) ) {
			$pager = $documents->paginate( $args['perPage'], $args['page'] );
			if ( $pager ) {
				$numberOfPages = $pager->getNumberOfPages();
				$documents = $pager->getResults();
			} else {
				$documents = [];
			}

		} else {
			$documents = $documents->findAll()->get();
		}


		$documents = $documents ? $documents->toArray() : [];

		if ( $documents && empty( $fields ) ) {
			$uploadDir = wp_upload_dir();
			foreach ( $documents as $key => $document ) {
				$documents[ $key ]['publishedAt'] = $this->getLastPublishedAt( $document );

				if ( is_file( $uploadDir['basedir'] . '/depicter/preview-images/' . $document['id'] . '.png' ) ) {
					$documents[ $key ]['previewImage'] = $uploadDir['baseurl'] . '/depicter/preview-images/' . $document['id'] . '.png';
				} else {
					$documents[ $key ]['previewImage'] = '';
				}
			}
		}

		if ( !empty( $numberOfPages ) ) {
			return [
				'page' => $args['page'],
				'perPage' => $args['perPage'],
				'numberOfPages' => $numberOfPages,
				'documents' => $documents
			];
		}

		return $documents;
	}

	/**
	 * Queries records of documents with specified fields
	 *
	 * @param array $fields
	 *
	 * @return Document
	 * @throws Exception
	 */
	public function select( array $fields = [] )
	{
		$columnsName = !empty( $fields ) ? $fields : ['id', 'name', 'slug', 'author', 'sections_count', 'created_at', 'modified_at', 'thumbnail', 'status'];
		return $this->document()->reselect( $columnsName )
			->where('parent', '0');
	}

	/**
     * Save changes directly to current document
     *
     * @param array $fields
     *
     * @return mixed
     */
	public function save( array $fields = [] )
	{
		$fields = $this->getMergedFields( $fields );
		return $this->document->save( $fields);
	}

	/**
	 * Updates a document by ID
	 *
	 * @param int   $id
	 * @param array $fields
	 * @param bool  $parentOnly  Only update the record if is parent document
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function update( int $id = 0, array $fields = [], bool $parentOnly = false )
	{
		if( $document = $this->document->findById( $id ) ){
			if( $parentOnly && $document->getFieldValue('parent') != 0 ){
				throw new Exception('Updating revision is not allowed.');
			}

			$fields['modified_at'] = $document->getDateTime();
			return $document->update( $fields );
		}
		return false;
	}



	/**
	 * Creates new document in database
	 *
	 * @return Document
	 * @throws Exception
	 */
	public function create( $type = 'custom' )
	{
		$documentId = $this->findOrCreate(0,
		    $this->getMergedFields([
				'slug' => $this->makeSlug(),
				'type' => $type,
				'created_at'  => $this->document->getDateTime(),
				'modified_at'  => $this->document->getDateTime()
		    ])
		);

		$document = $this->document()->findById( $documentId );
		$document->rename( $document->getFieldValue( 'name' ) . ' ' . $documentId );

		error_log( $document->getFieldValue( 'name' ) );

		return $document;
	}

	/**
	 * Renames a document.
	 *
	 * @param $id
	 * @param $name
	 *
	 * @return bool
	 */
	public function rename( $id, $name )
	{
		if( $document = $this->document->findById( $id ) ){
			return $document->rename( $name );
		}
		return false;
	}

	/**
	 * Changes the document slug.
	 *
	 * @param $id
	 * @param $slug
	 *
	 * @return int
	 * @throws Exception
	 */
	public function changeSlug( $id, $slug )
	{
		if( $this->checkSlug( $slug ) ){
			throw new Exception("The slug is already in use.");
		}
		if( $document = $this->document->findById( $id ) ){
			return $document->changeSlug( $slug );
		}
		return false;
	}

	/**
	 * Duplicates a document.
	 *
	 * @param int  $id
	 *
	 * @param bool $returnNew
	 *
	 * @return int
	 * @throws Exception
	 */
	public function duplicate( int $id, bool $returnNew = false )
	{
		if( $document = $this->document->findById( $id ) ){
			$fields = $document->getProperties();

			unset( $fields['id'] );

			$fields['name'] = is_string( $fields['name'] ) ? $fields['name'] . ' copy' : $fields['name'];
			$fields['slug'] = $this->makeSlug();
			$fields['created_at'] = $this->document->getDateTime();

			if( ! is_numeric( $fields['sections_count'] ) ){
				unset( $fields['sections_count'] );
			}
			if( ! is_numeric( $fields['parent'] ) ){
				unset( $fields['parent'] );
			}
			if( ! is_numeric( $fields['author'] ) ){
				unset( $fields['author'] );
			}
			if( is_null( $fields['content'] ) ){
				unset( $fields['content'] );
			}
			if( is_null( $fields['password'] ) ){
				unset( $fields['password'] );
			}
			if( is_null( $fields['thumbnail'] ) ){
				unset( $fields['thumbnail'] );
			}

			$newId = $this->findOrCreate( 0, $fields );

			return $this->document()->findById( $newId );
		}

		return false;
	}

	/**
	 * Removes a document.
	 *
	 * @param $id
	 *
	 * @return int
	 * @throws Exception
	 */
	public function delete( $id )
	{
		if( $document = $this->document()->findById( $id ) ){
			$revisions = $this->document()->select('id')->where('parent', $id )->get();
			if ( $revisions ) {
				$revisionIDs = wp_list_pluck(  $revisions->toArray() , 'id' );
				$this->document()->delete( $revisionIDs );
			}
			return $document->delete();
		}
		return false;
	}

	/**
	 * Find a document by ID
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function findById( int $id )
	{
		return $this->document()->findById( $id );
	}

	/**
	 * Finds or creates new document in database
	 *
	 * @param integer $id
	 * @param array   $fields
	 *
	 * @return mixed      returns Document if exists or id of created document
	 * @throws Exception
	 */
	public function findOrCreate( int $id, array $fields = [] )
	{
		$fields = $this->getMergedFields( $fields );

		return $this->document()->findOrCreate( $id, $fields );
	}

	/**
	 * Retrieves the lst document
	 *
	 * @return array|bool|false|int|object|Model|null
	 * @throws Exception
	 */
	public function getLastDocument()
	{
		return $this->document()->findAll()->orderBy('id', 'DESC')->first();
	}

	/**
	 * Make a unique slug
	 *
	 * @param string $slug
	 * @param int    $id
	 *
	 * @return string
	 * @throws Exception
	 */
	public function makeSlug( string $slug = '', int $id = 0 )
	{
		if( ! $id && $document = $this->getLastDocument() ){
			$id = $document->getID();
		}

		if( ! $slug ){
			$slug = 'document';
		}

		$newID = $id + 1;
		$newSlug = $slug . '-' . $newID;

		while( $this->checkSlug( $newSlug ) ){
			$newID++;
			$newSlug = $slug . '-' . $newID;
		}

		return $newSlug;
	}

	/**
	 * Rename document
	 *
	 * @param string $slug
	 *
	 * @param int    $ignoreID  The document ID to ignore on check
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function checkSlug( string $slug, int $ignoreID = 0 )
	{
		$document   = $this->document();
		$foundCount = $document->where('slug', $slug )
							   ->where('id', 'NOT LIKE', $ignoreID )
							   ->findAll()->count();

		if( $foundCount > 0 ){
			unset( $document );
			return true;
		}

		return false;
	}

	/**
	 * Retrieves default fields
	 *
	 * @return array
	 */
	public function draftFields( $type = '')
	{
		$typesDictionary = [
			'slider' => __( 'Slider', 'depicter' ),
			'custom' => __( 'Slider', 'depicter' ),
			'popup'  => __( 'Popup', 'depicter' )
		];

		$typeLabel = !empty( $typesDictionary[ $type ] ) ? $typesDictionary[ $type ] : __('Slider', 'depicter' );

		return [
			'name'        => sprintf( __('Untitled %s', 'depicter' ), $typeLabel ),
			'status'      => 'draft',
			'author'      => $this->getCurrentUserId()
		];

	}

	/**
	 * Retrieves current logged in user ID
	 *
	 * @return int
	 */
	public function getCurrentUserId(){
		return is_user_logged_in() ? get_current_user_id() : 0;
	}

	/**
	 * Retrieves default fields
	 *
	 * @return array
	 */
	public function defaultFields()
	{
		return [
			'name'           => __('Untitled Slider'),
			'slug'			 => '',
			'type'           => 'slider',
			'author'         => 0,
			'parent'         => 0,
			'created_at'     => $this->document->getDateTime(),
			'sections_count' => 0,
			'thumbnail'      => '',
			'content' 		 => '',
			'password' 		 => '',
			'status'         => 'draft'
		];

	}

	/**
	 * Merge fields with default fields
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	public function getMergedFields( $fields )
	{
		$type = $fields['type'] ?? '';

		return Arr::merge( $fields, $this->draftFields( $type ) );
	}

	/**
	 * Get a revision of a document
	 *
	 * @param int $id      Document ID
	 * @param int $offset  offset
	 *
	 * @return array|Model|object|Results|null
	 * @throws Exception
	 */
	public function getRecentRevision( int $id, int $offset = 0 ) {
		$offset = max( $offset, 0 );
		return $this->document()->orderBy('id', 'DESC')->where( 'parent', $id )->take( 1, $offset )->get();
	}

	/**
	 * @param array $document  Document object in array
	 *
	 * @return mixed|string|null
	 * @throws Exception
	 */
	public function getLastPublishedAt( array $document ) {
		if ( $document['status'] == 'publish' ) {
			return $document['modified_at'];
		}

		$lastRevision = $this->getRecentRevision( $document['id'] );

		return $lastRevision && $lastRevision->status === 'publish' ? $lastRevision->modified_at : null;
	}

	/**
	 * Get a document entity
	 *
	 * @param null  $documentId
	 * @param array $where
	 *
	 * @return array|bool|int|object|Results|Model|null
	 * @throws Exception
	 */
	public function findOne( $documentId = null, array $where = [] ){

		if( $documentId ){
			$where['id'] = $documentId;
		}

		$documentEntity = $this->find( $where );

		if ( ! $documentEntity->count() && !empty( $where['id'] ) ) {
			$where['status'] = 'publish';
			$where['parent'] = $where['id'];
			unset( $where['id'] );

			if ( ! $documentEntity = $this->find( $where )->orderBy('id', 'DESC') ) {
				return false;
			}
		}

		return $documentEntity->first();
	}

	/**
	 * Retrieves the content of document
	 *
	 * @param int   $documentId
	 * @param array $where
	 *
	 * @return mixed
	 * @throws DocumentNotFoundException
	 */
	public function geContent( int $documentId, array $where = [] ){
		if( ! $documentEntity = $this->findOne( $documentId, $where ) ){
			if ( isset( $where['status'] ) && is_array( $where['status'] ) ) {
				unset( $where['status'][0] );
				if( ! $documentEntity = $this->findOne( $documentId, $where ) ){
					throw new DocumentNotFoundException( 'Document does not exist.', 404, $where );
				}
			} else {
				throw new DocumentNotFoundException( 'Document does not exist.', 404, $where );
			}
		}
		return $documentEntity->content();
	}

	/**
	 * Find based of where clauses
	 *
	 * @param array $where
	 *
	 * @return Document
	 * @throws Exception
	 */
	public function find( array $where = [] ){
		$documentEntity = $this->document();
		foreach ( $where as $clause => $clauseValue ) {
			$documentEntity->where( $clause,  is_array( $clauseValue ) ? 'IN' : '=' , $clauseValue );
		}

		return $documentEntity;
	}

	/**
	 * Whether the document has been published so far or not
	 *
	 * @param int   $documentId
	 *
	 * @return bool
	 */
	public function isPublishedBefore( $documentId ){
		try{
			if( $this->getRecentRevision( $documentId ) ){
				return true;
			}

			return $this->isPublished( $documentId );
		} catch ( \Exception $exception ) {
			return false;
		}
	}

	/**
	 * Whether the document has published status or not
	 *
	 * @param int $documentId
	 *
	 * @return bool|Document
	 * @throws Exception
	 */
	public function isPublished( $documentId ){
		try{
			return $this->getStatus( $documentId ) === 'publish';
		} catch ( \Exception $exception ) {
			return false;
		}
	}

	/**
	 * @param int    $documentID  Document ID
	 * @param string $fieldName   Table field to retrieve value from
	 *
	 * @return array|mixed|object|string|null
	 */
	public function getFieldValue( $documentID, $fieldName = 'name' ){

		if( $document = $this->document->findById( $documentID ) ){
			return $document->getFieldValue( $fieldName );
		}
		return null;
	}

	/**
	 * Get status of document
	 *
	 * @param int $documentId
	 *
	 * @return string
	 */
	public function getStatus( $documentId ) {
		if( $document = $this->findById( $documentId ) ){
			$document = $document->toArray();
			return $document['status'];
		}

		return '';
	}

	/**
	 * Retrieves the url to preview image of document if exists
	 *
	 * @param int $documentID
	 *
	 * @return string   URL of image if exits, otherwise, empty string
	 */
	public function getPreviewImageUrl( int $documentID ){
		if( file_exists( $this->getPreviewImagePath( $documentID ) ) ){
			return Depicter::storage()->uploads()->getBaseUrl() . $this->getPreviewRelativeImagePath( $documentID );
		}
		return '';
	}

	/**
	 * Retrieves the path to preview image of a document
	 *
	 * @param int $documentID
	 *
	 * @return string
	 */
	public function getPreviewImagePath( int $documentID ){
		return Depicter::storage()->uploads()->getBaseDirectory() . $this->getPreviewRelativeImagePath( $documentID );
	}

	/**
	 * Writes preview image to the disk
	 *
	 * @param int    $documentID
	 * @param string $imageContent
	 *
	 * @return bool
	 */
	public function savePreviewImage( int $documentID, string $imageContent ){
		return Depicter::storage()->filesystem()->write(
			$this->getPreviewImagePath( $documentID ),
			(string) $imageContent
		);
	}

	/**
	 * Retrieves the relative path to preview image of a document
	 *
	 * @param $documentID
	 *
	 * @return string
	 */
	protected function getPreviewRelativeImagePath( $documentID ){
		return '/depicter/preview-images/' . $documentID . '.png';
	}
}
