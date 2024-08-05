<?php
namespace Depicter\Media;

/**
 * Media File Class
 *
 * @package Depicter\Media
 */
class File
{
	/**
	 * Original width in pixels.
	 *
	 * @var int
	 */
	protected $orig_w;

	/**
	 * Original height in pixels.
	 *
	 * @var int
	 */
	protected $orig_h;


	/**
	 * Path to the file to load
	 *
	 * @var string
	 */
	protected $file = null;

	/**
	 * Information about the file path
	 *
	 * @var string
	 */
	protected $fileInfo = [];

	/**
	 * Constructor.
	 *
	 * @param string $path  Path to the file or attachment id to load.
	 */
	public function __construct( $path ){
		if( is_numeric( $path ) ){
			$path = get_attached_file( $path );
		}

		$this->file = $path;
	}

	/**
	 * Sets initial values
	 */
	public function reset(){
		$this->orig_w = $this->getSize('width');
		$this->orig_h = $this->getSize('height');
	}

	/**
	 * Get current image size
	 *
	 * @param string $dimension
	 *
	 * @return array|null
	 */
	public function getSize( $dimension = null ){
		if( ! $dimension ){
			return getimagesize( $this->file );
		}
		list( $width, $height, $type, $attr ) = getimagesize( $this->file );

		if( isset( $$dimension ) ){
			return $$dimension;
		}
		return null;
	}

	/**
	 * Retrieves information about a file path
	 *
	 * @param string $param  Key of file info
	 *
	 * @return array|string
	 */
	public function getFileInfo( $param = null ){
		if( empty( $this->fileInfo ) ){
			$this->fileInfo = pathinfo( $this->file );
		}
		if( $param && !empty( $this->fileInfo[ $param ] ) ){
			return $this->fileInfo[ $param ];
		}
		return $this->fileInfo;
	}

	/**
	 * Retrieves the file extension
	 *
	 * @return string
	 */
	public function getExtension(){
		if( ! empty( $this->getFileInfo('extension' ) ) ){
			return $this->getFileInfo('extension');
		}
		return '';
	}
}

