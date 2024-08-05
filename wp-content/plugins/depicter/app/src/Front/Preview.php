<?php
namespace Depicter\Front;


use Averta\Core\Utility\Arr;
use Depicter\Document\Models\Document;
use Depicter\Html\Html;

class Preview
{

	/**
	 * Preview constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * Prepares viewArgs for document preview
	 *
	 * @return array
	 */
	public function prepare()
	{
		// prevent enabling preview mode if render mode param was passed
		if( empty( $_REQUEST['renderMode'] ) ){
			$this->enablePreviewMode();
		}

		$viewArgs = [
			'head'  => '',
			'title' => __( 'Depicter Preview', 'depicter' ),
			'footer' => ''
		];

		$styleLinks = \Depicter::front()->assets()->getStyles(['common', 'situational']);

		foreach ( $styleLinks as $styleId => $styleLink ) {
			$viewArgs['head'] .= Html::link([
					'rel'   => "stylesheet",
					'id'    => $styleId . '-css',
					'href'  => $styleLink,
					'media' => 'all'
				]) . "\n";
		}

		$scriptLinks = \Depicter::front()->assets()->getScripts('player');

		foreach ( $scriptLinks as $scriptId => $scriptLink ){
			$viewArgs['footer'] .= Html::script([
					'id'    => $scriptId . '-js',
					'src'  => $scriptLink
				]) . "\n";
		}

		return $viewArgs;
	}

	/**
	 * Generates a complete HTML page to preview document
	 *
	 * @param       $documentId
	 * @param array $documentArgs
	 *
	 * @return string|array
	 */
	public function document( $documentId, $documentArgs = [] )
	{
		$defaults = [
			'status' => 'draft',
			'start'  => null
		];
		$documentArgs = Arr::merge( $documentArgs, $defaults );

		$viewArgs = $this->prepare();
		$where = [ 'status' => trim( $documentArgs['status'] ) ];

		// support for multiple statuses (status=publish|draft)
		if( strpos( $where['status'], '|' ) !== false ){
			$where['status'] = explode('|', $where['status'] );
		}

		$where['start'] = $documentArgs['start'];

		if ( !empty( $documentArgs['gutenberg'] ) ) {
			$scriptLinks = \Depicter::front()->assets()->getScripts('iframe-resizer-content');

			foreach ( $scriptLinks as $scriptId => $scriptLink ){
				$viewArgs['footer'] .= Html::script([
						'id'    => $scriptId . '-js',
						'src'  => $scriptLink
					]) . "\n";
			}

			if ( !$documentId ) {
				$viewArgs['content'] = '<span class="depicter-no-content">' . esc_html__( 'Please select a slider from the list.', 'depicter' ) . '</span>';
				return $this->view( $viewArgs );
			}

		}

		try{
			$documentModel = \Depicter::document()->getModel( $documentId, $where );
			$documentModel->setUnpublishedNotice( false );
			$viewArgs = $this->prepareToRender( $documentModel, $viewArgs );

			if ( !empty( $documentArgs['viewParts'] ) ) {
				return $viewArgs;
			}
			if ( $documentModel->isDisplayExtension() ) {
				$viewArgs['body_classes'][] = 'depicter-display-canvas';
			}
		} catch ( \Exception $exception ) {
			$viewArgs['content'] = '<span class="depicter-no-content">' . $exception->getMessage() . '</span>';
		}

		return $this->view( $viewArgs );
	}

	/**
	 * prepare document model to render
	 *
	 * @param Document $documentModel
	 * @param $viewArgs
	 *
	 * @return mixed
	 */
	protected function prepareToRender( $documentModel, $viewArgs )
	{
		// Prepare and render document
		$viewArgs['content'] = Html::div( [ 'class' => 'depicter-preview-canvas' ], $documentModel->prepare()
		                                                                                          ->render() );

		// Link to special styles
		$stylesList = [];
		if( $fontsLink = $documentModel->getFontsLink() ){
			$stylesList["depicter-google-fonts"] = $fontsLink;
		}
		foreach( $stylesList as $styleId => $styleLink ){
			$viewArgs['head'] .= Html::link( [ 'rel'   => "stylesheet",
			                                   'id'    => $styleId . '-css',
			                                   'href'  => $styleLink,
			                                   'media' => 'all' ] ) . "\n";
		}

		$documentID = $documentModel->getDocumentID();

		// Add custom styles
		$localFontsCss = \Depicter::documentFonts()->getLocalFontsCss( $documentID );
		$viewArgs['head'] .= $documentModel->getBeforeInitCssAndTag() . "\n";
		$viewArgs['head'] .= Html::style( [ 'type' => 'text/css' ], $documentModel->getCss() . $localFontsCss ) . "\n";

		if ( $document = \Depicter::documentRepository()->findById( $documentID ) ) {
			$documentName = $document->getFieldValue('name');
			$viewArgs['title'] = empty( $documentName ) ? $viewArgs['title'] : $documentName;
		}

		return $viewArgs;
	}

	/**
	 * Renders a page with message
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function message( $content )
	{
		$viewArgs = $this->prepare();
		$viewArgs['content'] = '<span class="depicter-no-content">' . $content . '</span>';

		return $this->view( $viewArgs );
	}

	/**
	 * Renders page view
	 *
	 * @param $args
	 *
	 * @return string
	 */
	protected function view( $args ){
		// JetPack compatibility issue 
		if ( class_exists( 'Filter_Embedded_HTML_Objects' ) ) {
			remove_filter( 'pre_kses', array( 'Filter_Embedded_HTML_Objects', 'filter' ), 11 );
		}

		$view = \Depicter::view('canvas.php')->with( 'view_args', $args )->toString();

		// JetPack compatibility issue 
		if ( class_exists( 'Filter_Embedded_HTML_Objects' ) ) {
			add_filter( 'pre_kses', array( 'Filter_Embedded_HTML_Objects', 'filter' ), 11 );
		}

		return $view;
	}

	/**
	 * Set a constant to specify document preview mode
	 */
	protected function enablePreviewMode() {
		if ( !defined('IS_DEPICTER_PREVIEW') ) {
			define( 'IS_DEPICTER_PREVIEW', true );
		}
	}

	/**
	 * Check if preview mode is active or not
	 *
	 * @return bool
	 */
	public function isPreview() {
		return defined( 'IS_DEPICTER_PREVIEW' ) && IS_DEPICTER_PREVIEW;
	}
}
