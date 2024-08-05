<?php
namespace Depicter\Database\Entity;

use TypeRocket\Models\Model;

class Meta extends Model
{
	/**
	 * Resource name.
	 *
	 * @var string
	 */
	protected $resource = 'depicter_meta';

	/**
	 * Determines what fields can be saved without be explicitly.
	 *
	 * @var array
	 */
	protected $builtin = [ 'relation',
		'relation_id',
		'meta_key',
		'meta_value' ];

	protected $guard = [ 'id' ];
}
