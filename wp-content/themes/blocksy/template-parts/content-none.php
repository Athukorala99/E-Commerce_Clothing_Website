<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Blocksy
 */

echo '<div class="entry-content">';

if (is_home() && current_user_can('publish_posts')) {
	printf(
		'<p>' . wp_kses(
			/* translators: 1: link to WP admin new post page open 2: link closing. */
			__('Ready to publish your first post? %1$sGet started here%2$s.', 'blocksy'),
			[
				'a' => [
					'href' => []
				]
			]
		) . '</p>',
		'<a href="' . esc_url(admin_url('post-new.php')) . '">',
		'</a>'
	);
} else {
	get_search_form();
}

echo '</div>';

