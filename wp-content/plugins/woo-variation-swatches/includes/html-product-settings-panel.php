<?php
defined( 'ABSPATH' ) or die( 'Keep Quit' );

/**
 * @var $post
 * @var $wpdb
 * @var $product_object
 * @var $attributes
 * @var $settings
 */

// $attributes = $product_object->get_attributes();
$product_id = $product_object->get_id();

$product_swatches_data = array();
?>
<style type="text/css">
	.woo-variation-swatches-variation-product-option-features-wrapper {
		padding: 20px;
		margin: 5px;
		background-color: #eeeeee;
	}

	.woo-variation-swatches-variation-product-option-features-wrapper li span {
		color: #15ce5c;
	}

	.woo-variation-swatches-variation-product-option-features-wrapper p, .woo-variation-swatches-variation-product-option-features-wrapper ul {
		padding: 10px 0;
	}

	.gwp-pro-button span {
		padding-top: 10px;
	}

	.woo-variation-swatches-variation-product-option-features-wrapper ul {
		display: block;

	}

	.woo-variation-swatches-variation-product-option-features-wrapper ul li {
		margin-bottom: 10px;
	}

	.woo-variation-swatches-variation-product-option-features-wrapper .gwp-pro-features-links {
		margin-left: 20px;
		padding: 5px;
	}

</style>
<div data-product_id="<?php echo esc_attr( $product_id ) ?>" id="woo_variation_swatches_variation_product_options" class="woo-variation-swatches-variation-product-options-wrapper panel wc-metaboxes-wrapper hidden">

	<div id="woo_variation_swatches_variation_product_options_inner">
		<div id="woo-variation-swatches-variation-product-option-settings-wrapper">

			<div class="woo-variation-swatches-variation-product-option-features-wrapper">

				<h3><?php printf( __( 'With the premium version of <a target="_blank" href="%s">Variation Swatches for WooCommerce</a>, you can do:', 'woo-variation-swatches' ), 'https://getwooplugins.com/plugins/woocommerce-variation-swatches/' ); ?></h3>
				<ul>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Individual Product Basis Attribute Variation Swatches Customization', 'woo-variation-swatches' ) ?>

						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/show-all-swatches-type-in-the-same-variation/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=rg1Xg-t29Kc"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Show Image, Color, Button Variation Swatches in Same Attribute', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/product-details/product/polo-ralph-lauren-ph3127-59/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=ILf1S2k97es&list=PLjkiDGg3ul_L-3332EDkmuN_G2ttrJM24&index=9"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Convert Manually Created Attribute Variations Into Color, Image, and Label Swatches', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://www.youtube.com/watch?v=rg1Xg-t29Kc"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Group based swatches display.', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/product/nike-air-vapormax-plus-group/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a>
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/group-category-swatches"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Convert attribute variations into radio button.', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/radio-variation-swatches"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=XY6GjG3yPUU&list=PLjkiDGg3ul_JB6vcxRYEVlDC1_setGLY0"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Show Entire Color, Image, Label And Radio Attributes Swatches In Catalog/ Category / Archive / Store/ Shop Pages', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=1IhEZiGzJHs"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Show Selected Single Color or Image Or Label Attribute Swatches In Catelog/ Category / Archive / Store / Shop Pages', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/product-details/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=r1DIlBSJI5o&list=PLjkiDGg3ul_L-3332EDkmuN_G2ttrJM24&index=8"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Change Variation Product Gallery After Selecting Single Attribute Like Amazon Or AliExpress', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/change-product-image-based-on-select-attribute/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=31WR96-9kuQ"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Generate Selected Attribute Variation Link', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/generate-link-for-selected-variations/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=Zw_5ACg0ASA&list=PLjkiDGg3ul_L-3332EDkmuN_G2ttrJM24"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Option to Select ROUNDED and SQUARED Attribute Variation Swatches Shape In the Same Product.', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/round-square-shape-swatches"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=1yO9DGYCYAA&list=PLjkiDGg3ul_L-3332EDkmuN_G2ttrJM24&index=10"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Blur Or Hide Or Show Cross Sign For Out of Stock Variation Swatches (Unlimited Variations Without hiding out of stock item from catalog)', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/cross-out-of-stock-variations-more-than-30-variations/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=LGomum3lwHw&list=PLjkiDGg3ul_L-3332EDkmuN_G2ttrJM24&index=11"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Shop Page Swatches Size Control', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://www.youtube.com/watch?v=xeT7byUaa7U"><?php esc_html_e( 'Live Preview', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Make Selected Attribute Variation Swatches Size Larger Than Other Default Attribute Variations', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/swatches-size-selected-attribute/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=xeT7byUaa7U"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Show Custom Text in Variation Tooltip In Product and Shop Page', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://www.youtube.com/watch?v=bzx_G5Di9kQ&list=PLjkiDGg3ul_L-3332EDkmuN_G2ttrJM24&index=12"><?php esc_html_e( 'Live Preview', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Show Custom Image in Variation Swatches Tooltip In Product And Shop Page', 'woo-variation-swatches' ) ?>
						<div class="gwp-pro-features-links">
							<a target="_blank" href="https://demo.getwooplugins.com/woocommerce-variation-swatches/image-tooltip/"><?php esc_html_e( 'Live Demo', 'woo-variation-swatches' ) ?></a> |
							<a target="_blank" href="https://www.youtube.com/watch?v=zrOeb2ksOig&list=PLjkiDGg3ul_L-3332EDkmuN_G2ttrJM24&index=13"><?php esc_html_e( 'Video Tutorial', 'woo-variation-swatches' ) ?></a>
						</div>
					</li>

					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Archive page swatches positioning.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Archive page swatches alignment.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Tooltip display setting on archive/shop page.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Variation clear button display setting.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Customize tooltip text and background color.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Customize tooltip image and image size.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Customize font size, swatches height and width.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Customize swatches colors, background and border sizes.', 'woo-variation-swatches' ) ?>
					</li>
					<li>
						<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Automatic updates and exclusive technical support.', 'woo-variation-swatches' ) ?>
					</li>
				</ul>
				<div class="clear"></div>
				<a target="_blank" class="button button-primary button-hero gwp-pro-button" href="<?php echo esc_url( woo_variation_swatches()->get_backend()->get_pro_link() ); ?>"><?php esc_html_e( 'Ok, I need this feature!', 'woo-variation-swatches' ); ?>
					<span class="dashicons dashicons-external"></span></a>
			</div>

		</div>
	</div>
</div>
