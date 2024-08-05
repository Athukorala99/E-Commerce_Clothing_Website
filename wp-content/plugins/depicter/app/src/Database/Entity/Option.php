<?php
namespace Depicter\Database\Entity;

use TypeRocket\Models\Model;

class Option extends Model
{
	/**
	 * Resource name.
	 *
	 * @var string
	 */
	protected $resource = 'depicter_options';

	/**
	 * Determines what fields can be saved without be explicitly.
	 *
	 * @var array
	 */
	protected $fillable = [
        'option_name',
        'option_value'
    ];

	protected $guard = [
        'id'
    ];
}
