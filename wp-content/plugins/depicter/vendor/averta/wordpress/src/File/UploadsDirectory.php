<?php
namespace Averta\WordPress\File;


class UploadsDirectory
{
	/**
	 * Array of information about the upload directory.
	 *
	 * @var array
	 */
	private $uploads_directory;

	/**
	 * UploadsDirectory constructor.
	 *
	 * @param string|null $time          (Optional) Time formatted in 'yyyy/mm'. Default value: null
	 * @param bool        $create_dir    (Optional) Whether to check and create the uploads directory. Default true for backward compatibility.Default value: true
	 * @param bool        $refresh_cache (Optional) Whether to refresh the cache. Default value: false
	 */
	public function __construct( $time = null, $create_dir = true, $refresh_cache = false )
	{
		$this->uploads_directory = wp_upload_dir( $time, $create_dir, $refresh_cache );
	}

	/**
	 * Base directory and sub directory or full path to upload directory.
	 * @example C:\path\to\wordpress\wp-content\uploads\2010\05
	 *
	 * @return mixed
	 */
	public function getPath(){
		return $this->uploads_directory['path'];
	}

	/**
	 * Base url and sub directory or absolute URL to upload directory.
	 * @example http://example.com/wp-content/uploads/2010/05
	 *
	 * @return mixed
	 */
	public function getUrl(){
		return $this->uploads_directory['url'];
	}

	/**
	 * Path without subdir.
	 * @example C:\path\to\wordpress\wp-content\uploads
	 *
	 * @return mixed
	 */
	public function getBaseDirectory(){
		return $this->uploads_directory['basedir'];
	}

	/**
	 * URL path without subdir.
	 * @example http://example.com/wp-content/uploads
	 *
	 * @return mixed
	 */
	public function getBaseUrl(){
		return $this->uploads_directory['baseurl'];
	}

	/**
	 * Sub directory if uploads use year/month folders option is on.
	 * @example /2010/05
	 *
	 * @return mixed
	 */
	public function getSubDirectory(){
		return $this->uploads_directory['subdir'];
	}

}
