<?php

namespace Blocksy\DbVersioning;

class V203 {
	public function migrate() {
		$prefixes = array_merge(
			blocksy_manager()->screen->get_single_prefixes([
				'has_bbpress' => true,
				'has_buddy_press' => true,
				'has_woocommerce' => true
			]),

			blocksy_manager()->screen->get_archive_prefixes([
				'has_woocommerce' => true,
				'has_categories' => true,
				'has_author' => true,
				'has_search' => true
			])
		);

		foreach ($prefixes as $prefix) {
			blocksy_manager()->db_versioning->migrate_options_values([
				[
					'id' => $prefix . '_hero_alignment2',
					'migrate' => [
						[
							'old' => 'left',
							'new' => 'start'
						],

						[
							'old' => 'right',
							'new' => 'end'
						]
					]
				]
			]);
		}
	}
}

