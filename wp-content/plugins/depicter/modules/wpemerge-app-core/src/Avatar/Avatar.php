<?php
/**
 * @package   WPEmergeAppCore
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2020 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmergeAppCore\Avatar;

use WP_Comment;

class Avatar {
	/**
	 * Default avatar attachment id.
	 *
	 * @var integer
	 */
	protected $default_avatar_id = 0;

	/**
	 * User meta keys that should be used as the avatar, in order.
	 *
	 * @var string[]
	 */
	protected $avatar_user_meta_keys = [];

	/**
	 * Bootstrap.
	 *
	 * @return void
	 */
	public function bootstrap() {
		add_filter( 'get_avatar_url', [$this, 'filterAvatar'], 10, 3 );
	}

	/**
	 * Set the default avatar to an attachment id.
	 *
	 * @param  integer $attachment_id
	 * @return void
	 */
	public function setDefault( $attachment_id ) {
		$this->default_avatar_id = intval( $attachment_id );
	}

	/**
	 * Add a meta key which should be checked for a valid attachment id
	 *
	 * @param  string $user_meta_key
	 * @return void
	 */
	public function addUserMetaKey( $user_meta_key ) {
		$this->avatar_user_meta_keys[] = strval( $user_meta_key );
	}

	/**
	 * Remove a previously added meta key
	 *
	 * @param  string $user_meta_key
	 * @return void
	 */
	public function removeUserMetaKey( $user_meta_key ) {
		$filter = function( $meta_key ) use ( $user_meta_key ) {
			return $meta_key !== $user_meta_key;
		};
		$this->avatar_user_meta_keys = array_filter( $this->avatar_user_meta_keys, $filter );
	}

	/**
	 * Converts an id_or_email to an ID if possible.
	 *
	 * @param  integer|string|WP_Comment $id_or_email
	 * @return integer|string
	 */
	protected function idOrEmailToId( $id_or_email ) {
		if ( is_a( $id_or_email, WP_Comment::class ) ) {
			return intval( $id_or_email->user_id );
		}

		if ( ! is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			if ( $user ) {
				return intval( $user->ID );
			}
		}

		return strval( $id_or_email );
	}

	/**
	 * Returns a size (name or [widget, height]) for the given avatar arguments.
	 *
	 * @param  array                 $arguments
	 * @return array<integer>|string
	 */
	protected function getSize( $arguments ) {
		$size = 'full';

		if ( ! empty( $arguments['width'] ) && ! empty( $arguments['height'] ) ) {
			$size = [ intval( $arguments['width'] ), intval( $arguments['height'] ) ];
		} elseif ( ! empty( $arguments['size'] ) ) {
			$size = [ intval( $arguments['size'] ), intval( $arguments['size'] ) ];
		}

		return $size;
	}

	/**
	 * Get attachment fallback chain for the user avatar.
	 *
	 * @param  integer        $user_id
	 * @return array<integer>
	 */
	protected function getAttachmentFallbackChain( $user_id ) {
		$chain = [];

		foreach ( $this->avatar_user_meta_keys as $user_meta_key ) {
			$attachment_id = get_user_meta( $user_id, $user_meta_key, true );
			if ( is_numeric( $attachment_id ) ) {
				$chain[] = intval( $attachment_id );
			}
		}

		if ( $this->default_avatar_id !== 0 ) {
			$chain[] = $this->default_avatar_id;
		}

		return $chain;
	}

	/**
	 * Get avatar url
	 *
	 * @param  integer               $id
	 * @param  array<integer>|string $size
	 * @return string|null
	 */
	protected function getAvatarUrl( $id, $size ) {
		$attachments_fallback_chain = $this->getAttachmentFallbackChain( $id );

		foreach ( $attachments_fallback_chain as $attachment_id ) {
			$image = wp_get_attachment_image_src( $attachment_id, $size );
			if ( ! empty( $image ) ) {
				return $image[0];
			}
		}

		return null;
	}

	/**
	 * Filter an avatar url based on the default avatar attachment id and registered meta keys.
	 *
	 * @param  string         $url
	 * @param  integer|string $id_or_email
	 * @param  array          $args
	 * @return string
	 */
	public function filterAvatar( $url, $id_or_email, $args ) {
		if ( ! empty( $args['force_default'] ) ) {
			return $url;
		}

		if ( $this->default_avatar_id === 0 && empty( $this->avatar_user_meta_keys ) ) {
			return $url;
		}

		$id = $this->idOrEmailToId( $id_or_email );

		if ( is_numeric( $id ) ) {
			$filtered_url = $this->getAvatarUrl( (int) $id, $this->getSize( $args ) );
			if ( $filtered_url !== null ) {
				$url = $filtered_url;
			}
		}

		return $url;
	}
}
