<?php
namespace Depicter\Dashboard;

use Averta\Core\Utility\Arr;
use Averta\WordPress\Utility\Escape;
use Averta\WordPress\Utility\JSON;
use Averta\WordPress\Utility\Plugin;
use Depicter\Security\CSRF;
use Depicter\Services\UserAPIService;
use Depicter\WordPress\Settings\Settings;

class DashboardPage
{

	const HOOK_SUFFIX = 'toplevel_page_depicter-dashboard';
	const PAGE_ID = 'depicter-dashboard';

	/**
	 * @var string
	 */
	var $hook_suffix = '';

	public function bootstrap(){
		add_action( 'admin_menu', [ $this, 'registerPage' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
		add_action( 'admin_head', array( $this, 'disable_admin_notices' ) );
		add_action( 'admin_init', [ $this, 'externalPageRedirect' ] );

		$this->settingsPage();
	}

	/**
	 * Register admin pages.
	 *
	 * @return void
	 */
	public function registerPage() {
		$this->hook_suffix = add_menu_page(
			__('Depicter', 'depicter'),
			__('Depicter', 'depicter'),
			'access_depicter',
			self::PAGE_ID,
			[ $this, 'render' ], // called to output the content for this page
			\Depicter::core()->assets()->getUrl() . '/resources/images/svg/wp-logo.svg'
		);

		add_submenu_page(
			self::PAGE_ID,
			__( 'Dashboard', 'depicter' ),
			__( 'Dashboard', 'depicter' ),
			'access_depicter',
			self::PAGE_ID
		);

		add_submenu_page(
			self::PAGE_ID,
			__( 'Support', 'depicter' ),
			__( 'Support', 'depicter' ),
			'access_depicter',
			self::PAGE_ID . '-goto-support',
			[ $this, 'externalPageRedirect' ]
		);

		add_action( 'admin_print_scripts-' . $this->hook_suffix, [ $this, 'printScripts' ] );
	}

	/**
	 * Process redirect after clicking on Depicter admin menu
	 *
	 * @return void
	 */
	public function externalPageRedirect(){
		if ( empty( $_GET['page'] ) ) {
			return;
		}

		if ( self::PAGE_ID . '-goto-support' === $_GET['page'] ) {
			wp_redirect( 'https://depicter.com/?utm_source=wp-submenu#support' );
			die;
		}
	}

	/**
	 * Settings page markup
	 *
	 * @return void
	 */
	public function settingsPage() {

		$settings = new Settings(__('Settings', 'depicter'), 'depicter-settings');
		$settings->set_option_name('depicter_options');
		$settings->set_menu_parent_slug( self::PAGE_ID );

		$settings->add_tab(__( 'General', 'depicter' ));
		$settings->add_section( __( 'General Settings', 'depicter' ) );

		$settings->add_option('nonce',[
			'action' => 'depicter-settings',
			'name' => '_depicter_settings_nonce'
		]);

		$settings->add_option('select', [
			'name' => 'use_google_fonts',
			'label' => __( 'Google Fonts', 'depicter' ),
			'options' => [
				'on' => __( 'Default (Enable)', 'depicter' ),
				'off' => __( 'Disable', 'depicter' ),
				'editor_only' => __( 'Load in Editor Only', 'depicter' ),
				'save_locally' => __( 'Save Locally', 'depicter' )
			],
			'description' => __( 'Enable, disable, or save Google Fonts locally on your host.', 'depicter' )
		]);

		$settings->add_option('select', [
			'name' => 'resource_preloading',
			'label' => __( 'Resource Preloading', 'depicter' ),
			'options' => [
				'on' => __( 'Default (Enable)', 'depicter' ),
				'off' => __( 'Disable', 'depicter' )
			],
			'description' => __( 'Enable or disable preloading of website resources (images and CSS) for faster page load speed.', 'depicter' )
		]);

		$settings->add_option('checkbox', [
			'name' => 'always_load_assets',
			'label' => __( 'Load assets on all pages?', 'depicter' ),
			'description' => "<br><br>". __( 'By default, Depicter will load corresponding JavaScript and CSS files on demand. but if you need to load assets on all pages, check this option. <br>( For example, if you plan to load Depicter via Ajax, you need to enable this option )', 'depicter' ),
		]);

		$settings->make();

	}

	/**
	 * Disable all admin notices in dashboard page
	 *
	 * @return void
	 */
	public function disable_admin_notices() {
		$screen = get_current_screen();
		if ( $screen->id == $this->hook_suffix ) {
			remove_all_actions( 'admin_notices' );
		}
	}

	public function render(){
		$this->renewTokens();
		echo Escape::content( \Depicter::view( 'admin/dashboard/index.php' )->toString() );
	}

	/**
	 * Load dashboard scripts
	 *
	 * @param string $hook_suffix
	 */
	public function enqueueScripts( $hook_suffix = '' ){

		if( $hook_suffix !== $this->hook_suffix ){
			return;
		}

		// Enqueue scripts.
		\Depicter::core()->assets()->enqueueScript(
			'depicter--dashboard',
			\Depicter::core()->assets()->getUrl() . '/resources/scripts/dashboard/depicter-dashboard.js',
			[],
			true
		);

		// Enqueue styles.
		\Depicter::core()->assets()->enqueueStyle(
			'depicter-dashboard',
			\Depicter::core()->assets()->getUrl() . '/resources/styles/dashboard/index.css'
		);
	}

	/**
	 * Print required scripts in Dashboard page
	 *
	 * @return void
	 */
	public function printScripts()
	{
		global $wp_version;
		$currentUser = wp_get_current_user();

		try {
			$googleClientId = UserAPIService::googleClientID()['clientId'] ?? '';
		} catch ( \Exception $e ) {
			$googleClientId = '';
		}

		$upgradeLink = add_query_arg([
			'action'   => 'upgrade-plugin',
			'plugin'   => 'depicter/depicter.php',
			'_wpnonce' => wp_create_nonce( 'upgrade-plugin_depicter/depicter.php')
		], self_admin_url('update.php') );

		wp_add_inline_script('depicter--dashboard', 'window.depicterEnv = '. JSON::encode(
		    [
				'wpVersion'   => $wp_version,
				"scriptsPath" => \Depicter::core()->assets()->getUrl(). '/resources/scripts/dashboard/',
				'clientKey'   => \Depicter::auth()->getClientKey(),
				'csrfToken'   => \Depicter::csrf()->getToken( CSRF::DASHBOARD_ACTION ),
				'updateInfo' => [
					'from' => \Depicter::options()->get('version_previous') ?: null,
					'to'   => \Depicter::options()->get('version'),
					'url'  => $upgradeLink,
				],
				"assetsAPI"   => Escape::url('https://wp-api.depicter.com/' ),
				"wpRestApi"   => Escape::url( get_rest_url() ),
				"pluginAPI"   => admin_url( 'admin-ajax.php' ),
				"editorPath"  => \Depicter::editor()->getEditUrl( '__id__' ),
				"documentPreviewPath" => \Depicter::editor()->getEditUrl( '__id__' ),
				'user' => [
					'tier'  => \Depicter::auth()->getTier(),
					'name'  => Escape::html( $currentUser->display_name ),
					'email' => Escape::html( $currentUser->user_email   ),
					'joinedNewsletter' => !! \Depicter::options()->get('has_subscribed')
				],
				'activation' => [
					'status'       => \Depicter::auth()->getActivationStatus(),
					'errorMessage' => \Depicter::options()->get('activation_error_message', ''),
					'expiresAt'    => \Depicter::options()->get('subscription_expires_at' , ''),
					'isNew'        => isset( $_GET['depicter_upgraded'] )
				],
			    'integrations' => [
					'woocommerce' => [
						'label' => __( 'WooCommerce Plugin', 'depicter' ),
						'enabled' => Plugin::isActive( 'woocommerce/woocommerce.php' )
					]
				],
			    'AIWizard' =>  [
					'introVideoSrc' => 'https://www.youtube.com/embed/kdR9Jw0yWjU?rel=0'
				],
				'googleClientId' => $googleClientId,
				'tokens' => [
					'idToken'      => \Depicter::cache('base')->get( 'id_token'     , null ),
					'accessToken'  => \Depicter::cache('base')->get( 'access_token' , null ),
					'refreshToken' => \Depicter::cache('base')->get( 'refresh_token', null )
				],
			]
		), 'before' );

	}

	/**
	 * Renew member tokens before expire date
	 *
	 * @return void
	 */
	public function renewTokens() {
		if ( false === \Depicter::cache('base')->get( 'access_token' ) ) {
			UserAPIService::renewTokens();
		}
	}

}
