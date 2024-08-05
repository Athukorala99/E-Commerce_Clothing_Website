<?php
namespace Depicter\Media\Image;

use WP_Error;
use WP_Image_Editor;


class FileEdit extends File implements FileEditInterface
{
	/**
	 * The builtin editor
	 *
	 * @var \WP_Error|\WP_Image_Editor
	 */
	protected $editor;

	/**
	 * Crop starting point for x in pixels.
	 *
	 * @var int
	 */
	protected $x;

	/**
	 * Crop starting point for y in pixels.
	 *
	 * @var int
	 */
	protected $y;

	/**
	 * Determines if further process is not allowed
	 *
	 * @var bool
	 */
	protected $isPaused = false;


	/**
	 * Constructor.
	 *
	 * @param string $path  Path to the file or attachment id to load.
	 *
	 * @throws ImageEditorException
	 */
	public function __construct( $path ){
		parent::__construct( $path );
		$this->reset();
	}

	/**
	 * Loads image from $this->file into new editor.
	 *
	 * @throws ImageEditorException
	 */
	public function reset(){
		if( $this->getExtension() === 'svg' ){
			$this->isPaused = true;
			throw new ImageEditorException( 'SVG file provided' );
		}

		$this->editor = wp_get_image_editor( $this->file );
		if ( is_wp_error( $this->editor ) ) {
			throw new ImageEditorException( $this->editor->get_error_message() );
		}

		$this->orig_w = $this->getSize('width');
		$this->orig_h = $this->getSize('height');

	}

	/**
	 * Resize the current image.
	 *
	 * @param int  $width    Resize width
	 * @param int  $height   Resize height
	 * @param array $args    Resizing options
	 *
	 * @return $this
	 * @throws ImageEditorException
	 */
	public function resize( $width = null, $height = null, $args = [] ){
		if( $this->isPaused ){
			return $this;
		}

		$this->resized_w = $width;
		$this->resized_h = $height;

		if( !empty( $args['upscale'] ) ){
			add_filter( 'image_resize_dimensions', [ $this, 'allowUpscale' ], 10, 6 );
		}

		// check if upscale is false and resize with and height are greater than original sizes
		if ( empty( $args['upscale'] ) && $this->orig_w <= $width && $this->orig_h <= $height ) {
			return $this;
		}

		$resized = $this->editor->resize( $width, $height );
		if ( is_wp_error( $resized ) ) {
			throw new ImageEditorException( $resized->get_error_message() );
		}

		if( !empty( $args['upscale'] ) ){
			remove_filter( 'image_resize_dimensions', [ $this, 'allowUpscale' ] );
		}

		return $this;
	}

	/**
	 * Crops the images
	 *
	 * @param int   $width   Width to crop
	 * @param int   $height  Height to crop
	 * @param array $args    Cropping options
	 *
	 * @return $this
	 * @throws ImageEditorException
	 */
	public function crop( $width = null, $height = null, $args = [] ){
		if( $this->isPaused ){
			return $this;
		}

		$x = isset( $args['x'] ) ? $args['x'] : null;
		$y = isset( $args['y'] ) ? $args['y'] : null;

		if( isset( $args['focalX'] ) ){
			$this->focal[0] = $args['focalX'];
		}
		if( isset( $args['focalY'] ) ){
			$this->focal[1] = $args['focalY'];
		}

		$mediaWidth  = $this->getSize('width' );
		$mediaHeight = $this->getSize('height' );

		if( $width > $mediaWidth ){
			$width = $mediaWidth;
		}
		if( $height > $mediaHeight ){
			$height = $mediaHeight;
		}

		// check if crop size is equal to media size then stop the cropping process
		if ( $width == $mediaWidth && $height == $mediaHeight ) {
			return $this;
		}

		// if $x was not defined, calculate it based on focal point
		if( is_null( $x ) ){
			$x = $mediaWidth  * $this->focal[0] - $width  / 2;
		}
		$x = min($mediaWidth  - $width , max(0, $x) );

		// if $y was not defined, calculate it based on focal point
		if( is_null( $y ) ){
			$y = $mediaHeight * $this->focal[1] - $height / 2;
		}
		$y = min($mediaHeight - $height, max(0, $y) );

		$this->x = $x;
		$this->y = $y;

		$this->crop_w = $width;
		$this->crop_h = $height;

		$cropped = $this->editor->crop( $x, $y, (int) $width, (int) $height );
		if ( is_wp_error( $cropped ) ) {
			throw new ImageEditorException( $cropped->get_error_message() );
		}

		return $this;
	}

