<?php

if ( !function_exists('blocksy_custom_accordion_tabs') ) {
	function blocksy_custom_accordion_tabs() {
		$is_open = blocksy_get_theme_mod('woo_accordion_closed_by_default', 'yes') === 'yes';
		$additional_attr = blocksy_get_theme_mod('woo_accordion_close_prev', 'yes') === 'yes' ? 'data-close-others' : '';

		$tabs = apply_filters( 'woocommerce_product_tabs', [] );
		$index = 0;

		if ( ! empty( $tabs ) ) : ?>
			<div class="woocommerce-tabs wc-tabs-wrapper">

				<?php foreach ( $tabs as $key => $tab ) : ?>
					<div class="ct-accordion-tab">
						<button
							class="ct-accordion-heading ct-expandable-trigger"
							data-target="#tab-<?php echo esc_attr( $key ); ?>"
							aria-expanded="<?php echo $is_open && !$index ? "true" : "false" ?>"
							<?php echo $additional_attr; ?>
							>
							<?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?>

							<?php
								echo blocksy_html_tag(
									'span',
									[],
									'<svg width="12" height="12" viewBox="0 0 20 20">
										<path fill="currentColor" class="plus-line" d="M10,20c-0.6,0-1-0.4-1-1V1c0-0.6,0.4-1,1-1s1,0.4,1,1v18C11,19.6,10.6,20,10,20z"/>
										<path fill="currentColor" d="M19,11H1c-0.6,0-1-0.4-1-1s0.4-1,1-1h18c0.6,0,1,0.4,1,1S19.6,11,19,11z"/>
									</svg>'
								);
							?>
						</button>

						<article id="tab-<?php echo esc_attr( $key ); ?>" aria-hidden="<?php echo $is_open && !$index ? "false" : "true" ?>" data-behaviour="drop-down">
							<div class="entry-content">
								<?php call_user_func( $tab['callback'], $key, $tab ); ?>
							</div>
						</article>
					</div>
				<?php
					$index++;
					endforeach;
				?>

			</div>
		<?php endif;
	}
}

if ( !function_exists('blocksy_custom_simple_tabs') ) {
	function blocksy_custom_simple_tabs() {
		$tabs = apply_filters( 'woocommerce_product_tabs', [] );

		if ( ! empty( $tabs ) ) : ?>
			<div class="woocommerce-tabs wc-tabs-wrapper">
				<article>
					<?php foreach ( $tabs as $key => $tab ) : ?>
						<div class="entry-content">
							<?php call_user_func( $tab['callback'], $key, $tab ); ?>
						</div>
					<?php endforeach; ?>
				</article>
			</div>
		<?php endif;
	}
}

if (! function_exists('woocommerce_output_product_data_tabs')) {
	function woocommerce_output_product_data_tabs()  {
		if (! blocksy_manager()->screen->uses_woo_default_template()) {
			wc_get_template( 'single-product/tabs/tabs.php' );
			return;
		}
		$result = '';
		$res = blocksy_get_theme_mod('woo_tabs_type', 'type-1');

		if (
			$res === 'type-1'
			||
			$res === 'type-2'
		) {
			ob_start();
			wc_get_template( 'single-product/tabs/tabs.php' );
			$result = ob_get_clean();
		}

		if ($res === 'type-3') {
			if (blocksy_get_theme_mod('woo_accordion_in_summary', 'default') === 'summary') {
				return;
			}
			ob_start();
			blocksy_custom_accordion_tabs();
			$result = ob_get_clean();
		}

		if ( $res === 'type-4' ) {
			if (blocksy_get_theme_mod('woo_accordion_in_summary', 'default') === 'summary') {
				return;
			}
			ob_start();
			blocksy_custom_simple_tabs();
			$result = ob_get_clean();
		}

		$res .= ':' . blocksy_get_theme_mod('woo_tabs_alignment', 'center');

		$prefix = blocksy_manager()->screen->get_prefix();

		$deep_link_args = [
			'prefix' => $prefix,
			'suffix' => 'woo_has_product_tabs'
		];

		echo str_replace(
			'wc-tabs-wrapper"',
			'wc-tabs-wrapper" data-type="' . $res . '" ' . blocksy_generic_get_deep_link($deep_link_args),
			$result
		);
	}
}