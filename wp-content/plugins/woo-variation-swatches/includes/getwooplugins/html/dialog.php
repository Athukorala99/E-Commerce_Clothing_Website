<?php
defined( 'ABSPATH' ) or die( 'Keep Quit' );
/**
 * @var $template_id
 * @var $title
 * @var $body
 * @var $links
 * @var $footer
 */
$template_id = sprintf( 'tmpl-%s', esc_attr( $template_id ) );
?>

<script type="text/template" id="<?php echo esc_attr( $template_id ) ?>">
	<div class="gwp-backbone-modal gwp-pro-dialog">
		<div class="gwp-backbone-modal-content">
			<section class="gwp-backbone-modal-main" role="main">
				<header class="gwp-backbone-modal-header">
					<h1><?php echo esc_html( $title ); ?></h1>
					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'woo-variation-swatches' ); ?></span>
					</button>
				</header>
				<article>
					<div class="gwp-dialog-form-body">
						<!--
						USE video-wrapper for iframe video like: youtube / vimeo
						<div class="video-wrapper">
						<iframe src="..."></iframe>
						</div>
						-->
						<?php echo wp_kses_post( $body ); // WPCS: XSS ok. ?>
					</div>
				</article>
				<?php if ( ! empty( $links ) ): ?>
					<footer>
						<div class="inner">
							<?php if ( isset( $links['button_url'] ) && ! empty( $links['button_url'] ) ): ?>
								<div class="gwp-action-button-group">
									<a target="_blank" href="<?php echo esc_url( $links['button_url'] ) ?>" class="button <?php echo isset( $links['button_class'] ) ? esc_attr( $links['button_class'] ) : 'button-primary' ?>"><?php echo esc_html( $links['button_text'] ) ?></a>
								</div>
							<?php endif; ?>

							<?php if ( isset( $links['link_url'] ) && ! empty( $links['link_url'] ) ): ?>
								<a target="_blank" href="<?php echo esc_url( $links['link_url'] ) ?>"><?php echo esc_html( $links['link_text'] ) ?></a>
							<?php endif; ?>
						</div>
					</footer>
				<?php endif; ?>
			</section>
		</div>
	</div>
	<div class="gwp-backbone-modal-backdrop modal-close"></div>
</script>