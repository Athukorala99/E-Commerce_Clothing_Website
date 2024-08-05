<?php

add_filter(
	'tribe_get_option_tribeEventsTemplate',
	function ($value) {
		return 'default';
	}
);

add_filter(
	'tec_events_display_settings_tab_fields',
	function ($fields) {
		if (isset($fields['tribeEventsTemplate'])) {
			$fields['tribeEventsTemplate']['conditional'] = false;
		}

		return $fields;
	}
);

add_filter(
	'tribe_events_views_v2_view_html_classes',
	function ($classes) {
		return array_filter($classes, function ($class) {
			return $class !== 'alignwide';
		});
	},
	50
);
