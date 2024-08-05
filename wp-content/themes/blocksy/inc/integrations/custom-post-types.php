<?php

namespace Blocksy;

class CustomPostTypes {
	private $supported_post_types = null;

	public function wipe_caches() {
		$this->supported_post_types = null;
	}

	public function get_supported_post_types() {
		if ($this->supported_post_types === null) {
			$potential_post_types = array_keys(get_post_types([
				'public'   => true,
				'_builtin' => false,
			]));

			$potential_post_types = array_values(array_diff($potential_post_types, [
				'cmplz-processing',
				'cmplz-dataleak',
				'iamport_payment',
				'wpcw_achievements',
				'zoom-meetings',
				'pafe-formabandonment',
				'pafe-form-database',
				'pafe-form-booking',
				'piotnetforms',
				'piotnetforms-aban',
				'piotnetforms-data',
				'piotnetforms-book',
				'piotnetforms-fonts',
				'jet-popup',
				'jet-smart-filters',
				'jet-theme-core',
				'jet-woo-builder',
				'jet-engine',
				'jet-engine-booking',
				'jet_options_preset',
				'jet-menu',
				'adsforwp',
				'adsforwp-groups',
				'popup',
				'ct_content_block',
				'product',
				'elementor_library',
				'brizy_template',
				'editor-story',
				'forum',
				'topic',
				'reply',
				'blockslider',
				'mailpoet_page',
				'ha_nav_content',
				'course',
				'lesson',
				'atbdp_orders',
				'at_biz_dir',
				'gspbstylebook',


				// tutor lms
				'tutor_quiz',
				'tutor_assignments',
				'tutor_zoom_meeting',

				// Lifter LMS
				'llms_quiz',
				'llms_membership',
				'llms_certificate',
				'llms_my_certificate',

				// learn dash
				'ld-exam',
				'groups',

				'tribe_events',
				'tribe_event_series',
				'tribe_venue',
				'tribe_organizer',

				'testimonial',
				'frm_display',
				'mec_esb',
				'mec-events',

				'sfwd-assignment',
				'sfwd-essays',
				'sfwd-transactions',
				'sfwd-certificates',
				'e-landing-page',
				'zion_template',
				'pafe-fonts',
				'pgc_simply_gallery',
				'pdfviewer',
				'da_image',


				// thrive
				'tcb_lightbox',
				'tcb_symbol',
				'tvo_display_post',
				'tvo_capture',
				'tvo_display',

				'woolentor-template',
				'shopengine-template',
				'elementskit_content',
				'elementskit_template',
				'elementskit_widget',
				'ha_library',
			]));

			$this->supported_post_types = array_unique(apply_filters(
				'blocksy:custom_post_types:supported_list',
				$potential_post_types
			));
		}

		return $this->supported_post_types;
	}

	public function is_supported_post_type() {
		global $post;
		global $wp_taxonomies;
		global $wp_query;

		$post_type = get_post_type($post);

		$tax_query = $wp_query->tax_query;


		if (
			$tax_query
			&&
			! is_home()
			&&
			! is_post_type_archive()
		) {
			$tax = null;

			foreach ($tax_query->queries as $taxonomy) {
				if (isset($taxonomy['taxonomy'])) {
					$taxonomy_obj = get_taxonomy($taxonomy['taxonomy']);

					if ($taxonomy_obj->public) {
						$tax = $taxonomy['taxonomy'];
						break;
					}
				}
			}

			if ($tax && ! is_array($tax) && isset($wp_taxonomies[$tax])) {
				$all_tax_post_types = $wp_taxonomies[$tax]->object_type;

				if (
					! empty($all_tax_post_types)
					&&
					isset($all_tax_post_types[0])
				) {
					$post_type = $all_tax_post_types[0];
				}
			}
		}

		if (! $post_type) {
			$post_type = get_query_var('post_type');
		}

		$post_type = apply_filters(
			'blocksy:custom_post_types:current_post_type:compute',
			$post_type
		);

		if (in_array($post_type, $this->get_supported_post_types())) {
			return $post_type;
		}

		return null;
	}
}

