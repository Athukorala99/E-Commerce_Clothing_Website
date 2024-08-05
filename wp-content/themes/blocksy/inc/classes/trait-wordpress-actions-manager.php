<?php

namespace Blocksy;

trait WordPressActionsManager {
	public function attach_hooks($args = []) {
		if (! isset($this->actions) && ! isset($this->filters)) {
			throw new \Error(
				'Please define $actions or $filters properties on the ' . get_class($this) . ' class.'
			);
		}

		$args = wp_parse_args($args, [
			'only' => [],
			'exclude' => []
		]);

		if (isset($this->actions)) {
			$this->attach_actions($args);
		}

		if (isset($this->filters)) {
			$this->attach_filters($args);
		}
	}

	private function attach_actions($args) {
		foreach ($this->actions as $action) {
			if (
				! empty($args['only'])
				&&
				! in_array($action['action'], $args['only'])
			) {
				continue;
			}

			if (in_array($action['action'], $args['exclude'])) {
				continue;
			}

			add_filter(
				$action['action'],
				[$this, $action['action']],
				isset($action['priority']) ? $action['priority'] : 10,
				isset($filter['args']) ? $filter['args'] : 1
			);
		}
	}

	private function attach_filters($args) {
		foreach ($this->filters as $filter) {
			if (
				! empty($args['only'])
				&&
				! in_array($filter['action'], $args['only'])
			) {
				continue;
			}

			if (in_array($filter['action'], $args['exclude'])) {
				continue;
			}

			add_action(
				$filter['action'],
				[$this, $filter['action']],
				isset($filter['priority']) ? $filter['priority'] : 10,
				isset($filter['args']) ? $filter['args'] : 1
			);
		}
	}

	public function detach_hooks() {
		if (isset($this->actions)) {
			foreach ($this->actions as $action) {
				remove_action(
					$action['action'],
					[$this, $action['action']],
					isset($action['priority']) ? $action['priority'] : 10
				);
			}
		}

		if (isset($this->filters)) {
			foreach ($this->filters as $filter) {
				remove_filter(
					$filter['action'],
					[$this, $filter['action']],
					isset($filter['priority']) ? $filter['priority'] : 10,
					isset($filter['args']) ? $filter['args'] : 1
				);
			}
		}
	}
}
