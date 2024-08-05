<?php
namespace Depicter\Html;

use Averta\WordPress\Utility\JSON;
use TypeRocket\Html\Tag as TypeRocketTag;

class Tag extends TypeRocketTag
{

	const ALLOWED_EMPTY_ATTRIBUTES = ['alt'];

	/**
     * Html constructor.
     *
     * @param string $tag
     * @param array|null $attributes
     * @param string|Tag|Html|array|null $nest
     */
    public function __construct( $tag, $attributes = null, $nest = null)
    {
        parent::__construct( $tag, $attributes, $nest );

        // Adding 'meta', 'link' and 'source' to close tags list
        if( in_array( $this->tag, ['img', 'br', 'hr', 'input', 'meta', 'link', 'source'] ) ) {
            $this->closed = true;
        }

        return $this;
    }

	/**
	 * Get the opening tag in string form
	 *
	 * @return string
	 */
	public function open() {
		$openTag = "<{$this->tag}";

		foreach( $this->attr as $attribute => $value ) {
			if ( JSON::isJson( $value ) ) {
				$value = htmlentities( str_replace( '"', "'", $value ), ENT_NOQUOTES, 'UTF-8');
			} elseif( in_array( $attribute, ['href'] ) ) {
				$value = esc_url_raw( $value );
			} elseif ( ! in_array( $attribute, ['data-src', 'src', 'data-srcset', 'onload'] ) ) {
				$value = trim( esc_attr( $value ) );
			}

			if( in_array( $attribute, static::ALLOWED_EMPTY_ATTRIBUTES ) && empty( $value ) ) {
				$value = '=""';
			} else {
				$value = $value !== '' ? "=\"{$value}\"" : '';
			}

			$openTag .= " {$attribute}{$value}";
		}

		$openTag .= $this->closed ? " />" : ">";

		return $openTag;
	}

}
