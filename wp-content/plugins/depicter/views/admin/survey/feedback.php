<?php
/**
 * feedback layout.
 *
 * @package Depicter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="depicter-survey-container">
	<div class="depicter-survey-list">
		<div class="depicter-logo">
			<img src="<?php echo Depicter::core()->assets()->getUrl() . '/resources/images/svg/light-logo.svg'; ?>" alt="logo">
			<span class="depicter-close"></span>
		</div>
		<form action="#" method="post">
			<?php
			wp_nonce_field( 'depicter-nonce' );
			?>
			<h3><?php esc_html_e( 'If you have a moment, please share why you are deactivating Depicter:', 'depicter' );?></h3>
			<?php
			foreach( $reasons as $key => $reason ) {
				echo '<div class="depicter-deactivate-issue">';
				echo '<input type="radio" id="depicter-option-'. esc_attr( $key ) .'" name="dep_deactivation_reason" value="'. esc_attr( $reason['key'] ) .'">';
				echo '<label for="depicter-option-'. esc_attr( $key ) .'">'. esc_html( $reason['text'] ) .'</label>';
				if ( !empty( $reason['user-description'] ) ) {
					echo '<br>';
					echo '<input type="text" name="user-description-'. esc_attr( $key ) .'" value="" placeholder="'. esc_attr( $reason['user-description'] ) .'">';
				}
				echo '</div>';
			}
			?>
			<div class="depicter-button-wrapper">
				<input type="button" class="depicter-skip" value="<?php esc_attr_e( 'Skip & Deactivate', 'depicter' );?>">
				<input type="button" class="depicter-submit" value="<?php esc_attr_e( 'Submit & Deactivate', 'depicter' );?>" disabled>
			</div>
		</form>
	</div>
</div>
