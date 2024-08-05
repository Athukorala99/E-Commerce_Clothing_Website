<?php
namespace Depicter\Modules\Beaver;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends \FLBuilderModule {

    public function __construct() {
        parent::__construct( array(

            'name' => __( 'Depicter', 'depicter' ),

            'description' => __( 'Make animated and interactive image slider, video slider, post slider and carousel which work smoothly across devices.', 'depicter' ),

            'category' => __( 'Depicter', 'depicter' ),

            'dir' => DEPICTER_PLUGIN_PATH . '/app/src/Modules/Beaver/',

            'url' => DEPICTER_PLUGIN_URL . '/app/src/Modules/Beaver/',

            // 'icon' => 'button.svg',

            'editor_export' => true, // Defaults to true and can be omitted.

            'enabled' => true, // Defaults to true and can be omitted.

            'partial_refresh' => false, // Defaults to false and can be omitted.

        ) );

        add_action( 'wp_enqueue_scripts', [ $this, 'loadModuletScripts' ] );
    }

    /**
     * Load module scripts when required
     *
     * @return void
     */
    public function loadModuletScripts() {
        global $post;
        if ( !\FLBuilderModel::is_builder_enabled( $post->ID ) ) {
            return;
        }

        if ( strpos( $post->post_content, 'depicter-') || \FLBuilderModel::is_builder_active() ) {
            $styles = \Depicter::front()->assets()->enqueueStyles();
            foreach ( $styles as $handler => $style ) {
                $this->add_css( $handler );
            }

            $flbuilder_data = get_post_meta( $post->ID, '_fl_builder_data', true);
            $flbuilder_data = is_array( $flbuilder_data ) ? maybe_serialize( $flbuilder_data ) : $flbuilder_data;
            preg_match_all( '/document_id";s:\d+:"(\d+)"/', $flbuilder_data, $sliderIDs, PREG_SET_ORDER );
            foreach( $sliderIDs as $key => $sliderID ) {
                if ( !empty( $sliderID[1] ) ) {
					\Depicter::document()->cacheCustomStyles( $sliderID[1] );
					\Depicter::front()->assets()->enqueueCustomAssets( $sliderID[1] );
					\Depicter::front()->assets()->enqueuePreloadTags( $sliderID[1] );
                }
            }

            $scripts = \Depicter::front()->assets()->enqueueScripts();
            foreach ( $scripts as $handler => $script ) {
                $this->add_js( $handler );
            }
        }
    }

    public static function getDepicterFields() {
        if ( ! isset( $_GET['fl_builder'] ) ) {
            return[];
        }

        $list = [
			'0' => __( 'Select Slider', 'depicter' )
		];
        $documents = \Depicter::documentRepository()->select( ['id', 'name', 'status'] )->orderBy('modified_at', 'DESC')->findAll()->get();
		$documents = $documents ? $documents->toArray() : [];
        foreach( $documents as $document ) {
			$list[ '#' . $document['id'] ] = "[#{$document['id']}]: " . $document['name'];
		}

        $fields = [
            'document_id'   => [
                'type'          => 'select',
                'label'         => __( 'Select Slider', 'depicter' ),
                'default'       => '0',
                'options'       => $list
            ]
        ];
        if ( current_user_can( 'access_depicter' ) ) {
            foreach( $documents as $document ) {

                $args = [
                    'isPublishedBefore' => \Depicter::documentRepository()->isPublishedBefore( $document['id'] ),
                    'documentStatus'    => $document['status'],
                    'documentID'        => $document['id']
                ];
    
                $markup = \Depicter::view('admin/notices/builders-draft-notice')->with('view_args', $args)->toString();
                $fields[ 'slider_control_buttons_' . $document['id'] ] =  [
                    'type'    => 'raw',
                    'content' => $markup,
                ];
            }
        }

        return $fields;
    }
}

\FLBuilder::register_module( '\Depicter\Modules\Beaver\Module', array(
    'general'       => array( // Tab
        'title'         => __('General', 'depicter'), // Tab title
        'sections'      => array( // Tab Sections
            'general'       => array( // Section
                'title'         => __('Depicter Settings', 'depicter'), // Section Title
                'fields'        => Module::getDepicterFields() // Section Fields
            ),
        ),
    )
) );
