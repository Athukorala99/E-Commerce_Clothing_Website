<?php

namespace Blocksy;

class Database {
	private $mods = '__EMPTY__';

	public function get_theme_mod($name, $default_value = false) {
		if (
			is_admin()
			||
			is_customize_preview()
			||
			wp_doing_ajax()
			||
			$this->mods === '__EMPTY__'
		) {
			$this->mods = get_theme_mods();
		}

		$value = $default_value;

		if (isset($this->mods[$name])) {
			$value = $this->mods[$name];
		}

		/** This filter is documented in wp-includes/theme.php */
		return apply_filters("theme_mod_{$name}", $value);
	}

	public function wipe_cache() {
		$this->mods = '__EMPTY__';
	}
}

