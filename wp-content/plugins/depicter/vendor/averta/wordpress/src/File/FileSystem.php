<?php

namespace Averta\WordPress\File;


class FileSystem
{

    protected $wpFilesystem;


    public function __construct()
    {
		global $wp_filesystem;

		if ( ! function_exists( 'get_filesystem_method' ) || empty( $wp_filesystem ) ) {
			require_once ( ABSPATH.'/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		$this->wpFilesystem = $wp_filesystem;
    }

    /**
     * Validates filesystem credentials.
     */
    public function validate( $url = null )
    {
        if ( get_filesystem_method() === 'direct' ) {}
		return true;
    }

    /**
     * Reads a file if exists.
	 *
     * @param string $filename File name.
     *
     * @return string
     */
    public function read( $filename )
    {
        if ( ! $this->validate() ){
			return false;
		}
        return $this->wpFilesystem->get_contents( $filename );
    }


    /**
     * Creates and stores content in a file
     *
     * @param string $file_location The address that we plan to create the file in.
     * @param string $content The content for writing in the file
     * @param int    $chmod
     *
     * @return boolean            Returns true if the file is created and updated successfully, false on failure
     */
    public function write( $file_location = '', $content = '', $chmod = 0644 )
    {
        if ( ! $this->validate() ){
			return false;
		}

		$_chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : $chmod;
		// Write the content, if possible
		if ( wp_mkdir_p( dirname( $file_location ) ) && ! $this->wpFilesystem->put_contents( $file_location, $content, $_chmod ) ) {
			// If writing the content in the file was not successful
			return false;
		} else {
			return true;
		}
    }

    /**
     * Whether the file/path exists or not.
     *
     * @param string $file   File name or file path.
     *
     * @return bool
     */
    public function exists( $file )
    {
        if ( ! $this->validate() ){
			return false;
		}

        return $this->wpFilesystem->exists( $file );
    }

    /**
     * Whether the path is a file or not.
     *
     * @return bool
     */
    public function isFile( $file )
    {
    	if ( ! $this->validate() ){
			return false;
		}
        return $this->wpFilesystem->is_file( $file );
    }

    /**
     * Whether the path is a directory or not.
     *
     * @return bool
     */
    public function isDir( $path )
    {
        if ( ! $this->validate() ){
			return false;
		}

		return $this->wpFilesystem->is_dir( $path );
    }

    /**
     * Creates a folder path recursively.
     *
     * @param string $path Path  Full path to directory to create
     * @param bool   $recursive  Whether to create directory recursively or not
     *
     * @return bool
     */
    public function mkdir( $path, $recursive = false )
    {
        if ( ! $this->validate() ){
			return false;
		}

        if( $recursive ){
            return wp_mkdir_p( $path );
        }

        return $this->wpFilesystem->mkdir( $path );
    }

    /**
     * Removes folder path and contents.
     *
     * @return bool
     * @global $wp_filesytem
     * @since 0.9.0
     *
     */
    public function rmdir( $path, $recursive = false )
    {
        if ( ! $this->validate() ){
            return false;
        }

        return $this->wpFilesystem->rmdir( $path, $recursive );
    }

    /**
	 * Gets details for files in a directory or a specific file.
	 *
	 *
	 * @param string $path           Path to directory or file.
	 * @param bool   $include_hidden Optional. Whether to include details of hidden ("." prefixed) files.
	 *                               Default true.
	 * @param bool   $recursive      Optional. Whether to recursively include file details in nested directories.
	 *                               Default false.
	 * @return array|false {
	 *     Array of files. False if unable to list directory contents.
	 *
	 *     @type string $name        Name of the file or directory.
	 *     @type string $perms       *nix representation of permissions.
	 *     @type string $permsn      Octal representation of permissions.
	 *     @type string $owner       Owner name or ID.
	 *     @type int    $size        Size of file in bytes.
	 *     @type int    $lastmodunix Last modified unix timestamp.
	 *     @type mixed  $lastmod     Last modified month (3 letter) and day (without leading 0).
	 *     @type int    $time        Last modified time.
	 *     @type string $type        Type of resource. 'f' for file, 'd' for directory.
	 *     @type mixed  $files       If a directory and `$recursive` is true, contains another array of files.
	 * }
	 */
	public function scan( $path, $include_hidden = true, $recursive = false ) {
        if ( ! $this->validate() ){
			return false;
		}

		return $this->wpFilesystem->dirlist( $path, $include_hidden, $recursive );
    }

    /**
     * Copy files from source to destination
     *
     * @param string $source
     * @param string $destination
     * @param false  $overwrite
     * @param false  $mode
     *
     * @return bool True on success, false on failure.
     */
    public function copy( $source, $destination, $overwrite = false, $mode = false ) {
        if ( ! $this->validate() ){
            return false;
        }

        return $this->wpFilesystem->copy( $source, $destination, $overwrite, $mode );
    }

    /**
     * Move files from source to destination
     *
     * @param string $source
     * @param string $destination
     * @param false  $overwrite
     *
     * @return bool True on success, false on failure.
     */
    public function move( $source, $destination, $overwrite = false ) {
        if ( ! $this->validate() ){
            return false;
        }

        return $this->wpFilesystem->move( $source, $destination, $overwrite );
    }

    /**
     * Gets the file modification time.
     *
     * @param string $file Path to file.
     *
     * @return mixed
     */
    public function mtime( $file ) {
        return $this->wpFilesystem->mtime( $file );
    }

    /**
     * Retrieves FileSystem instance.
     *
     * @return mixed
     */
    public function proxy(){
        return $this->wpFilesystem;
    }
}
