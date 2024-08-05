<?php

namespace Depicter\Modules\Elementor;

use Averta\WordPress\Utility\JSON;

class Module {

	public function __construct(){
		if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
			add_action( 'elementor/widgets/register', [ $this, 'registerWidgets' ] );
		} else {
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'registerWidgets' ] );
		}

		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueueEditorAssets'] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueWidgetScript'] );
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @return void
	 */
	public function registerWidgets() {
		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
			\Elementor\Plugin::instance()->widgets_manager->register( new SliderWidget() );
		} else {
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new SliderWidget() );
		}
	}

	/**
	 * load required script for elementor widget in elementor editor env
	 */
	public function enqueueWidgetScript() {
		global $post;
		if ( !class_exists( '\Elementor\Plugin' ) || empty( $post->ID ) ) {
			return;
		}

		$document = \Elementor\Plugin::$instance->documents->get($post->ID);
		if ( !empty( $document ) && $document->is_built_with_elementor() ) {
			$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
			$elementor_data = is_array( $elementor_data ) ? JSON::encode( $elementor_data ) : $elementor_data;

			if ( strpos( $elementor_data, 'depicter_slider' ) ) {
				\Depicter::front()->assets()->enqueueStyles();
				\Depicter::front()->assets()->enqueueScripts();

				preg_match_all( '/slider_id":"(\d+)"/', $elementor_data, $sliderIDs, PREG_SET_ORDER );
				foreach( $sliderIDs as $key => $sliderID ) {
					if ( !empty( $sliderID[1] ) ) {
						\Depicter::document()->cacheCustomStyles( $sliderID[1] );
						\Depicter::front()->assets()->enqueueCustomAssets( $sliderID[1] );
						\Depicter::front()->assets()->enqueuePreloadTags( $sliderID[1] );
					}
				}
			}
		}

		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			\Depicter::front()->assets()->enqueueScripts('widget');
		}
	}

	public function enqueueEditorAssets(){

		\Depicter::core()->assets()->enqueueStyle(
			'depicter-admin',
			\Depicter::core()->assets()->getUrl() . '/resources/styles/admin/admin.css'
		);

		\Depicter::core()->assets()->enqueueScript(
			'depicter-admin',
			\Depicter::core()->assets()->getUrl() . '/resources/scripts/admin/index.js',
			['jquery'],
			true
		);

		wp_localize_script( 'depicter-admin', 'depicterParams',[
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'editorUrl' => \Depicter::editor()->getEditUrl('1'),
			'token' => \Depicter::csrf()->getToken( \Depicter\Security\CSRF::EDITOR_ACTION ),
			'publishedText' => esc_html__( 'Published', 'depicter' )
		]);
	}
}
