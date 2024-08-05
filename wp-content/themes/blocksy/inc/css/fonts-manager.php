<?php

namespace Blocksy;

class FontsManager {
	private $matching_fonts_collection = [];

	public function get_all_fonts() {
		return apply_filters('blocksy_typography_font_sources', [
			'system' => [
				'type' => 'system',
				'families' => $this->get_system_fonts(),
			],

			'google' => [
				'type' => 'google',
				'families' => $this->get_googgle_fonts()
				// 'families' => []
			]
		]);
	}

	public function process_matching_typography($value) {
		$family_to_push = 'Default';
		$variation_to_push = 'Default';

		if ($value && isset($value['family'])) {
			$family_to_push = $value['family'];
		}

		if ($value && isset($value['variation'])) {
			$variation_to_push = $value['variation'];
		}

		if (! isset($this->matching_fonts_collection[$family_to_push])) {
			$this->matching_fonts_collection[$family_to_push] = [$variation_to_push];
		} else {
			$this->matching_fonts_collection[$family_to_push][] = $variation_to_push;
		}

		$this->matching_fonts_collection[$family_to_push] = array_unique(
			$this->matching_fonts_collection[$family_to_push]
		);
	}

	public function get_matching_google_fonts() {
		$matching_fonts_collection = $this->matching_fonts_collection;

		$matching_google_fonts = [];

		$all_fonts = $this->get_system_fonts();

		$system_fonts_families = [];

		foreach ($all_fonts as $single_google_font) {
			$system_fonts_families[] = $single_google_font['family'];
		}

		$default_family = blocksy_get_theme_mod(
			'rootTypography',
			blocksy_typography_default_values([
				'family' => 'System Default',
				'variation' => 'n4',
				'size' => '16px',
				'line-height' => '1.65',
				'letter-spacing' => '0em',
				'text-transform' => 'none',
				'text-decoration' => 'none',
			])
		);

		$default_variation = $default_family['variation'];
		$default_family = $default_family['family'];

		$all_google_fonts = $this->get_googgle_fonts(true);

		foreach ($matching_fonts_collection as $family => $variations) {
			foreach ($variations as $variation) {
				$family_to_use = $family;
				$variation_to_use = $variation;

				if ($family_to_use === 'Default') {
					$family_to_use = $default_family;
				}

				if ($variation_to_use === 'Default') {
					$variation_to_use = $default_variation;
				}

				if (
					in_array($family_to_use, $system_fonts_families)
					||
					$family_to_use === 'Default'
					||
					! isset($all_google_fonts[$family_to_use])
				) {
					continue;
				}

				if (! isset($matching_google_fonts[$family_to_use])) {
					$matching_google_fonts[$family_to_use] = [$variation_to_use];
				} else {
					$matching_google_fonts[$family_to_use][] = $variation_to_use;
				}

				$matching_google_fonts[$family_to_use] = array_unique(
					$matching_google_fonts[$family_to_use]
				);
			}
		}

		return $matching_google_fonts;
	}

	public function load_dynamic_google_fonts($matching_google_fonts) {
		$has_dynamic_google_fonts = apply_filters(
			'blocksy:typography:google:use-remote',
			true
		);

		if (! $has_dynamic_google_fonts) {
			return;
		}

		$url = $this->get_google_fonts_url($matching_google_fonts);

		if (! empty($url)) {
			wp_register_style('blocksy-fonts-font-source-google', $url, [], null);
			wp_enqueue_style('blocksy-fonts-font-source-google');
		}
	}

	public function load_editor_fonts() {
		$has_dynamic_google_fonts = apply_filters(
			'blocksy:typography:google:use-remote',
			true
		);

		if (! $has_dynamic_google_fonts) {
			return '';
		}

		$dynamic_styles_descriptor = blocksy_manager()
			->dynamic_css
			->get_dynamic_styles_descriptor();

		$matching_google_fonts = $dynamic_styles_descriptor['google_fonts'];

		$url = $this->get_google_fonts_url($matching_google_fonts);

		return $url;
	}

