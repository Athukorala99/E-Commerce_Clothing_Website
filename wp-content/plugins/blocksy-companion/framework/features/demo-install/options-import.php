<?php

namespace Blocksy;

class DemoInstallOptionsInstaller {
	protected $has_streaming = true;
	protected $demo_name = null;

	protected $sideloaded_images = [];

	public function __construct($args = []) {
		$args = wp_parse_args($args, [
			'has_streaming' => true,
			'demo_name' => null
		]);

		if (
			!$args['demo_name']
			&&
			isset($_REQUEST['demo_name'])
			&&
			$_REQUEST['demo_name']
		) {
			$args['demo_name'] = $_REQUEST['demo_name'];
		}

		$this->has_streaming = $args['has_streaming'];
		$this->demo_name = $args['demo_name'];
	}

	public function import() {
		if ($this->has_streaming) {
			Plugin::instance()->demo->start_streaming();

			if (! current_user_can('edit_theme_options')) {
				Plugin::instance()->demo->emit_sse_message([
					'action' => 'complete',
					'error' => 'No permission.',
				]);

				exit;
			}

			if (! $this->demo_name) {
				Plugin::instance()->demo->emit_sse_message([
					'action' => 'complete',
					'error' => 'No demo name passed.',
				]);
				exit;
			}
		}

		$demo_name = explode(':', $this->demo_name);

		if (! isset($demo_name[1])) {
			$demo_name[1] = '';
		}

		$demo = $demo_name[0];
		$builder = $demo_name[1];

		if ($this->has_streaming) {
			Plugin::instance()->demo->emit_sse_message([
				'action' => 'download_demo_options',
				'error' => false,
			]);
		}

		$demo_content = Plugin::instance()->demo->fetch_single_demo([
			'demo' => $demo,
			'builder' => $builder,
			'field' => 'options'
		]);

		if (! isset($demo_content['options']) && $this->has_streaming) {
			Plugin::instance()->demo->emit_sse_message([
				'action' => 'complete',
				'error' => __('Downloaded demo is corrupted.'),
			]);

			exit;
		}

		$options = $demo_content['options'];
		$this->import_options($options, $demo_content);

		if ($this->has_streaming) {
			Plugin::instance()->demo->emit_sse_message([
				'action' => 'complete',
				'error' => false,
			]);

			exit;
		}
	}

