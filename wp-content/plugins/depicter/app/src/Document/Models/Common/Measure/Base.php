<?php
namespace Depicter\Document\Models\Common\Measure;


class Base
{
	/**
	 * @var string
	 */
	public $default;

	/**
	 * @var string
	 */
	public $unit;

	/**
	 * @var mixed
	 */
	public $value;


	public function __construct( $value )
	{
		$this->value = $value;
	}

	public function __toString()
	{
		return ! empty( $this->unit ) ? $this->value . $this->unit : $this->value;
	}
}
