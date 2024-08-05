<?php
namespace Averta\Core\Utility;

/**
 * Class to generate Embed url
 *
 * https://regex101.com/r/6IhI2o/1
 */
class Embed{

	/**
	 * Converts YouTube url to embed url
	 *
	 * @param string $videoUrl    YouTube url
	 *
	 * @return bool|mixed  Returns the YouTube embed url on success, and false on failure.
	 */
	public static function getYouTubeEmbedUrl( $videoUrl ){
		if( $code = self::getYouTubeVimeoCode( $videoUrl ) ){
			return 'https://www.youtube.com/embed/' . $code;
		}

		return $code;
	}

    /**
	 * Converts YouTube url to poster image url
	 *
	 * @param string $videoUrl    YouTube url
	 *
	 * @return bool|mixed  Returns the YouTube poster image url on success, and false on failure.
	 */
	public static function getYouTubePosterUrl( $videoUrl ){
		if( $code = self::getYouTubeVimeoCode( $videoUrl ) ){
			return 'http://img.youtube.com/vi/' . $code. '/maxresdefault.jpg';
		}

		return $code;
	}

	/**
	 * Converts Vimeo url to embed url
	 *
	 * @param string $videoUrl    Vimeo url
	 *
	 * @return bool|mixed  Returns the Vimeo embed url on success, and false on failure.
	 */
	public static function getVimeoEmbedUrl( $videoUrl ){
		if( $code = self::getYouTubeVimeoCode( $videoUrl ) ){
			return 'https://player.vimeo.com/video/' . $code;
		}

		return $code;
	}

    /**
	 * Extracts and returns YouTube or Vimeo video ID from video url
	 *
	 * @param string $videoUrl    YouTube url
	 *
	 * @return bool|mixed  Returns the YouTube or Vimeo video code on success, and false on failure.
	 */
	public static function getYouTubeVimeoCode( $videoUrl ){
		$matches = $matches = static::getYouTubeVimeoMatches( $videoUrl );

        if( is_array( $matches ) ){
            return end( $matches );
        }
        return false;
	}

    /**
	 * Converts Youtube or Vimeo video url to embed url
	 *
	 * @param string $videoUrl    YouTube or Vimeo video url
	 *
	 * @return string  Returns Video embed url on success
	 */
	public static function getYouTubeVimeoEmbedUrl( $videoUrl ){
		$matches = static::getYouTubeVimeoMatches( $videoUrl );

        if( is_array( $matches ) ){
            if( !empty( $matches[3] ) ){
                if( in_array( $matches[3], ["youtube.com", "youtu.be"] ) ){
                    return 'https://www.youtube.com/embed/' . end( $matches );
                } elseif( $matches[3] === "vimeo.com" ){
                    return 'https://player.vimeo.com/video/' . end( $matches );
                }
            }
        }
        return '';
	}

    /**
     * Generates matches for a Youtube or Vimeo url
     *
     * @param string $videoUrl  Video url
     *
     * @return array|bool|mixed
     */
    public static function getYouTubeVimeoMatches( $videoUrl ){
        return Str::extractByRegex( $videoUrl, '/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\\&\S+)?/', -1 );
    }
}
