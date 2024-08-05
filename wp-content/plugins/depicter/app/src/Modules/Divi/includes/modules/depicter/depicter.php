<?php

class Divi_Depicter_Module extends ET_Builder_Module {

	public $slug       = 'depicter_module';

	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'Depicter',
		'author'     => 'averta',
		'author_uri' => '',
	);

	public function init() {

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_depicter_assets' ] );

		$this->name = esc_html__( 'Depicter', 'depicter' );
	}

	public function enqueue_depicter_assets() {
		global $post;
		if ( et_core_is_fb_enabled() ) {
			wp_localize_script( 'divi-depicter-builder-bundle', 'depicter_divi', [
				'ajax_url' => admin_url('admin-ajax.php'),
				'editor_url' => \Depicter::editor()->getEditUrl('1'),
				'token' => \Depicter::csrf()->getToken( \Depicter\Security\CSRF::EDITOR_ACTION ),
				'published_text' => esc_html__( 'Published', 'depicter' )
			]);
		}

		if ( et_core_is_builder_used_on_current_request() ) {
			if ( strpos( $post->post_content, $this->slug ) || et_core_is_fb_enabled() ) {
				\Depicter::front()->assets()->enqueueStyles();
				\Depicter::front()->assets()->enqueueScripts();

				preg_match_all( '/document_id="(\d+)"/', $post->post_content, $sliderIDs, PREG_SET_ORDER );
				foreach( $sliderIDs as $key => $sliderID ) {
					if ( !empty( $sliderID[1] ) ) {
						\Depicter::document()->cacheCustomStyles( $sliderID[1] );
						\Depicter::front()->assets()->enqueueCustomAssets( $sliderID[1] );
						\Depicter::front()->assets()->enqueuePreloadTags( $sliderID[1] );
					}
				}
			}
		}

	}

	public function get_fields() {
		$list = [
			'0' => __( 'Select Slider', 'depicter' )
		];
		$documents = \Depicter::documentRepository()->select( ['id', 'name', 'status'] )->orderBy('modified_at', 'DESC')->findAll()->get();
		$documents = $documents ? $documents->toArray() : [];
		foreach( $documents as $document ) {
			$list[ '#' . $document['id'] ] = "[#{$document['id']}]: " . $document['name'];
		}

		$fields = array(
			'document_id'                     => array(
				'label'            => esc_html__( 'Slider ID', 'depicter' ),
				'type'             => 'select',
				'options'          => $list,
			),
		);

		if ( current_user_can( 'access_depicter' ) ) {
			foreach( $documents as $document ) {

				$fields[ 'slider_buttons_' . $document['id'] ] = [
					'type'            => 'slider_buttons',
					'option_category' => 'basic_option',
					'description'     => esc_html__( 'Publish or edit slider', 'depicter' ),
					'status' 	  	  => $document['status'],
					'show_if' => [
						'document_id' => $document['id']
					]
				];
			}
		}

		return $fields;
	}

	public function render( $attrs, $content, $render_slug ) {
		$args = [
			'addImportant' => true,
			'echo' => false
		];

		return \Depicter::front()->render()->document( ltrim( $this->props['document_id'], '#' ), $args );
	}
}

new Divi_Depicter_Module;