	/**
	 * Shorthand method for calling resize and crop methods.
	 *
	 * @param int  $resizeW  Resize width
	 * @param int  $resizeH  Resize height
	 * @param int  $cropW    Crop with
	 * @param int  $cropH    Crop height
	 * @param array $args    Resizing options
	 *
	 * @return $this
	 * @throws ImageEditorException
	 */
	public function process( $resizeW = null, $resizeH = null, $cropW = null, $cropH = null, $args = [] ){
		if( $this->isPaused ){
			return $this;
		}

		if( isset( $args['focalX'] ) ){
			$this->focal[0] = $args['focalX'];
		}
		if( isset( $args['focalY'] ) ){
			$this->focal[1] = $args['focalY'];
		}

		if( $resizeW || $resizeH ){

			$resized = $this->resize( $resizeW, $resizeH, $args );
			if ( is_wp_error( $resized ) ) {
				throw new ImageEditorException( $resized->get_error_message() );
			}
		}
		if( $cropW || $cropH ){
			$cropped = $this->crop( $cropW, $cropH, $args );
			if ( is_wp_error( $cropped ) ) {
				throw new ImageEditorException( $cropped->get_error_message() );
			}
		}

		return $this;
	}

	/**
	 * Sets Image Compression quality on a 1-100% scale.
	 *
	 * @param int $quality Compression Quality. Range: [1,100]
	 * @return true|\WP_Error  True if set successfully; WP_Error on failure.
	 */
	public function setQuality( $quality = null ){
		return $this->editor->set_quality( $quality );
	}

	/**
	 * Saves current image to file.
	 *
	 * @param null $filename
	 * @param string $mime_type
	 *
	 * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
	 */
	public function save( $filename = null, $mime_type = null ){
		if( $this->isPaused ){
			throw new ImageEditorException( __( 'File cannot be resized.' ) );
		}
		if( ! $filename ){
			$filename = $this->generateFilename();
		}
		return $this->editor->save( $filename, $mime_type );
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
			return $this->editor->get_size();
		}
		return !empty( $this->editor->get_size()[ $dimension ] ) ? $this->editor->get_size()[ $dimension ] : null;
	}

	/**
	 * Retrieves the WP_Image_Editor instance
	 *
	 * @return WP_Error|WP_Image_Editor
	 */
	public function editor(){
		return $this->editor;
	}

	/**
	 * Allows to scale up an image
	 *
	 * @param $default
	 * @param $orig_w
	 * @param $orig_h
	 * @param $dest_w
	 * @param $dest_h
	 * @param $crop
	 *
	 * @return array
	 */
	public function allowUpscale( $default, $orig_w, $orig_h, $dest_w, $dest_h, $crop ){
		// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
		return array( 0, 0, 0, 0, (int) $dest_w, (int) $dest_h, (int) $orig_w, (int) $orig_h );
	}

	/**
	 *
	 * @return string
	 */
	protected function getSuffix(){
		$suffix = $this->editor->get_suffix();
		$focalSuffix = $this->focalPointSuffix();

		return $focalSuffix . $suffix;
	}

	/**
	 * Builds an output filename based on current file, and adding proper suffix
	 *
	 * @since 3.5.0
	 *
	 * @param string $suffix
	 * @param string $dest_path
	 * @param string $extension
	 * @return string filename
	 */
	public function generateFilename( $suffix = null, $dest_path = null, $extension = null ) {
		// $suffix will be appended to the destination filename, just before the extension.
		if ( ! $suffix ) {
			$suffix = $this->getSuffix();
		}

		$dir = pathinfo( $this->file, PATHINFO_DIRNAME );
		$ext = pathinfo( $this->file, PATHINFO_EXTENSION );

		$name    = wp_basename( $this->file, ".$ext" );
		$new_ext = strtolower( $extension ? $extension : $ext );

		if ( ! is_null( $dest_path ) ) {
			if ( ! wp_is_stream( $dest_path ) ) {
				$_dest_path = realpath( $dest_path );
				if ( $_dest_path ) {
					$dir = $_dest_path;
				}
			} else {
				$dir = $dest_path;
			}
		}

		return trailingslashit( $dir ) . "{$name}-{$suffix}.{$new_ext}";
	}

}
