<?php

namespace Depicter\Front;


class Front
{

	public function bootstrap() {
		$this->assets()->bootstrap();
	}

	/**
	 * Returns the instance of preview class
	 *
	 * @return Preview
	 */
	public function preview()
	{
		return \Depicter::resolve('depicter.front.document.preview');
	}

	/**
	 * Returns the instance of render class
	 *
	 * @return Render
	 */
	public function render()
	{
		return \Depicter::resolve('depicter.front.document.render');
	}

	/**
	 * Returns the instance of assets class
	 *
	 * @return Assets
	 */
	public function assets()
	{
		return \Depicter::resolve('depicter.front.document.assets');
	}
}
