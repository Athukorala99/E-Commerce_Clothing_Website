<?php

if (! function_exists('blocksy_get_video_data')) {
	function blocksy_get_video_data($attachment_id) {
		$maybe_video = [];

		if (! $attachment_id) {
			return $maybe_video;
		}

		$maybe_new_video = blocksy_get_post_options($attachment_id);

		if ($maybe_new_video) {
			$source = blocksy_akg('media_video_source', $maybe_new_video, 'upload');
			$maybe_video = $maybe_new_video;

			if ($source === 'upload') {
				$video_url = wp_get_attachment_url(
					blocksy_akg('media_video_upload', $maybe_new_video, '')
				);

				if (! empty($video_url)) {
					$maybe_video['url'] = $video_url;
				}
			}

			if ($source === 'youtube') {
				$video_url = blocksy_akg(
					'media_video_youtube_url',
					$maybe_new_video,
					''
				);

				if ( ! empty($video_url) ) {
					if (
						strpos($video_url, 'youtube') !== false
						||
						strpos($video_url, 'youtu.be') !== false
					) {
						$maybe_video['url'] = $video_url;
					}
				}
			}

			if ($source === 'vimeo') {
				$video_url = blocksy_akg(
					'media_video_vimeo_url',
					$maybe_new_video,
					''
				);

				if ( ! empty($video_url) ) {
					if (
						strpos($video_url, 'vimeo') !== false
					) {
						$maybe_video['url'] = $video_url;
					}
				}
			}

			return $maybe_video;
		}

		$maybe_old_video = get_post_meta(
			$attachment_id,
			'blocksy_media_video',
			true
		);

		if (! empty($maybe_old_video)) {
			$maybe_video = [
				'url' => $maybe_old_video
			];
		}

		return $maybe_video;
	}
}

if (! function_exists('blocksy_has_video_element')) {
	function blocksy_has_video_element($args) {
		$parser = new Blocksy_Attributes_Parser();

		$play_icon = '<span class="ct-video-indicator">
			<svg width="40" height="40" viewBox="0 0 40 40" fill="#fff">
				<path class="ct-play-path" d="M20,0C8.9,0,0,8.9,0,20s8.9,20,20,20s20-9,20-20S31,0,20,0z M16,29V11l12,9L16,29z"/>

				<path class="ct-pause-path" d="M20 0C8.9 0 0 8.9 0 20s8.9 20 20 20 20-9 20-20S31 0 20 0zm-2.3 28h-4.6V12h4.6v16zm9.2 0h-4.6V12h4.6v16z"/>

				<path class="ct-video-loader" fill="currentColor" opacity="0.2" d="M20,11c-5,0-9,4-9,9c0,5,4,9,9,9s9-4,9-9C29,15,25,11,20,11z M20,27c-3.9,0-7-3.1-7-7c0-3.9,3.1-7,7-7s7,3.1,7,7C27,23.9,23.9,27,20,27z"/>

				<path class="ct-video-loader" fill="currentColor" d="M23.5,13.9l1-1.7C23.1,11.4,21.6,11,20,11v2C21.3,13,22.5,13.3,23.5,13.9z">
					<animateTransform attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.6s" repeatCount="indefinite"/>
				</path>
			</svg>
		</span>';

		if (! $args['display_video']) {
			return null;
		}

		$video_data = blocksy_get_video_data($args['attachment_id']);

		if (
			! isset($video_data['url'])
			||
			empty($video_data['url'])
		) {
			return null;
		}

		return array_merge(
			$video_data,
			[
				'icon' => $play_icon,
			]
		);
	}
}

add_action(
	'wp_ajax_blocksy_get_image_video_component',
	'blocksy_get_via_request_image_video_component'
);

