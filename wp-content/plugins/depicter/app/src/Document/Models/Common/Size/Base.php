<?php
namespace Depicter\Document\Models\Common\Size;


class Base
{
	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var mixed
	 */
	public $width;

	/**
	 * @var mixed
	 */
	public $height;

	/**
	 * @var int
	 */
	public $sideSpace;

	/**
	 * @var bool
	 */
	public $useCustomSize;
}
