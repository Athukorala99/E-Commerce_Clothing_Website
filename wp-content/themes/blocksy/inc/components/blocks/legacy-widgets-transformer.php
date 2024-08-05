<?php

namespace Blocksy;

class LegacyWidgetsTransformer {
	public function __construct() {
		$sidebars_widgets = get_option('sidebars_widgets', []);

		$did_change = false;

		foreach ($sidebars_widgets as $sidebar_id => $widgets_list) {
			if ($sidebar_id === 'wp_inactive_widgets') {
				continue;
			}

			if (! is_array($widgets_list)) {
				continue;
			}

			$new_widgets_descriptor = $this->migrate_sidebar($widgets_list);

			if ($new_widgets_descriptor['new_blocks_count'] === 0) {
				continue;
			}

			$did_change = true;
			$sidebars_widgets[$sidebar_id] = $new_widgets_descriptor[
				'new_sidebar'
			];
		}

		if ($did_change) {
			update_option('sidebars_widgets', $sidebars_widgets);
		}
	}

	public function migrate_sidebar($widgets_list) {
		$widget_block_data = get_option('widget_block');

		$new_blocks_count = 0;

		$new_sidebar = [];

		foreach ($widgets_list as $widget_id) {
			if (strpos($widget_id, 'blocksy_ct_') === 0) {
				$widget_prefix = explode('-', $widget_id)[0];
				$widget_numeric_id = explode('-', $widget_id)[1];

				$data = get_option('widget_' . $widget_prefix);

				if (! isset($data[$widget_numeric_id])) {
					continue;
				}

				$data = $data[$widget_numeric_id];

				$maybe_block_structure = $this->migrate_blocksy_widget_to_block(
					$widget_prefix,
					$data
				);

				if ($maybe_block_structure) {
					$new_widget_id = 1;

					$maybe_current_ids = array_filter(
						array_keys($widget_block_data),
						'is_numeric'
					);

					if (! empty($maybe_current_ids)) {
						$new_widget_id = array_keys($widget_block_data);
						$new_widget_id = max($maybe_current_ids) + 1;
					}

					$widget_block_data[$new_widget_id] = [
						'content' => $maybe_block_structure
					];

					$posts_widget_data = get_option('widget_' . $widget_prefix);

					unset($posts_widget_data[$widget_numeric_id]);

					update_option(
						'widget_' . $widget_prefix,
						$posts_widget_data
					);

					$new_blocks_count++;
					$new_sidebar[] = 'block-' . $new_widget_id;
					continue;
				}
			}

			$new_sidebar[] = $widget_id;
		}

		update_option('widget_block', $widget_block_data);

		return [
			'new_blocks_count' => $new_blocks_count,
			'new_sidebar' => $new_sidebar
		];
	}

	public function migrate_blocksy_widget_to_block($widget_id, $data) {
		if ($widget_id === 'blocksy_ct_posts') {
			$posts_migrator = new LegacyWidgetsPostsTransformer($data);
			return $posts_migrator->get_block();
		}

		// Non-posts widgets...

		if ($widget_id === 'blocksy_ct_about_me') {
			$about_me_migrator = new LegacyWidgetsAboutMeTransformer($data);
			return $about_me_migrator->get_block();
		}

		if ($widget_id === 'blocksy_ct_contact_info') {
			$contact_info_migrator = new LegacyWidgetsContactInfoTransformer($data);
			return $contact_info_migrator->get_block();
		}

		if ($widget_id === 'blocksy_ct_socials') {
			$socials_migrator = new LegacyWidgetsSocialsTransformer($data);
			return $socials_migrator->get_block();
		}

		if ($widget_id === 'blocksy_ct_advertisement') {
			$advertisement_migrator = new LegacyWidgetsAdvertisementTransformer($data);
			return $advertisement_migrator->get_block();
		}

		if ($widget_id === 'blocksy_ct_newsletter_subscribe') {
			$advertisement_migrator = new LegacyWidgetsNewsletterSubscribeTransformer($data);
			return $advertisement_migrator->get_block();
		}

		if ($widget_id === 'blocksy_ct_quote') {
			$quote_migrator = new LegacyWidgetsQuoteTransformer($data);
			return $quote_migrator->get_block();
		}

		return null;
	}
}