add_action(
	'wp_ajax_nopriv_blocksy_get_image_video_component',
	'blocksy_get_via_request_image_video_component'
);
function blocksy_get_via_request_image_video_component() {
	if (! isset($_GET['media'])) {
		wp_send_json_error();
	}

	$args = [];

	if (isset($_GET['ignore_video_options'])) {
		$args['ignore_video_options'] = true;
	}

	$result = blocksy_get_image_video_component($_GET['media'], $args);

	if (empty($result)) {
		wp_send_json_error();
	}

	wp_send_json_success([
		'html' => $result
	]);

	return;
}

function blocksy_get_image_video_component($media_id, $args = []) {
	$args = wp_parse_args(
		$args,
		[
			'ignore_video_options' => false,
		]
	);

	if (! isset($media_id)) {
		return '';
	}

	$video_data = blocksy_get_video_data($media_id);

	if (
		! isset($video_data['url'])
		||
		empty($video_data['url'])
	) {
		return '';
	}

	$use_simple_player = blocksy_akg('media_video_player', $video_data, 'no') === 'yes';
	$use_autoplay = blocksy_akg('media_video_autoplay', $video_data, 'no') === 'yes';
	$nocookies_mode = blocksy_akg('media_video_youtube_nocookies', $video_data, 'no') === 'yes';

	if ($args['ignore_video_options']) {
		$use_autoplay = false;
		$use_simple_player = false;
	}

	preg_match(
		'#^(http|https)://.+\.(mp4|MP4|mpeg4)(?=\?|$)#i',
		$video_data['url'],
		$matches
	);

	$media_video_loop = blocksy_akg('media_video_loop', $video_data, 'no');

	if (isset($matches[0]) && ! empty($matches[0])) {
		$poster = wp_get_attachment_url($media_id);

		$video_attr = [
			'poster' => $poster,
			'controls' => !$use_simple_player,
			'playsinline' => 1
		];

		$video_attr['autoplay'] = $use_autoplay;
		$video_attr['muted'] = $use_autoplay;
		$video_attr['loop'] = $media_video_loop === 'yes' ? 1 : 0;

		$result = blocksy_html_tag(
			'video',
			$video_attr,
			blocksy_html_tag(
				'source',
				[
					'src' => $video_data['url'],
					'type' => 'video/mp4',
				],
				false
			)
		);

		return $result;
	}

	$embed = wp_oembed_get($video_data['url']);

	$additional_parmas = [
		'autoplay' => $use_autoplay ? 1 : 0,
		'controls' => ! $use_simple_player ? 1 : 0,
		'loop' => $media_video_loop === 'yes' ? 1 : 0,
		'playsinline' => 1,
	];

	if (
		strpos($video_data['url'], 'youtube') !== false
		||
		strpos($video_data['url'], 'youtu.be') !== false
	) {
		if ($media_video_loop === 'yes') {
			$id = explode('?', $video_data['url'])[1];
			$id = str_replace('v=', '', array_reverse(explode('/', $id))[0]);

			$additional_parmas['playlist'] = $id;
		}

		$additional_parmas = array_merge(
			$additional_parmas,
			[
				'enablejsapi' => 1,
				'version' => 3,
				'playerapiid' => 'ytplayer',
				'mute' => $use_autoplay ? 1 : 0
			]
		);

		if ($nocookies_mode) {
			$embed = str_replace( 'youtube.com/embed', 'youtube-nocookie.com/embed', $embed );
		}
	}

	if ( strpos($video_data['url'], 'vimeo') !== false ) {
		$additional_parmas = array_merge(
			$additional_parmas,
			[
				'autopause' => 0,
				'background' => $use_simple_player ? 1 : 0,
				'muted' => $use_autoplay ? 1 : 0,
				'transparent' => 0
			]
		);
	}

	$src_param = [];

	preg_match('/src=["](.*?)["]/', $embed, $src_param);

	if (isset($src_param[1]) && ! empty($src_param[1])) {
		$embed = str_replace(
			$src_param[1],
			add_query_arg(
				$additional_parmas,
				$src_param[1]
			),
			$embed
		);
	}

	return $embed;
}