	public function import_options($options, $demo_content = null) {
		if ($this->has_streaming) {
			Plugin::instance()->demo->emit_sse_message([
				'action' => 'import_mods_images',
				'error' => false,
			]);
		}

		if ($demo_content) {
			$options['mods'] = $this->import_images(
				$demo_content,
				$options['mods']
			);
		}

		global $wp_customize;

		if ($this->has_streaming) {
			Plugin::instance()->demo->emit_sse_message([
				'action' => 'import_customizer_options',
				'error' => false,
			]);
		}

		do_action('customize_save', $wp_customize);

		foreach ($options['mods'] as $key => $val) {
			if ($key === 'sidebars_widgets') continue;
			if ($key === 'custom_css_post_id') continue;
			do_action('customize_save_' . $key, $wp_customize);
			set_theme_mod($key, $val);
		}

		do_action('customize_save_after', $wp_customize);

		foreach ($options['options'] as $key => $val) {
			if ($key === 'blocksy_active_extensions') {
				if ($val && is_array($val)) {
					if ($this->has_streaming) {
						Plugin::instance()->demo->emit_sse_message([
							'action' => 'activate_required_extensions',
							'error' => false,
						]);
					}

					foreach ($val as $single_extension) {
						Plugin::instance()->extensions->activate_extension(
							$single_extension
						);
					}
				}
			} else {
				if (
					strpos($key, 'woocommerce') !== false
					&&
					$key !== 'woocommerce_thumbnail_cropping'
				) {
					add_option($key, $val);
					update_option($key, $val);
				} else {
					update_option($key, $val);
				}
			}
		}

		/*
		$all = get_option('sidebars_widgets');
		$all['sidebar-1'] = [];
		update_option('sidebars_widgets', $all);

		$all = blocksy_get_theme_mod('sidebars_widgets');

		if ($all) {
			$all['data']['sidebar-1'] = [];
			set_theme_mod('sidebars_widgets', $all);
		}
		 */

		if (
			class_exists('\FluentForm\App\Hooks\Handlers\ActivationHandler')
			&&
			isset($options['fluent_form_forms'])
		) {
			$fluentFormActivation = new \FluentForm\App\Hooks\Handlers\ActivationHandler();
			$fluentFormActivation->migrate();

			$forms = $options['fluent_form_forms'];

			$insertedForms = [];

			if ($forms && is_array($forms)) {
				foreach ($forms as $formItem) {
					$formFields = json_encode([]);

					if ($fields = \FluentForm\Framework\Support\Arr::get($formItem, 'form', '')) {
						$formFields = json_encode($fields);
					} elseif ($fields = \FluentForm\Framework\Support\Arr::get($formItem, 'form_fields', '')) {
						$formFields = json_encode($fields);
					} else {
					}

					$form = [
						'title'       => \FluentForm\Framework\Support\Arr::get($formItem, 'title'),
						'form_fields' => $formFields,
						'status'      => \FluentForm\Framework\Support\Arr::get($formItem, 'status', 'published'),
						'has_payment' => \FluentForm\Framework\Support\Arr::get($formItem, 'has_payment', 0),
						'type'        => \FluentForm\Framework\Support\Arr::get($formItem, 'type', 'form'),
						'created_by'  => get_current_user_id(),
					];

					if (\FluentForm\Framework\Support\Arr::get($formItem, 'conditions')) {
						$form['conditions'] = \FluentForm\Framework\Support\Arr::get($formItem, 'conditions');
					}

					if (isset($formItem['appearance_settings'])) {
						$form['appearance_settings'] = \FluentForm\Framework\Support\Arr::get($formItem, 'appearance_settings');
					}

					$formId = \FluentForm\App\Models\Form::insertGetId($form);
					$insertedForms[$formId] = [
						'title'    => $form['title'],
						'edit_url' => admin_url('admin.php?page=fluent_forms&route=editor&form_id=' . $formId),
					];

					if (isset($formItem['metas'])) {
						foreach ($formItem['metas'] as $metaData) {
							$metaKey = \FluentForm\Framework\Support\Arr::get($metaData, 'meta_key');
							$metaValue = \FluentForm\Framework\Support\Arr::get($metaData, 'value');
							if ("ffc_form_settings_generated_css" == $metaKey || "ffc_form_settings_meta" == $metaKey) {
								$metaValue = str_replace('ff_conv_app_' . \FluentForm\Framework\Support\Arr::get($formItem, 'id'), 'ff_conv_app_' . $formId, $metaValue);
							}
							$settings = [
								'form_id'  => $formId,
								'meta_key' => $metaKey,
								'value'    => $metaValue,
							];
							\FluentForm\App\Models\FormMeta::insert($settings);
						}
					} else {
						$oldKeys = [
							'formSettings',
							'notifications',
							'mailchimp_feeds',
							'slack',
						];
						foreach ($oldKeys as $key) {
							if (isset($formItem[$key])) {
								\FluentForm\App\Models\FormMeta::persist($formId, $key, json_encode(\FluentForm\Framework\Support\Arr::get($formItem, $key)));
							}
						}
					}
					do_action_deprecated(
						'fluentform_form_imported',
						[
							$formId
						],
						FLUENTFORM_FRAMEWORK_UPGRADE,
						'fluentform/form_imported',
						'Use fluentform/form_imported instead of fluentform_form_imported.'
					);
					do_action('fluentform/form_imported', $formId);
				}
			}
		}

		if (
			function_exists('wc_get_attribute_taxonomies')
			&&
			isset($options['woocommerce_attribute_taxonomies'])
		) {
			$current = wc_get_attribute_taxonomies();

			foreach ($options['woocommerce_attribute_taxonomies'] as $attr) {
				$found = false;

				foreach (array_values($current) as $current_attr) {
					if ($current_attr->attribute_name === $attr['attribute_name']) {
						$found = true;
						break;
					}
				}

				if (! $found) {
					wc_create_attribute([
						'name' => $attr['attribute_label'],
						'slug' => $attr['attribute_name'],
						'type' => $attr['attribute_type'],
						'order_by' => $attr['attribute_orderby'],
						'has_archives' => !! $attr['attribute_public']
					]);
				}
			}
		}

		if (
			function_exists('wp_update_custom_css_post')
			&&
			isset($options['wp_css'])
			&&
			$options['wp_css']
		) {
			wp_update_custom_css_post($options['wp_css']);
		}

		/**
		 * Temporary work around until Elementor comes up with something better
		 */
		if (class_exists('\Elementor\Plugin')) {
			$default_post_id = \Elementor\Plugin::$instance->kits_manager->get_active_id();

			if (
				! empty($default_post_id)
				&&
				isset($options['elementor_active_kit_settings'])
				&&
				! empty($options['elementor_active_kit_settings'])
			) {
				update_post_meta(
					$default_post_id,
					'_elementor_page_settings',
					$options['elementor_active_kit_settings']
				);
			}
		}
	}

