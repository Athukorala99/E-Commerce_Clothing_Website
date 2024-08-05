<?php

namespace Averta\Core\Utility;

class Media{

    /**
	 * Calculates new width and height to fit the media in a box size
	 *
	 * @param string $type     Resize type. "cover" or "contain"
	 * @param int $boxWidth    Width of the box to fit the media in
	 * @param int $boxHeight   Height of the box to fit the media in
	 * @param int $width       Original width of the media
	 * @param int $height      Original height of the media
	 *
	 * @return float[]|int[]
	 */
	public static function fitInBox( $type, $boxWidth, $boxHeight, $width, $height ) {
		$widthRatio  = $width  ? $boxWidth  / $width  : 1;
		$heightRatio = $height ? $boxHeight / $height : 1;

		$ratio = $type === 'cover' ? max( $widthRatio, $heightRatio ) : min( $widthRatio, $heightRatio );

		return [
			$width  * $ratio,
			$height * $ratio,
		];
	}
}
