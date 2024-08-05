<?php
namespace Depicter\Document\Models\Options;

class Navigation
{
	/**
	 * @var mixed
	 */
	public $swipe;

	/**
	 * @var mixed
	 */
	public $keyboardNavigation;

	/**
	 * @var Toggleable
	 */
	public $autoShow;

	/**
	 * @var bool
	 */
	public $mouseWheel;

	/**
	 * @var bool
	 */
	public $pauseOnHover;

	/**
	 * @var Toggleable
	 */
	public $pauseOnLastSlide;

	/**
	 * @var bool
	 */
	public $loop;

	/**
	 * @var bool
	 */
	public $randomOrder;

	/**
	 * @var bool
	 */
	public $rtl;

	/**
	 * @var bool
	 */
	public $startOnAppear;

	/**
	 * @var mixed
	 */
	public $deeplink;

	/**
	 * @var object
	 */
	public $slideshow;
}