	private function import_images($demo_content, $mods) {
		foreach ($mods as $key => $val) {
			if ($this->is_image_url($val)) {
				$data = $this->sideload_image($val);

				if (! is_wp_error($data)) {
					$mods[$key] = $data->url;

					// Handle header image controls.
					if (isset($mods[$key . '_data'])) {
						$mods[$key . '_data'] = $data;

						update_post_meta(
							$data->attachment_id,
							'_wp_attachment_is_custom_header',
							get_stylesheet()
						);
					}
				}
			}

			if ($key === 'header_placements') {
				foreach ($val['sections'] as $section_index => $section) {
					foreach ($section['items'] as $item_index => $item) {
						$mods['header_placements']['sections'][$section_index][
							'items'
						][$item_index]['values'] = $this->import_images(
							$demo_content,
							$item['values']
						);
					}
				}
			}

			if ($key === 'custom_logo' && is_array($val) && isset($val['desktop'])) {
				$maybe_url = $this->sideload_image_for_url(
					$demo_content['url'],
					$val['desktop']
				);

				if ($maybe_url) {
					$data = $this->sideload_image($maybe_url);
					$mods[$key]['desktop'] = $data->attachment_id;
				}

				$maybe_url = $this->sideload_image_for_url(
					$demo_content['url'],
					$val['tablet']
				);

				if ($maybe_url) {
					$data = $this->sideload_image($maybe_url);
					$mods[$key]['tablet'] = $data->attachment_id;
				}

				$maybe_url = $this->sideload_image_for_url(
					$demo_content['url'],
					$val['mobile']
				);

				if ($maybe_url) {
					$data = $this->sideload_image($maybe_url);
					$mods[$key]['mobile'] = $data->attachment_id;
				}
			}

			if ($key === 'custom_logo' && is_numeric($val)) {
				$maybe_url = $this->sideload_image_for_url(
					$demo_content['url'],
					$val
				);

				if ($maybe_url) {
					$data = $this->sideload_image($maybe_url);
					$mods[$key] = $data->attachment_id;
				}
			}

			if (
				is_array($val)
				&&
				isset($val['attachment_id'])
				&&
				$val['attachment_id']
			) {
				$maybe_url = $this->sideload_image_for_url(
					$demo_content['url'],
					$val['attachment_id']
				);

				if ($maybe_url) {
					$data = $this->sideload_image($maybe_url);
					$mods[$key]['attachment_id'] = $data->attachment_id;
				}
			}
		}

		return $mods;
	}

	public function sideload_image_for_url($url, $id) {
		$url = rtrim($url,"/") . '/wp-json/wp/v2/media/' . $id;

		$request = wp_remote_get($url);

		if (is_wp_error($request)) {
			return false;
		}

		$body = wp_remote_retrieve_body($request);

		$body = json_decode($body, true);

		if (! $body) {
			return false;
		}

		if (! isset($body['source_url'])) {
			return false;
		}

		return $body['source_url'];
	}

	private function sideload_image($file) {
		if (isset($this->sideloaded_images[$file])) {
			return $this->sideloaded_images[$file];
		}

		$data = new \stdClass();

		if (! function_exists('media_handle_sideload')) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		if (empty($file)) {
			return $data;
		}

		// Set variables for storage, fix file filename for query strings.
		preg_match('/[^\?]+\.(jpe?g|jpe|gif|png|svg|webp)\b/i', $file, $matches);

		$file_array = [];

		$file_array['name'] = basename($matches[0]);

		// Download file to temp location.
		$file_array['tmp_name'] = download_url($file);

		// If error storing temporarily, return the error.
		if (is_wp_error($file_array['tmp_name'])) {
			return $file_array['tmp_name'];
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload($file_array, 0);

		// If error storing permanently, unlink.
		if (is_wp_error($id)) {
			@unlink($file_array['tmp_name']);
			return $id;
		}

		update_post_meta($id, 'blocksy_demos_imported_post', true);

		// Build the object to return.
		$meta = wp_get_attachment_metadata($id);
		$data->attachment_id = $id;
		$data->url = wp_get_attachment_url($id);
		$data->thumbnail_url = wp_get_attachment_thumb_url($id);

		if (
			isset($file_array['extension'])
			&&
			'svg' === $file_array['extension']
		) {
			$dimensions = Plugin::instance()
				->theme_integration
				->svg_dimensions($file);

			$data->width = (int) $dimensions->width;
			$data->height = (int) $dimensions->height;
		} else {
			if (
				$meta
				&&
				is_array($meta)
				&&
				isset($meta['height'])
				&&
				isset($meta['width'])
			) {
				$data->height = $meta['height'];
				$data->width = $meta['width'];
			}
		}

		$this->sideloaded_images[$file] = $data;

		return $data;
	}

	private function is_image_url($string = '') {
		if (is_string($string)) {
			if (preg_match('/\.(jpg|jpeg|png|gif|svg|webp)/i', $string)) {
				return true;
			}
		}

		return false;
	}
}


