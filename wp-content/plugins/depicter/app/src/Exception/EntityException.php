<?php
namespace Depicter\Exception;


class EntityException extends \Exception
{
	private $where;

	public function __construct( $message = "", $code = 0, $where = [] )
	{
		parent::__construct( $message, $code, null );
		$this->where = $where;
	}

	public function getStatus(){
		return $this->getWhereCondition( 'status' );
	}

	public function getWhereCondition( $whereCondition ){
		if ( isset( $this->where[ $whereCondition ] ) ) {
			return $this->where[ $whereCondition ];
		}
		return null;
	}
}
