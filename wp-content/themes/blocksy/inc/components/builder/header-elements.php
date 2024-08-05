<?php

class Blocksy_Header_Builder_Elements {
	private $current_section_id = null;

	public function __construct($args = []) {
		$args = wp_parse_args($args, [
			'current_section_id' => null
		]);

		$this->current_section_id = $args['current_section_id'];
	}

	public function render_offcanvas($args = []) {
		$args = wp_parse_args($args, [
			'has_container' => true,
			'device' => 'mobile'
		]);

		$render = new Blocksy_Header_Builder_Render([
			'current_section_id' => $this->current_section_id
		]);

		if (! $render->contains_item('trigger')) {
			if (! is_customize_preview()) {
				return '';
			}
		}

		$mobile_content = '';
		$desktop_content = '';

		$current_layout = $render->get_current_section()['mobile'];

		foreach ($current_layout as $row) {
			if ($row['id'] !== 'offcanvas') {
				continue;
			}

			if ($render->is_row_empty($row)) {
				// return '';
			}

			$mobile_content .= $render->render_items_collection(
				$row['placements'][0]['items'],
				[
					'device' => 'mobile'
				]
			);
		}

		$current_layout = $render->get_current_section()['desktop'];

		foreach ($current_layout as $row) {
			if ($row['id'] !== 'offcanvas') {
				continue;
			}

			if (! empty($desktop_content)) {
				continue;
			}

			$desktop_content = $render->render_items_collection(
				$row['placements'][0]['items']
			);
		}

		$atts = $render->get_item_data_for('offcanvas');
		$row_config = $render->get_item_config_for('offcanvas');

		$class = 'ct-panel ct-header';
		$behavior = 'modal';

		$position_output = [];

		if (blocksy_default_akg('offcanvas_behavior', $atts, 'panel') !== 'modal') {
			$behavior = blocksy_default_akg(
				'side_panel_position', $atts, 'right'
			) . '-side';
		}

		ob_start();
		do_action('blocksy:header:offcanvas:desktop:top');
		$desktop_content = ob_get_clean() . $desktop_content;

		ob_start();
		do_action('blocksy:header:offcanvas:desktop:bottom');
		$desktop_content = $desktop_content . ob_get_clean();

		ob_start();
		do_action('blocksy:header:offcanvas:mobile:top');
		$mobile_content = ob_get_clean() . $mobile_content;

		ob_start();
		do_action('blocksy:header:offcanvas:mobile:bottom');
		$mobile_content = $mobile_content . ob_get_clean();

		$without_container = blocksy_html_tag(
			'div',
			array_merge(
				[
					'class' => 'ct-panel-content',
					'data-device' => 'desktop'
				],
				is_customize_preview() ? [
					'data-item-label' => $row_config['config']['name'],
					'data-location' => $render->get_customizer_location_for('offcanvas')
				] : []
			),
			'<div class="ct-panel-content-inner">' . $desktop_content . '</div>'
		) . blocksy_html_tag(
			'div',
			array_merge(
				[
					'class' => 'ct-panel-content',
					'data-device' => 'mobile'
				],
				is_customize_preview() ? [
					'data-item-label' => $row_config['config']['name'],
					'data-location' => $render->get_customizer_location_for('offcanvas')
				] : []
			),
			'<div class="ct-panel-content-inner">' . $mobile_content . '</div>'
		);

		$close_type = blocksy_akg('menu_close_button_type', $atts, 'type-1');

		$main_offcanvas_close_icon = apply_filters(
			'blocksy:main:offcanvas:close:icon',
			'<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15"><path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/></svg>'
		);

		$heading = '';

		if (blocksy_akg('has_offcanvas_heading', $atts, 'no') === 'yes') {
			$heading = '<span class="ct-panel-heading">' . blocksy_akg('offcanvas_heading', $atts, __( 'Menu', 'blocksy' )) . '</span>';
		}

		$without_container = '
		<div class="ct-panel-actions">
			'. $heading .'
			<button class="ct-toggle-close" data-type="' . $close_type . '" aria-label="'. __('Close drawer', 'blocksy') . '">
				'. $main_offcanvas_close_icon . '
			</button>
		</div>
		' .  $without_container;

		if (blocksy_default_akg(
			'offcanvas_behavior',
			$atts,
			'panel'
		) === 'panel') {
			$without_container = '<div class="ct-panel-inner">' . $without_container . '</div>';
		}

		if (! $args['has_container']) {
			return $without_container;
		}

		return blocksy_html_tag(
			'div',
			array_merge(
				[
					'id' => 'offcanvas',
					'class' => $class,
					'data-behaviour' => $behavior
					// ,
					// 'data-device' => $args['device']
				],
				$position_output
			),
			$without_container
		);
	}

