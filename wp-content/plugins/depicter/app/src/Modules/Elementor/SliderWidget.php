<?php
namespace Depicter\Modules\Elementor;

use Elementor\Plugin;
use Depicter\Document\CSS\Selector;
use Elementor\Controls_Manager;
use Elementor\Widget_Base;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor 'Slider' widget.
 *
 * Elementor widget that displays an 'Slider' with lightbox.
 *
 * @since 1.0.0
 */
class SliderWidget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve 'Slider' widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'depicter_slider';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve 'Slider' widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __('Depicter Slider', 'depicter' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Slider widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-post-slider depicter-badge';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_categories() {
		return ['basic'];
	}

	/**
     * load dependent styles
     *
     * @return array
     */
    public function get_style_depends() {
        $styles = \Depicter::front()->assets()->enqueueStyles();
        return array_keys( $styles );
    }

    /**
     * load dependent scripts
     *
     * @return array
     */
    public function get_script_depends() {
        $scripts = \Depicter::front()->assets()->enqueueScripts();
        return array_keys( $scripts );
    }


	public function getSlidersList() {
		$list = [
			'0' => __( 'Select Slider', 'depicter' )
		];
		$documents = \Depicter::documentRepository()->select( ['id', 'name'] )->orderBy('modified_at', 'DESC')->findAll()->get();
		$documents = $documents ? $documents->toArray() : [];
		foreach( $documents as $document ) {
			$list[ "#" . $document['id'] ] = "[#{$document['id']}]: " . $document['name'];
		}
		return $list;
	}

	/**
	 * Register 'Slider' widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		/*-----------------------------------------------------------------------------------*/
		/*  slider_section
		/*-----------------------------------------------------------------------------------*/

		$this->start_controls_section(
			'slider_section',
			array(
				'label'      => __('Slider', 'depicter' ),
			)
		);

		$this->add_control(
			'slider_id',
			[
				'label'       => __('Select a Depicter slider','depicter' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => 'true',
				'options'     => $this->getSlidersList(),
				'default'     => '0'
			]
		);

		$documents = \Depicter::documentRepository()->select( ['id', 'name', 'status'] )->orderBy('modified_at', 'DESC')->findAll()->get();
		$documents = $documents ? $documents->toArray() : [];
		foreach( $documents as $key => $document ) {

			$args = [
                'isPublishedBefore' => \Depicter::documentRepository()->isPublishedBefore( $document['id'] ),
                'documentStatus' 	=> $document['status'],
				'documentID'        => $document['id']
            ];

			$markup = \Depicter::view('admin/notices/builders-draft-notice')->with('view_args', $args)->toString();

			if ( current_user_can( 'access_depicter' ) ) {
				$this->add_control(
					'slider_control_buttons_' . $key,
					[
						'type' => Controls_Manager::RAW_HTML,
						'raw' => $markup,
						'condition' => [
							'slider_id' => '#' . $document['id']
						]
					]
				);
			}
		}

		$this->end_controls_section();
	}

	/**
	 * Render image box widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings   = $this->get_settings_for_display();

		if ( $settings['slider_id'] ) {
			echo \Depicter::front()->render()->document( ltrim( $settings['slider_id'], '#' ) );
		} else {
			echo esc_html__('Please select a Depicter slider','depicter' );
		}

	}
}
