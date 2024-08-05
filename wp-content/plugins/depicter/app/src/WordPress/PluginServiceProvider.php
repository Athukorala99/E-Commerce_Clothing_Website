<?php
namespace Depicter\WordPress;

use Averta\WordPress\Models\WPOptions;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register plugin general hooks.
 */
class PluginServiceProvider implements ServiceProviderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		$app = $container[ WPEMERGE_APPLICATION_KEY ];

		// register depicter options
		$container[ 'depicter.options' ] = function () {
			return new WPOptions('depicter_');
		};
		$app->alias( 'options', 'depicter.options' );

	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		register_activation_hook(  DEPICTER_PLUGIN_FILE, [ $this, 'activate'  ] );
		register_deactivation_hook(DEPICTER_PLUGIN_FILE, [ $this, 'deactivate'] );

		add_action( 'plugins_loaded', [$this, 'loadTextDomain'] );
		add_action( 'admin_init', [ $this, 'check_plugin_upgrade_via_upload' ] );
		add_filter( 'update_plugin_complete_actions', [ $this, 'add_depicter_link_after_upgrade'], 10, 1);
	}

	/**
	 * Plugin activation.
	 *
	 * @return void
	 */
	public function activate() {
		// Nothing to do right now.
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
		// Nothing to do right now.
	}

	/**
	 * Load text domain.
	 *
	 * @return void
	 */
	public function loadTextDomain() {
		load_plugin_textdomain( 'depicter', false, basename( dirname( DEPICTER_PLUGIN_FILE ) ) . DIRECTORY_SEPARATOR . 'languages' );
	}

	/**
	 * Check if plugin updated via upload or not
	 */
	public function check_plugin_upgrade_via_upload() {
		$previousVersion = \Depicter::options()->get( 'version', 0 );
		if ( version_compare( DEPICTER_VERSION, $previousVersion, '>' ) ) {
			\Depicter::options()->set( 'version_previous', $previousVersion );
			\Depicter::options()->set( 'version', DEPICTER_VERSION );
			do_action( 'depicter/plugin/updated' );
		}
	}

	/**
	 * Add go to depicter dashboard link after upgrade at bottom of upgrade page
	 *
	 * @param array $install_actions
	 * @return void
	 */
	public function add_depicter_link_after_upgrade( $install_actions ){
		$install_actions['depicter_dashboard'] = sprintf(
			'<a href="%s" target="_parent">%s</a>',
			admin_url( 'admin.php?page=depicter-dashboard' ),
			__( 'Go to Depicter', 'depicter' )
		);
	
		return $install_actions;
	}
}