	public function render_search_modal() {
		$render = new Blocksy_Header_Builder_Render([
			'current_section_id' => $this->current_section_id
		]);

		if (! $render->contains_item('search')) {
			return;
		}

		$atts = $render->get_item_data_for('search');

		$search_through = blocksy_akg('search_through', $atts, [
			'post' => true,
			'page' => true,
			'product' => true
		]);

		$section_id = $render->get_current_section_id();
		$key = 'header:' . $section_id . ':search:header_search_placeholder';

		$search_placeholder = blocksy_translate_dynamic(
			blocksy_akg(
				'header_search_placeholder',
				$atts,
				__('Search', 'blocksy')
			),
			$key
		);

		$search_close_button_type = blocksy_akg(
			'search_close_button_type',
			$atts,
			'type-1'
		);

		$all_cpts = blocksy_manager()->post_types->get_supported_post_types();

		if (function_exists('is_bbpress')) {
			$all_cpts[] = 'forum';
			$all_cpts[] = 'topic';
			$all_cpts[] = 'reply';
		}

		foreach ($all_cpts as $single_cpt) {
			if (! isset($search_through[$single_cpt])) {
				$search_through[$single_cpt] = true;
			}
		}

		$post_type = [];

		foreach ($search_through as $single_post_type => $enabled) {
			if (
				! $enabled
				||
				! get_post_type_object($single_post_type)
			) {
				continue;
			}

			if (
				$single_post_type !== 'post'
				&&
				$single_post_type !== 'page'
				&&
				$single_post_type !== 'product'
				&&
				! in_array($single_post_type, $all_cpts)
			) {
				continue;
			}

			$post_type[] = $single_post_type;
		}

		if (count(array_keys($search_through)) === count($post_type)) {
			$post_type = [];
		}

		$search_modal_close_icon = apply_filters(
			'blocksy:search:modal:close:icon',
			'<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15"><path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/></svg>'
		);

		$search_form_args = [
			'enable_search_field_class' => true,
			'ct_post_type' => $post_type,
			'search_placeholder' => $search_placeholder,
			'search_live_results' => 'no',

			'override_html_atts' => [],
			'button_type' => 'icon'
		];

		if (blocksy_akg('enable_live_results', $atts, 'yes') === 'yes') {
			$search_form_args['search_live_results'] = 'yes';

			$search_form_args['live_results_attr'] = blocksy_akg(
				'searchHeaderImages', $atts, 'yes'
			) === 'yes' ? 'thumbs' : '';

			$search_form_args['ct_product_price'] = blocksy_akg(
				'searchHeaderProductPrice', $atts, 'no'
			) === 'yes';

			$search_form_args['ct_product_status'] = blocksy_akg(
				'searchHeaderProductStatus', $atts, 'no'
			) === 'yes';
		}


		?>

		<div id="search-modal" class="ct-panel" data-behaviour="modal">
			<div class="ct-panel-actions">
				<button class="ct-toggle-close" data-type="<?php echo $search_close_button_type ?>" aria-label="<?php echo __('Close search modal', 'blocksy') ?>">
					<?php echo $search_modal_close_icon ?>
				</button>
			</div>

			<div class="ct-panel-content">
				<?php blocksy_isolated_get_search_form($search_form_args); ?>
			</div>
		</div>

		<?php
	}
}
