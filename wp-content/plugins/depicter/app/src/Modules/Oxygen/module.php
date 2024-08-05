<?php

namespace Depicter\Modules\Oxygen;

class Module extends \OxyEl
{

    public function init() {
        if ( isset( $_GET['ct_builder'] ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueueDepicterAssets' ] );
        }
    }

    public function enqueueDepicterAssets() {
        \Depicter::front()->assets()->enqueueStyles();
        \Depicter::front()->assets()->enqueueScripts(['player', 'iframe-resizer']);
    }

    // Define the element's name.
    public function name() {
        return __("Depicter", 'depicter');
    }

    // Element options
    public function options(){

        return array(
            //"wrapper_class" => $classes,
            "server_side_render" => true
        );

    }

    public function render( $options, $defaults, $content ){

        if ( !empty( $options['slider_id'] ) ) {
            $list = $this->getSlidersList();
            $sliderID = count( $list ) > 2 ? $options['slider_id'] : array_search( $options['slider_id'], $list );

            echo "<style>.oxy-depicter{ width: 100%; }</style>";
            if ( isset( $_GET['action'] ) && $_GET['action'] == 'oxy_render_oxy-depicter' ) {
                echo '<iframe id="sliderIframe-' . $sliderID . '" style="width: 1px;min-width: 100%;" src="' . admin_url('admin-ajax.php') . '?action=depicter/document/preview&depicter-csrf=' . \Depicter::csrf()->getToken( \Depicter\Security\CSRF::EDITOR_ACTION ) . '&ID=' . $sliderID . '&status=draft|publish&gutenberg=true"></iframe>';
                echo "<script>iFrameResize({}, '#sliderIframe-" . $sliderID . "')</script>";
            } else {
                echo \Depicter::front()->render()->document( $sliderID, [ 'echo' => false ] );
            }
		} else {
			echo esc_html__('Please select a Depicter slider','depicter' );
		}

    }

    public function getSlidersList() {
        $list = [
			0 => __( 'Select Slider', 'depicter' )
		];
        $documents = \Depicter::documentRepository()->select( ['id', 'name', 'status'] )->orderBy('modified_at', 'DESC')->findAll()->get();
		$documents = $documents ? $documents->toArray() : [];
        foreach( $documents as $document ) {
			$list[ $document['id'] ] = "[#{$document['id']}]: " . $document['name'];
		}
        return $list;
    }

    public function controls(){

        $list = $this->getSlidersList();

        // Select Slider
        $this->addOptionControl(
            array(
                "type" => 'dropdown',
                "name" => __("Select Slider", 'depicter' ),
                "slug" => 'slider_id',
            )
        )->setValue( $list )->rebuildElementOnChange();

        if ( current_user_can( 'access_depicter' ) ) {
            $documents = \Depicter::documentRepository()->select( ['id', 'name', 'status'] )->orderBy('modified_at', 'DESC')->findAll()->get();
            $documents = $documents ? $documents->toArray() : [];
            foreach( $documents as $document ) {

                $this->addOptionControl( [
                    "type" => 'custom_control',
                    "name" => __('Publish or Edit Slider', 'depicter' ),
                    "slug" => 'slider_btns_' . $document['id'],
                    "condition" => 'slider_id=' . $document['id'],
                ])->setHTML( $this->slider_buttons_markup([
                    'isPublishedBefore' => \Depicter::documentRepository()->isPublishedBefore( $document['id'] ),
                    'documentStatus'    => $document['status'],
                    'documentID'        => $document['id']
                ]) );
            }
        }
    }

    /**
     * Get slider buttons control field markup
     *
     * @param array $settings
     * @param string $value
     * @return string
     */
    public function slider_buttons_markup( $settings ) {
        return \Depicter::view('admin/notices/builders-draft-notice')->with('view_args', $settings)->toString();
    }

}

new Module();
