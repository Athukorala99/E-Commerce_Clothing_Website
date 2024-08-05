<?php
namespace Depicter\WordPress;


use WPEmerge\ServiceProviders\ServiceProviderInterface;

class PermissionsServiceProvider implements ServiceProviderInterface {

	/**
	 * {@inheritDoc}
	 */
	public function register( $container ){}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ){
		register_activation_hook( DEPICTER_PLUGIN_FILE, [ $this, 'assign' ] );
		add_action( 'admin_init', [ $this, 'assign' ] );
		add_action( 'plugins_loaded', [ $this, 'onPluginsLoad' ] );
		add_action( 'user_register', [ $this, 'addCapabilityToNewUsers' ] );
	}

	/**
	 * Fire method when plugin assigns
	 */
	public function assign() {
		$this->assignCapabilities();
	}

	/**
	 * Add capability to new registered user
	 *
	 * @param int $userID
	 * @return void
	 */
	public function addCapabilityToNewUsers( $userID ) {
		$roles = array( 'administrator', 'editor' );
		$userData = get_userdata( $userID );
		$userRole = $userData->roles;
		if ( in_array( $userRole, $roles ) ) {
			$this->assignCapabilities( true );
		}
	}

	/**
	 * Use other plugins hooks
	 */
	public function onPluginsLoad() {
		// Add depicter custom capabilities to members plugin if it's installed
		if ( function_exists( 'members_get_capabilities' ) ) {
			add_filter( 'members_get_capabilities', [ $this, 'customCapabilities' ] );
		}
	}

	/**
	 * Add custom capabilities to members plugin
	 *
	 * @param array $caps
	 *
	 * @return array
	 */
	public function customCapabilities( array $caps = [] ) {
		$caps[] = 'access_depicter' ;
		$caps[] = 'edit_depicter'   ;
		$caps[] = 'edit_others_depicter';
		$caps[] = 'publish_depicter';
		$caps[] = 'delete_depicter' ;
		$caps[] = 'delete_others_depicter';
		$caps[] = 'create_depicter' ;
		$caps[] = 'import_depicter' ;
		$caps[] = 'export_depicter' ;
		$caps[] = 'duplicate_depicter';
		$caps[] = 'manage_depicter' ;

		return $caps;
	}

	/**
	 * Assign custom capabilities to main roles
	 *
	 * @param false $force_update
	 */
	protected function assignCapabilities( bool $force_update = false ) {
		// check if custom capabilities are added before or not
		$is_added = \Depicter::options()->get( 'capabilities_added', 0 );

		// add caps if they are not already added
		if( ! $is_added || $force_update ) {

			// assign depicter capabilities to following roles
			$roles = array( 'administrator', 'editor' );

			foreach ( $roles as $role ) {
				if( ! $role = get_role( $role ) ){
					continue;
				}
				foreach( $this->customCapabilities() as $capability ){
					$role->add_cap( $capability );
				}
			}

			\Depicter::options()->set( 'capabilities_added', 1 );
		}
	}
}
