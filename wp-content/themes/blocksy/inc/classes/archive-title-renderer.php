<?php

namespace Blocksy;

class ArchiveTitleRenderer {
	private $has_label = false;

	public function __construct($args = []) {
		$args = wp_parse_args($args, [
			'has_label' => false
		]);

		$this->has_label = $args['has_label'];
	}

	public function render_title($title, $original_title, $prefix) {
		if (! $this->has_label) {
			return $original_title;
		}

		return blocksy_html_tag(
			'span',
			[
				'class' => 'ct-title-label'
			],
			rtrim(trim($prefix), ':')
		) . ' ' . $original_title;
	}
}
