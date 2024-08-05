<?php

namespace Blocksy\DbVersioning;

class V209 {
	public function migrate() {
		$this->migrate_contacts_in_header();
		$this->migrate_contacts_in_footer();
	}

	public function migrate_contacts_in_header() {
		$header_placements = get_theme_mod('header_placements', []);

		if (empty($header_placements)) {
			return;
		}

		$made_changes = false;

		foreach ($header_placements['sections'] as $section_index => $single_section) {
			foreach ($single_section['items'] as $item_index => $single_item) {
				if ($single_item['id'] !== 'contacts') {
					continue;
				}

				if (isset($single_item['values']['contacts_items_direction'])) {
					$old_val = $single_item['values']['contacts_items_direction'];

					if ($old_val === 'vertical') {
						$old_val = 'column';
						$made_changes = true;
					}

					if ($old_val === 'horizontal') {
						$old_val = 'row';
						$made_changes = true;
					}


					if ($made_changes) {
						$header_placements['sections'][
							$section_index
						]['items'][$item_index]['values'][
							'contacts_items_direction'
						] = $old_val;
					}
				}
			}
		}

		if ($made_changes) {
			set_theme_mod('header_placements', $header_placements);
		}
	}

	public function migrate_contacts_in_footer() {
		$footer_placements = get_theme_mod('footer_placements', []);

		if (empty($footer_placements)) {
			return;
		}

		$made_changes = false;

		foreach ($footer_placements['sections'] as $section_index => $single_section) {
			foreach ($single_section['items'] as $item_index => $single_item) {
				if ($single_item['id'] !== 'contacts') {
					continue;
				}

				if (isset($single_item['values']['contacts_items_direction'])) {
					$old_val = $single_item['values']['contacts_items_direction'];

					if ($old_val === 'vertical') {
						$old_val = 'column';
						$made_changes = true;
					}

					if ($old_val === 'horizontal') {
						$old_val = 'row';
						$made_changes = true;
					}


					if ($made_changes) {
						$footer_placements['sections'][
							$section_index
						]['items'][$item_index]['values'][
							'contacts_items_direction'
						] = $old_val;
					}
				}
			}
		}

		if ($made_changes) {
			set_theme_mod('footer_placements', $footer_placements);
		}
	}
}

