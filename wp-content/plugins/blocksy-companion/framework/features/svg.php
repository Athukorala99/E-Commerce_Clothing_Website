<?php

namespace Blocksy;

class SvgHandling {
	public function __construct() {
		add_filter(
			'wp_check_filetype_and_ext',
			function ($data = null, $file = null, $filename = null, $mimes = null) {
				if (strpos($filename, '.svg') !== false) {
					$data['type'] = 'image/svg+xml';
					$data['ext'] = 'svg';
				}

				return $data;
			},
			75, 4
		);

		add_filter('upload_mimes', function ($mimes) {
			$mimes['svg'] = 'image/svg+xml';
			return $mimes;
		});

		add_filter(
			'wp_get_attachment_image_src',
			function ($image, $attachment_id, $size, $icon) {
				if (! isset($attachment_id)) {
					return $image;
				}

				$mime = get_post_mime_type($attachment_id);

				if ('image/svg+xml' === $mime) {
					$default_height = 100;
					$default_width = 100;

					$maybe_file = get_attached_file($attachment_id);

					if ($maybe_file) {
						$dimensions = $this->svg_dimensions($maybe_file);

						if ($dimensions) {
							$default_height = $dimensions['height'];
							$default_width = $dimensions['width'];
						}
					}

					$image[2] = $default_height;
					$image[1] = $default_width;
				}

				return $image;
			},
			10, 4
		);
	}

	public function svg_dimensions($svg) {
		if (
			! preg_match('/.svg$/', $svg)
			||
			! file_exists($svg)
		) {
			return null;
		}

		$svg = file_get_contents($svg);

		$attributes = new \stdClass();

		if ($svg && function_exists('simplexml_load_string')) {
			$svg = @simplexml_load_string($svg);

			if ($svg) {
				$attributes = $svg->attributes();
			}
		}

		if (
			! isset($attributes->width)
			&&
			$svg
			&&
			function_exists('xml_parser_create')
		) {
			$xml = xml_parser_create('UTF-8');

			$svgData = new \stdClass();

			xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
			xml_set_element_handler(
				$xml,
				function ($parser, $name, $attrs) use (&$svgData) {
					if ($name === 'SVG') {
						if (isset($attrs['WIDTH'])) {
							$attrs['width'] = $attrs['WIDTH'];
						}

						if (isset($attrs['HEIGHT'])) {
							$attrs['height'] = $attrs['HEIGHT'];
						}

						if (isset($attrs['VIEWBOX'])) {
							$attrs['viewBox'] = $attrs['VIEWBOX'];
						}

						foreach ($attrs as $key => $value) {
							$svgData->{$key} = $value;
						}
					}
				},
				function ($parser, $tag) {
				}
			);

			if (xml_parse($xml, $svg, true)) {
				$attributes = $svgData;
			}

			xml_parser_free($xml);
		}


		$width = 0;
		$height = 0;

		if (empty($attributes)) {
			return false;
		}

		if (
			isset($attributes->width, $attributes->height)
			&&
			is_numeric($attributes->width)
			&&
			is_numeric($attributes->height)
		) {
			$width = floatval($attributes->width);
			$height = floatval($attributes->height);
		} elseif (isset($attributes->viewBox)) {
			$sizes = explode(' ', $attributes->viewBox);

			if (isset($sizes[2], $sizes[3])) {
				$width = floatval($sizes[2]);
				$height = floatval($sizes[3]);
			}
		} else {
			return false;
		}

		return [
			'width' => $width,
			'height' => $height,
			'orientation' => ($width > $height) ? 'landscape' : 'portrait'
		];
	}
}

