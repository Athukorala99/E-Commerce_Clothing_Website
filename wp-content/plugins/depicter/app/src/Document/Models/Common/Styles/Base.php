<?php
namespace Depicter\Document\Models\Common\Styles;

use Depicter\Document\CSS\Breakpoints;
use Depicter\Document\Models\Traits\CSSPropertiesTrait;

class Base
{

	use CSSPropertiesTrait;
	
	/**
	 * @var array
	 */
	public $variations = [];

	public $enable;

	/**
	 * @var array
	 */
	protected $breakpoint_dictionary = [];

	public function __construct()
	{
		$this->breakpoint_dictionary = Breakpoints::all();
	}

	public function set( $name, $value )
	{
		return $this->variations[ $name ] = $value;
	}
}
