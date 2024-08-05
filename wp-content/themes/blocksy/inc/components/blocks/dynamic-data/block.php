<?php

namespace Blocksy\Blocks;

class DynamicData {
	public function __construct() {
		call_user_func(
			'register_' . 'block_type',
			get_template_directory() . '/static/js/editor/blocks/dynamic-data/block.json',
			[
				'render_callback' => [$this, 'render'],
			]
		);

		add_filter('blocksy:gutenberg-blocks-data', function ($data) {
			$options = blocksy_akg(
				'options',
				blocksy_get_variables_from_file(
					dirname(__FILE__) . '/options.php',
					['options' => []]
				)
			);

			$options_name = 'dynamic-data';
			$data[$options_name] = $options;

			return $data;
		});

		add_action('rest_api_init', function() {
			register_rest_field('attachment', 'has_video', array(
				'get_callback' => function ($post, $field_name, $request) {
					if ($post['type'] !== 'attachment') {
						return null;
					}

					$maybe_new_video = blocksy_get_post_options($post['id']);
					$media_video_source = blocksy_akg('media_video_source', $maybe_new_video, 'upload');

					$video_url = '';

					if ($media_video_source === 'upload') {
						$video_url = blocksy_akg('media_video_upload', $maybe_new_video, '');
					}

					if ($media_video_source === 'youtube') {
						$video_url = blocksy_akg('media_video_youtube_url', $maybe_new_video, '');
					}

					if ($media_video_source === 'vimeo') {
						$video_url = blocksy_akg('media_video_vimeo_url', $maybe_new_video, '');
					}

					if (! empty($video_url)) {
						return true;
					}

					return false;
				}
			));
		});

		add_action(
			'wp_ajax_blocksy_blocks_retrieve_dynamic_data_descriptor',
			function () {
				if (! current_user_can('manage_options')) {
					wp_send_json_error();
				}

				$data = json_decode(file_get_contents('php://input'), true);

				if (! $data || ! isset($data['post_id'])) {
					wp_send_json_error();
				}

				if (! function_exists('blc_get_ext')) {
					wp_send_json_error();
				}

				$post_type = get_post_type($data['post_id']);

				$providers = [];

				if (
					function_exists('blc_get_ext')
					&&
					blc_get_ext('post-types-extra')
					&&
					blc_get_ext('post-types-extra')->dynamic_data
				) {
					$providers = [
						'acf',
						'metabox',
						'custom',
						'toolset',
						'jetengine',
						'pods'
					];
				}

				$fields = [];

				foreach ($providers as $provider) {
					$maybe_fields = blc_get_ext('post-types-extra')
						->dynamic_data
						->retrieve_dynamic_data_fields([
							'post_type' => $post_type,
							'provider' => $provider,
							'allow_images' => true
						]);

					if (empty($maybe_fields)) {
						continue;
					}

					$result = [];

					foreach ($maybe_fields as $field => $label) {
						$field_render = blc_get_ext('post-types-extra')
							->dynamic_data
							->get_field_to_render(
								[
									'id' => $provider . '_field',
									'field' => $field,
								],
								[
									'post_type' => $post_type,
									'post_id' => $data['post_id'],
									'allow_images' => true
								]
							);

						if (! $field_render) {
							continue;
						}

						$field_type = 'text';

						if (
							is_array($field_render['value'])
							&&
							isset($field_render['value']['type'])
						) {
							if ($field_render['value']['type'] === 'image') {
								$field_type = 'image';
							}
						}

						$result[] = [
							'id' => $field,
							'label' => $label,
							'type' => $field_type
						];
					}

					$fields[] = [
						'provider' => $provider,
						'fields' => $result
					];
				}

				wp_send_json_success(apply_filters(
					'blocksy:general:blocks:dynamic-data:data',
					[
						'post_id' => $data['post_id'],
						'post_type' => $post_type,
						'fields' => $fields,
						'dynamic_styles' => $this->get_dynamic_styles_for()
					]
				));
			}
		);

		add_action(
			'wp_ajax_blocksy_dynamic_data_block_custom_field_data',
			function () {
				if (! current_user_can('manage_options')) {
					wp_send_json_error();
				}

				$data = json_decode(file_get_contents('php://input'), true);

				if (
					! $data
					||
					! isset($data['post_id'])
					||
					! isset($data['field_provider'])
					||
					! isset($data['field_id'])
				) {
					wp_send_json_error();
				}

				$maybe_ext = blc_get_ext('post-types-extra');

				if (! $maybe_ext || ! $maybe_ext->dynamic_data) {
					wp_send_json_error();
				}

				$post_type = get_post_type($data['post_id']);

				$field_render = blc_get_ext('post-types-extra')
					->dynamic_data
					->get_field_to_render(
						[
							'id' => $data['field_provider'] . '_field',
							'field' => $data['field_id'],
						],
						[
							'post_type' => $post_type,
							'post_id' => $data['post_id'],
							'allow_images' => true
						]
					);

				wp_send_json_success([
					'post_id' => $data['post_id'],
					'post_type' => $post_type,
					'field_data' => $field_render['value']
				]);
			}
		);
	}

	public function render($attributes) {

		if (
			isset($attributes['lightbox'])
			&&
			$attributes['lightbox'] === 'yes'
			&&
			wp_script_is('wp-block-image-view', 'registered')
		) {
			wp_enqueue_script('wp-block-image-view');
		}

		return blocksy_render_view(
			dirname(__FILE__) . '/view.php',
			[
				'attributes' => $attributes,
				'block_instance' => $this
			]
		);
	}

	public function get_dynamic_styles_for() {
		if (
			! function_exists('blc_get_ext')
			||
			! blc_get_ext('post-types-extra')
			||
			! blc_get_ext('post-types-extra')->taxonomies_customization
		) {
			return '';
		}

		$styles = [
			'desktop' => '',
			'tablet' => '',
			'mobile' => ''
		];

		$css = new \Blocksy_Css_Injector();
		$tablet_css = new \Blocksy_Css_Injector();
		$mobile_css = new \Blocksy_Css_Injector();

		blc_get_ext('post-types-extra')
			->taxonomies_customization
			->get_terms_dynamic_styles([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'context' => 'global',
				'chunk' => 'global'
			]);

		$styles['desktop'] .= $css->build_css_structure();
		$styles['tablet'] .= $tablet_css->build_css_structure();
		$styles['mobile'] .= $mobile_css->build_css_structure();

		$final_css = '';

		if (! empty($styles['desktop'])) {
			$final_css .= $styles['desktop'];
		}

		if (! empty(trim($styles['tablet']))) {
			$final_css .= '@media (max-width: 999.98px) {' . $styles['tablet'] . '}';
		}

		if (! empty(trim($styles['mobile']))) {
			$final_css .= '@media (max-width: 689.98px) {' . $styles['mobile'] . '}';
		}

		return $final_css;
	}
}


