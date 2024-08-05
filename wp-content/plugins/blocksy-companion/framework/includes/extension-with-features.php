<?php

namespace Blocksy;

trait ExtensionWithFeatures {
	public function getSlug() {
		$class = get_class($this);

		return str_replace(
			'blocksy-extension-',
			'',
			strtolower(
				preg_replace(
					'/[A-Z]([A-Z](?![a-z]))*/',
					'-$0',
					lcfirst($class)
				)
			)
		);
	}

	public function getFeatures() {
		return [1, 2, 3];
	}
}

