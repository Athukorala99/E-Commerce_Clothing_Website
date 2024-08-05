<?php
/**
 * Handle checking requirements.
 *
 * @package Depicter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'depicter_requirements_satisfied' ) ) {

	/**
	 * Whether required php version is available or not.
	 *
	 * @param string $name Project name.
	 * @param string $min Minimum PHP version.
	 *
	 * @return bool
	 */
	function depicter_requirements_satisfied( $name, $min ) {

		if ( version_compare( PHP_VERSION, $min, '>=' ) ) {
		    return true;
		}

		add_action(
			'admin_notices',
			function () use ( $name, $min ) {
				$message = __( '%1$s plugin requires PHP version %2$s but current version is %3$s. Please contact your host provider and ask them to upgrade PHP version.', 'depicter' );
				?>
				<div class="notice notice-error">
					<p><?php echo wp_kses( sprintf(
						$message,
						'<strong>' . $name . '</strong>',
						'<strong>' . $min . '</strong>',
						'<strong>' . PHP_VERSION . '</strong>'
					), ['strong' => [] ] ); ?></p>
				</div>
				<?php
			}
		);

		// An incompatible version is already loaded.
		return false;
	}
}
