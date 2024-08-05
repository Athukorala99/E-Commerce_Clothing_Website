<?php

namespace Depicter\Modules\WPBakery;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module {

    /**
     * Class constructor
     */
    public function __construct() {
        if ( function_exists( 'vc_map' ) ) {
            $this->map_vc_shortcodes();

            vc_add_shortcode_param( 'depicter_slider_buttons', [ $this, 'slider_buttons_markup' ] );
            add_action( 'vc_edit_form_fields_after_render', [ $this, 'add_scripts' ] );
        }
    }

    /**
     * Add depicter shortcode to wpbakery
     *
     * @return void
     */
    public function map_vc_shortcodes() {

        vc_map([
            'name' => __( 'Depicter', 'depicter' ),
            'base' => 'depicter',
            'category' => __( 'Content', 'depicter' ),
            'description' => __( 'Make animated and interactive slider', 'depicter' ),
            'icon'  => \Depicter::core()->assets()->getUrl() . '/resources/images/svg/logo-without-text.svg',
            'params' => $this->get_fields()
        ]);
    }

	/**
	 * get fields
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function get_fields() {

		$list = [
			__( 'Select Slider', 'depicter' ) => 0
		];
		$documents = \Depicter::documentRepository()->select( ['id', 'name', 'status'] )->orderBy('modified_at', 'DESC')->findAll()->get();
		$documents = $documents ? $documents->toArray() : [];
		foreach( $documents as $document ) {
            $list[ "[#{$document['id']}]: " . $document['name'] ] = $document['id'];
		}

        $fields = [
            [
                'type'        => 'dropdown',
                'class'       => '',
                'admin_label' => true,
                'heading'     => __( 'Select Slider', 'depicter' ),
                'param_name'  => 'id',
                'value'       => $list,
            ]
        ];

        if ( current_user_can( 'access_depicter' ) ) {
            foreach( $documents as $document ) {

                $fields[] = [
                    'type'        => 'depicter_slider_buttons',
                    'class'       => '',
                    'param_name'  => 'slider_control_buttons_' . $document['id'],
                    'isPublishedBefore' => \Depicter::documentRepository()->isPublishedBefore( $document['id'] ),
                    'documentStatus' => $document['status'],
                    'documentID'     => $document['id']
                ];
            }
        }

        return $fields;
	}

    /**
     * Add script to the end of editor fields after render
     *
     * @return void
     */
    public function add_scripts() {
        if ( empty( $_POST['tag'] ) || $_POST['tag'] != 'depicter' ) {
            return;
        }

        echo '<script type="text/javascript">
            var $sliderButtons = document.querySelectorAll(\'[data-param_type="depicter_slider_buttons"]\');
            document.querySelector(\'[data-vc-shortcode="depicter"]\').querySelector("select").addEventListener( "change", function(){
                if ( this.value != 0 ) {
                    $sliderButtons.forEach(function(el, index){ el.style.display = \'none\';});
                    document.querySelector(\'[data-vc-shortcode-param-name="slider_control_buttons_\' +this.value+ \'"]\').style.display = "block";
                }
            });
            $sliderButtons.forEach(function(el, index){ el.style.display = \'none\';});
            var id = document.querySelector(\'[data-vc-shortcode="depicter"]\').querySelector("select").value;
            if ( id != "0" ) {
                document.querySelector(\'[data-vc-shortcode-param-name="slider_control_buttons_\' +id+ \'"]\').style.display = "block";
            }

            document.querySelectorAll(".depicter-edit-slider").forEach(function(el, index){
                el.addEventListener( "click", function(){
                    var id = document.querySelector(\'[data-vc-shortcode="depicter"]\').querySelector("select").value;
                    var editorUrl = depicterParams.editorUrl.replace( \'document=1\', \'document=\' + id );
                    window.open(editorUrl);
                });
            });

            $sliderButtons.forEach(function(el, index){
                var $publishButton = el.querySelector(".depicter-publish-slider:not([disabled])");
                if ( $publishButton ) {
                    $publishButton.addEventListener( "click", function(event){
                        event.target.querySelector(".btn-label").style.display = "none";
                        event.target.querySelector(".depicter-state-icon").style.display = "inline-block";

                        var id = document.querySelector(\'[data-vc-shortcode="depicter"]\').querySelector("select").value;
                        var data = new FormData();
                        data.append(\'ID\', id);
                        data.append(\'status\', \'published\');

                        fetch( depicterParams.ajaxUrl + "?action=depicter/document/store", {
                                method: \'post\',
                                body: data,
                                headers: {
                                    \'X-DEPICTER-CSRF\': depicterParams.token
                                }
                            })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.hits) {
                                    event.target.disabled = "true";
                                    event.target.querySelector(".btn-label").innerHTML = depicterParams.publishedText;
                                    var $depicterNoticeWrapper = document.querySelector(".depicter-notice-wrapper");
                                    if ( $depicterNoticeWrapper ) {
                                        $depicterNoticeWrapper.style.display = "none";
                                    }
                                }
                                event.target.querySelector(".btn-label").style.display = "inline-block";
                                event.target.querySelector(".depicter-state-icon").style.display = "none";
                                el.querySelector(".depicter-notice-txts").style.display = "none";
                            }).catch((error) => {
                                console.error(error);
                            });
                    });

                    el.querySelector(".btn-label").addEventListener( "click", function(event) {
                        event.target.parentNode.click();
                    });
                }
            });

        </script>';
    }

    /**
     * Get slider buttons control field markup
     *
     * @param array $settings
     * @param string $value
     * @return string
     */
    public function slider_buttons_markup( $settings, $value ) {
        return \Depicter::view('admin/notices/builders-draft-notice')->with('view_args', $settings)->toString();
    }
}

new Module();
