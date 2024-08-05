<?php

if (! function_exists('blocksy_has_comments')) {
	function blocksy_has_comments() {
		$prefix = blocksy_manager()->screen->get_prefix();

		$has_comments = blocksy_get_theme_mod($prefix . '_has_comments', 'yes');

		if ($has_comments === 'yes') {
			return comments_open() || get_comments_number();
		}

		return false;
	}
}

if (! function_exists('blocksy_display_page_elements')) {
function blocksy_display_page_elements($location = null) {
	$prefix = blocksy_manager()->screen->get_prefix();

	$has_related_posts = blocksy_get_theme_mod(
		$prefix . '_has_related_posts',
		'no'
	) === 'yes' && (
		blocksy_default_akg(
			'disable_related_posts',
			blocksy_get_post_options(),
			'no'
		) !== 'yes'
	);

	$has_comments = blocksy_get_theme_mod($prefix . '_has_comments', 'yes');

	$related_posts_location = blocksy_get_theme_mod(
		$prefix . '_related_posts_containment',
		'separated'
	);

	$comments_location = null;

	if ($has_comments === 'yes') {
		$comments_location = blocksy_get_theme_mod(
			$prefix . '_comments_containment',
			'separated'
		);
	}

	ob_start();

	if ($has_related_posts && $related_posts_location === $location) {
		do_action('blocksy:single:related_posts:before');
		blocksy_related_posts($location);
		do_action('blocksy:single:related_posts:after');
	}

	$related_posts_output = ob_get_clean();

	if (
		(
			blocksy_get_theme_mod($prefix . '_related_location', 'before') === 'before'
			||
			$comments_location !== $related_posts_location
		) && $has_related_posts && $related_posts_location === $location
	) {
		/**
		 * Note to code reviewers: This line doesn't need to be escaped.
		 * The var $related_posts_output used here escapes the value properly.
		 */
		echo $related_posts_output;
	}

	$container_class = 'ct-container';

	if (
		blocksy_get_theme_mod(
			$prefix . '_comments_structure',
			'narrow'
		) === 'narrow'
	) {
		$container_class = 'ct-container-narrow';
	}

	if (
		$has_comments === 'yes'
		&&
		$comments_location === $location
		&&
		(comments_open() || get_comments_number())
	) {
		if ($location === 'separated') {
			echo '<div class="ct-comments-container">';
			echo '<div class="' . $container_class . '">';
		}

		comments_template();

		if ($location === 'separated') {
			echo '</div>';
			echo '</div>';
		}
	}

	if (
		blocksy_get_theme_mod($prefix . '_related_location', 'before') === 'after'
		&&
		$comments_location === $related_posts_location
		&&
		$has_related_posts
		&&
		$related_posts_location === $location
	) {
		/**
		 * Note to code reviewers: This line doesn't need to be escaped.
		 * The var $related_posts_output used here escapes the value properly.
		 */
		echo $related_posts_output;
	}
}
}

if (! function_exists('blocksy_action_button')) {
	function blocksy_action_button($attributes = []) {
		$attributes = wp_parse_args(
			$attributes,
			[
				'button_html_attributes' => [],
				'icon' => '',
				'icon_position' => 'start', // start | end
				'content' => '',
				'done_state' => false,
			]
		);

		$loading_icon = '<svg class="ct-button-loader" width="18" height="18"  viewBox="0 0 24 24">
			<circle cx="12" cy="12" r="10" opacity="0.2" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="2.5"/>

			<path d="m12,2c5.52,0,10,4.48,10,10" fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2.5">
				<animateTransform
					attributeName="transform"
					attributeType="XML"
					type="rotate"
					dur="0.5s"
					from="0 12 12"
					to="360 12 12"
					repeatCount="indefinite" />
			</path>
		</svg>';

		$done_icon = '<svg class="ct-done" width="20" height="20" viewBox="0,0,512,512">
				<path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path>
			</svg>';

		$icon = blocksy_html_tag(
			'span',
			[
				'class' => 'ct-icon-container'
			],
			$attributes['icon'] .
			$loading_icon .
			($attributes['done_state'] ? $done_icon : '')
		);

		$content = $attributes['content'];

		if ( $attributes['icon_position'] === 'start' ) {
			$content = $icon . $content;
		} else {
			$content .= $icon;
		}

		return blocksy_html_tag(
			'a',
			array_merge(
				$attributes['button_html_attributes'],
				[
					'data-button-state' => blocksy_akg(
						'data-button-state',
						$attributes['button_html_attributes'],
						''
					)
				]
			),
			$content
		);
	}
}
