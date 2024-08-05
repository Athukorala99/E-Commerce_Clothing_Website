<?php
namespace Blocksy;

class GutenbergBlock {
	protected $name;

	protected $uri;
	protected $widget_path;

	protected $config;

	public function __construct($name, $config) {
		$this->name = $name;
		$this->config = $config;

		$this->uri =
		get_template_directory_uri() . '/static/bundle/blocks/' .
			$this->name;

		$this->widget_path =
			get_template_directory() . '/inc/components/blocks/' . $name;

		add_action('init', [$this, 'register_gutenberg_block']);
		add_action('init', [$this, 'localize_data']);

		add_filter(
			'render_block',
			function ($block_content, $block) {
				if ($block['blockName'] === 'blocksy/' . $this->name) {
					wp_enqueue_style(
						'blocksy-block-' . $this->name . '-styles'
					);
				}

				return $block_content;
			},
			10,
			2
		);

		add_action('wp_enqueue_scripts', function () {
			$theme = blocksy_get_wp_parent_theme();

			$file_path = '/static/bundle/' .
				'theme-block-' .
				$this->name .
				'.min.css';

			if (! file_exists(get_template_directory() . $file_path)) {
				return;
			}

			wp_register_style(
				'blocksy-block-' . $this->name . '-styles',
				get_template_directory_uri() . $file_path,
				[],
				$theme->get('Version')
			);
		});
	}

	public function render($attributes) {
		$file_path = $this->widget_path . '/view.php';

		if (! file_exists($file_path)) {
			return '';
		}

		return blocksy_render_view($file_path, [
			'atts' => $attributes,
		]);
	}

	public function register_gutenberg_block() {
		call_user_func(
			'register_' . 'block_type',
			'blocksy/' . $this->name,
			[
				'attributes' => isset($this->config['attributes'])
				? $this->config['attributes']
				: [],
				'render_callback' => function ($attributes, $content) {
					if ($this->config['static']) {
						return $content;
					}

					return $this->render($attributes);
				},
				'editor_style_handles' => [
					'blocksy-theme-blocks-editor-styles',
				]
			]
		);
	}

	public function localize_data() {
		$options_file = $this->widget_path . '/options.php';

		if (!file_exists($options_file)) {
			return;
		}

		add_filter('blocksy:gutenberg-blocks-data', function ($data) use (
			$options_file
		) {
			$options = blocksy_akg(
				'options',
				blocksy_get_variables_from_file(
					$options_file,
					['options' => []]
				)
			);

			$options_name = str_replace('-', '_', $this->name);
			$data[$options_name] = $options;

			return $data;
		});
	}
}
