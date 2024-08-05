<?php
namespace Depicter\Html;

use TypeRocket\Html\Html as TypeRocketHtml;

class Html extends  TypeRocketHtml
{

	/**
	 * Create new Tag
	 *
	 * @param string $tag
	 * @param array|string|null $attributes
	 * @param string|array|null $text
	 *
	 * @return TypeRocketHtml
	 */
	protected function el($tag, $attributes = null, $text = null )
	{
		$this->tag = new Tag( $tag, $attributes, $text );

		return $this;
	}

	/**
	 * Create new link
	 *
	 * @param string|array $text
	 * @param string $url
	 * @param array $attributes
	 *
	 * @return TypeRocketHtml
	 */
	protected function a($text = '', $url = '#', array $attributes = [])
	{
		$attributes = array_merge( array_filter(['href' => $url], '\TypeRocket\Utility\Str::notBlank'), $attributes );
		$this->tag = new Tag( 'a', $attributes, $text );

		return $this;
	}

	/**
	 * Create new image
	 *
	 * @param string $src
	 * @param array $attributes
	 *
	 * @return $this
	 */
	protected function img($src = '', array $attributes = [])
	{
		$attributes = array_merge( ['src' => $src], $attributes );
		$this->tag = new Tag( 'img', $attributes );

		return $this;
	}
}
