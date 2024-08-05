<?php
namespace Depicter\Html;


use Averta\WordPress\Handler\Error;

class Canvas
{
	/**
	 * @var Html
	 */
	private $html;

	/**
	 * @var Html
	 */
	private $head;

	/**
	 * @var Html
	 */
	private $body;

	/**
	 * @var array
	 */
	private $cssLinks;

	/**
	 * @var array
	 */
	private $jsLinks;

	/**
	 * @var string
	 */
	private $content;

	/**
	 * @var string
	 */
	private $inlineStyle;


	public function __construct( $content = '' )
	{
		$this->content = $content;
	}


	public function registerStyle( $cssLinks = [] )
	{
		$cssLinks = (array) $cssLinks;

		foreach ( $cssLinks as $cssLinkId => $cssLink ){
			if( is_numeric( $cssLinkId ) ){
				Error::trigger( 'You must specify a valid ID-name for each css link.' );
			}
			$this->cssLinks[ $cssLinkId ] = $cssLink;
		}

	}

	public function registerScript( $jsLinks = [] )
	{
		$jsLinks = (array) $jsLinks;

		foreach ( $jsLinks as $jsLinkId => $jsLink ){
			if( is_numeric( $jsLinkId ) ){
				Error::trigger( 'You must specify a valid ID-name for each javascript link.' );
			}
			$this->jsLinks[ $jsLinkId ] = $jsLink;
		}

	}

	public function setContent( $content )
	{
		$this->content = $content;
	}

	public function setInlineStyle( $style )
	{
		$this->inlineStyle = $style;
	}

	public function render()
	{
		$this->html = Html::html([]);

		// Make head tag
		$this->renderHead();

		// Make body tag
		$this->renderBody();

		$this->html->nest( "\n" .$this->head );
		$this->html->nest( "\n" .$this->body . "\n" );

		return $this->html;
	}

	protected function renderHead(){
		$this->head = Html::head([]);

		$this->head->nest( "\n" . Html::meta([ 'charset' => 'utf-8' ]) );
		$this->head->nest( "\n" . Html::meta([ 'name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0' ]) );
		$this->head->nest( "\n" . Html::title([], 'Depicter Preview') );

		$this->renderStyleTags();

		if( $this->inlineStyle ){
			$this->head->nest( "\n" .Html::style([], $this->inlineStyle ) . "\n" );
		}
	}

	protected function renderBody()
	{
		$this->body = Html::body([
			'class' => 'depicter-preview-canvas'
		], "\n" . $this->content . "\n" );

		$this->renderScriptTags();
	}

	protected function renderStyleTags(){
		if( empty( $this->cssLinks ) ){
			return;
		}

		foreach ( $this->cssLinks as $cssId => $cssLink ){
			$linkTag = Html::link([
				'rel'   => "stylesheet",
				'id'    => $cssId . '-css',
				'href'  => $cssLink,
				'media' => 'all'
			]);
			$this->head->nest( "\n" .$linkTag );
		}
	}

	protected function renderScriptTags(){
		if( empty( $this->jsLinks ) ){
			return;
		}

		foreach ( $this->jsLinks as $jsId => $jsLink ){
			$scriptTag = Html::script([
				'id'    => $jsId . '-js',
				'src'  => $jsLink
			]);
			$this->body->nest( $scriptTag . "\n" );
		}
	}
}
