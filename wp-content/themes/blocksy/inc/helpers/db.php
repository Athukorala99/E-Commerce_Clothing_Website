<?php

function blocksy_get_theme_mod($name, $default_value = false) {
	return blocksy_manager()->db->get_theme_mod($name, $default_value);
}