	private function get_google_fonts_url($to_enqueue = []) {
		$endpoint = apply_filters(
			'blocksy:typography:google:endpoint',
			'https://fonts.googleapis.com/css2'
		);

		$url = $endpoint . '?';

		$families = [];

		foreach ($to_enqueue as $family => $variations) {
			$to_push = 'family=' . $family . ':';

			$ital_vars = [];
			$wght_vars = [];

			foreach ($variations as $variation) {
				$var_to_push = intval($variation[1]) * 100;
				$var_to_push .= $variation[0] === 'i' ? 'i' : '';

				if ($variation[0] === 'i') {
					$ital_vars[] = intval($variation[1]) * 100;
				} else {
					$wght_vars[] = intval($variation[1]) * 100;
				}
			}

			sort($ital_vars);
			sort($wght_vars);

			$axis_tag_list = [];

			if (count($ital_vars) > 0) {
				$axis_tag_list[] = 'ital';
			}

			$axis_tag_list[] = 'wght';

			$to_push .= implode(',', $axis_tag_list);
			$to_push .= '@';

			$all_vars = [];

			foreach ($wght_vars as $wght_var) {
				if (count($axis_tag_list) > 1) {
					$all_vars[] = '0,' . $wght_var;
				} else {
					$all_vars[] = $wght_var;
				}
			}

			foreach ($ital_vars as $ital_var) {
				$all_vars[] = '1,' . $ital_var;
			}

			$to_push .= implode(';', array_unique($all_vars));

			$families[] = $to_push;
		}

		$families = implode('&', $families);

		if (! empty($families)) {
			$url .= $families;
			$url .= '&display=swap';

			return $url;
		}

		return false;
	}

	public function get_system_fonts($as_string = false) {
		$system = [
			'System Default',
			'Arial', 'Verdana', 'Trebuchet', 'Georgia', 'Times New Roman',
			'Palatino', 'Helvetica', 'Myriad Pro',
			'Lucida', 'Gill Sans', 'Impact', 'Serif', 'monospace'
		];

		if ($as_string) {
			return $system;
		}

		$result = [];

		foreach ($system as $font) {
			$display = $font;

			if ($font === 'System Default') {
				$display = __('System Default', 'blocksy');
			}

			$result[] = [
				'source' => 'system',
				'family' => $font,
				'display' => $display,
				'variations' => [],
				'all_variations' => $this->get_standard_variations_descriptors()
			];
		}

		return $result;
	}

	public function get_standard_variations_descriptors() {
		return [
			'n1', 'i1', 'n2', 'i2', 'n3', 'i3', 'n4', 'i4', 'n5', 'i5', 'n6',
			'i6', 'n7', 'i7', 'n8', 'i8', 'n9', 'i9'
		];
	}

	public function retrieve_all_google_fonts() {
		$data = file_get_contents(
			get_template_directory() . '/static/fonts/google-fonts.json'
		);

		if (! $data) {
			return [];
		}

		return json_decode($data, true);
	}

	public function get_googgle_fonts($as_keys = false) {
		$maybe_custom_source = apply_filters(
			'blocksy-typography-google-fonts-source',
			null
		);

		if ($maybe_custom_source) {
			return $maybe_custom_source;
		}

		$response = $this->retrieve_all_google_fonts();

		if (! isset($response['items'])) {
			return false;
		}

		if (! is_array($response['items']) || !count($response['items'])) {
			return false;
		}

		foreach ($response['items'] as $key => $row) {
			$response['items'][$key] = $this->prepare_font_data($row);
		}

		if (! $as_keys) {
			return $response['items'];
		}

		$result = [];

		foreach ($response['items'] as $single_item) {
			$result[$single_item['family']] = true;
		}

		return $result;
	}

	private function prepare_font_data($font) {
		$font['source'] = 'google';

		$font['variations'] = [];

		if (isset($font['variants'])) {
			$font['all_variations'] = $this->change_variations_structure($font['variants']);
		}

		unset($font['variants']);
		return $font;
	}

	private function change_variations_structure( $structure ) {
		$result = [];

		foreach($structure as $weight) {
			$result[] = $this->get_weight_and_style_key($weight);
		}

		return $result;
	}

	private function get_weight_and_style_key($code) {
		$prefix = 'n'; // Font style: italic = `i`, regular = n.
		$sufix = '4';  // Font weight: 1 -> 9.

		$value = strtolower(trim($code));
		$value = str_replace(' ', '', $value);

		# Only number.
		if (is_numeric($value) && isset($value[0])) {
			$sufix = $value[0];
			$prefix = 'n';
		}

		// Italic.
		if (preg_match("#italic#", $value)) {
			if ('italic' === $value) {
				$sufix = 4;
				$prefix = 'i';
			} else {
				$value = trim(str_replace('italic', '', $value));
				if (is_numeric($value) && isset($value[0])) {
					$sufix = $value[0];
					$prefix = 'i';
				}
			}
		}

		// Regular.
		if (preg_match("#regular|normal#", $value)) {
			if ('regular' === $value) {
				$sufix = 4;
				$prefix = 'n';
			} else {
				$value = trim(str_replace(array('regular', 'normal') , '', $value));

				if (is_numeric($value) && isset($value[0])) {
					$sufix = $value[0];
					$prefix = 'n';
				}
			}
		}

		return "{$prefix}{$sufix}";
	}
}

