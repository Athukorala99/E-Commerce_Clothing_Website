<?php

namespace Blocksy;

class SingleProductAdditionalActions {
	public function __construct() {
		add_filter(
			'blocksy_woo_single_options_layers:extra',
			[$this, 'add_layer_options']
		);

		add_action(
			'blocksy:woocommerce:product:custom:layer',
			[$this, 'render']
		);
	}

	public function get_actions() {
		return apply_filters(
			'blocksy:woocommerce:single-product:additional-actions',
			[]
		);
	}

	public function add_layer_options($opt) {
		$actions_options = [];
		$actions_values = [];

		foreach ($this->get_actions() as $action) {
			$actions_options[$action['id']] = [
				'label' => $action['label']
			];

			if (isset($action['options'])) {
				$actions_options[$action['id']]['options'] = $action['options'];
			}

			$actions_values[] = [
				'id' => $action['id'],
				'enabled' => true,
			];
		}

		$opt['product_actions'] = [
			'label' => __('Additional Actions', 'blocksy'),
			'options' => [
				[
					'actions_type' => [
						'label' => __('Buttons Type', 'blocksy'),
						'type' => 'ct-radio',
						'value' => 'link',
						'view' => 'text',
						'design' => 'block',
						'choices' => [
							'link' => __('Link', 'blocksy'),
							'button' => __('Button', 'blocksy'),
						],
						'sync' => [
							'id' => 'woo_card_layout_skip'
						]
					],
				],

				[
					'woo_actions_layout' => [
						'label' => false,
						'type' => 'ct-layers',
						'manageable' => false,
						'itemClass' => 'ct-inner-layer',
						'sync' => [
							blocksy_sync_whole_page([
								'prefix' => 'woo_categories',
								'loader_selector' => '[data-products] > li'
							])
						],
						'value' => $actions_values,
						'settings' => $actions_options
					]
				],

				'label_visibility' => [
					'label' => __('Label Visibility', 'blocksy'),
					'type' => 'ct-visibility',
					'design' => 'block',
					'allow_empty' => true,
					'setting' => ['transport' => 'postMessage'],
					'sync' => [
						'id' => 'woo_card_layout_skip'
					],
					'value' => [
						'desktop' => true,
						'tablet' => true,
						'mobile' => false,
					],

					'choices' => blocksy_ordered_keys([
						'desktop' => __('Desktop', 'blocksy'),
						'tablet' => __('Tablet', 'blocksy'),
						'mobile' => __('Mobile', 'blocksy'),
					]),
				],

				'spacing' => [
					'label' => __('Bottom Spacing', 'blocksy'),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 100,
					'value' => 35,
					'responsive' => true,
					'sync' => [
						'id' => 'woo_card_layout_skip'
					]
				],
			]
		];

		return $opt;
	}

	public function render($layer) {
		if ($layer['id'] !== 'product_actions') {
			return;
		}

		$content = '';

		$actions_values = [];

		foreach ($this->get_actions() as $action) {
			$actions_values[] = [
				'id' => $action['id'],
				'enabled' => true,
			];
		}

		$label_visibility = blocksy_akg('label_visibility', $layer, [
			'desktop' => true,
			'tablet' => true,
			'mobile' => false,
		]);
		$layout = blocksy_akg('woo_actions_layout', $layer, $actions_values);

		if (empty($layout)) {
			return;
		}

		foreach($layout as $action) {
			if (! $action['enabled']) {
				continue;
			}

			$content = apply_filters(
				'blocksy:woocommerce:single-product:additional-actions:content:' . $action['id'],
				$content,
				array_merge(
					$action,
					[
						'label_visibility' => $label_visibility
					]
				)
			);
		}

		if (empty($content)) {
			return;
		}

		echo blocksy_html_tag(
			'div',
			[
				'class' => 'ct-product-additional-actions',
				'data-type' => blocksy_akg('actions_type', $layer, 'link')
			],
			$content
		);
	}
}

