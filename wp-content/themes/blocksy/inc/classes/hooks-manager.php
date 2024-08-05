<?php

namespace Blocksy;

class WpHooksManager {
	private $tokens = [];

	public function redirect_callbacks($args = []) {
		// TODO: token is unused for now because we dont need the revert
		// operation for callbacks redirection. But at some point we might
		// need it and it will be here for us.
		$args = wp_parse_args(
			$args,
			[
				'token' => '',
				'source' => [],
				'destination' => '',

				'priority_min' => PHP_INT_MIN,
				'priority_max' => PHP_INT_MAX
			]
		);

		blocksy_assert_args($args, ['token', 'source', 'destination']);

		if (
			(
				$args['priority_min'] !== PHP_INT_MIN
				||
				$args['priority_max'] !== PHP_INT_MAX
			) && count($args['source']) > 1
		) {
			throw new \Error(
				"You can't use priority_min or priority_max with multiple sources."
			);
		}

		global $wp_filter;

		if (! isset($wp_filter[$args['destination']])) {
			$wp_filter[$args['destination']] = new \WP_Hook();
		}

		/*
		foreach ($args['source'] as $source_id) {
			if (! isset($wp_filter[$source_id])) {
				continue;
			}

			$this->tokens[$args['token']][$source_id] = $wp_filter[$source_id];
		}

		$this->tokens[$args['token']][$args['destination']] = $wp_filter[$args['destination']]->callbacks;
		 */

		foreach ($args['source'] as $source_id) {
			if (! isset($wp_filter[$source_id])) {
				continue;
			}

			$source_callbacks = $wp_filter[$source_id]->callbacks;

			foreach ($source_callbacks as $priority => $callbacks) {
				if ($priority < $args['priority_min']) {
					continue;
				}

				if ($priority > $args['priority_max']) {
					continue;
				}

				foreach ($callbacks as $callback_id => $callback) {
					$wp_filter[$source_id]->remove_filter(
						$source_id,
						$callback['function'],
						$priority
					);

					$wp_filter[$args['destination']]->add_filter(
						$args['destination'],
						$callback['function'],
						$priority,
						$callback['accepted_args']
					);
				}
			}

			// $this->tokens[$args['token']][$source_id] = $wp_filter[$source_id];
		}
	}

    public function disable_callbacks($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'token' => '',
				'source' => []
			]
		);

		blocksy_assert_args($args, ['token', 'source']);

		global $wp_filter;

		foreach ($args['source'] as $source_id) {
			if (! isset($wp_filter[$source_id])) {
				continue;
			}

			$this->tokens[$args['token']][$source_id] = $wp_filter[$source_id];
			unset($wp_filter[$source_id]);
		}
    }

    public function enable_callbacks($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'token' => '',
				'source' => []
			]
		);

		blocksy_assert_args($args, ['token', 'source']);

		global $wp_filter;

		foreach ($args['source'] as $source_id) {
			if (! isset($this->tokens[$args['token']][$source_id])) {
				continue;
			}

			$wp_filter[$source_id] = $this->tokens[$args['token']][$source_id];

			unset($this->tokens[$args['token']][$source_id]);
		}
    }

	// For now callback rolling is not needed, but it may be needed eventually
	/*
	public function rollback_callbacks($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'token' => '',
				'source' => '',
				'destination' => ''
			]
		);

		blocksy_assert_args($args, ['token', 'source', 'destination']);

		if (
			! isset($this->tokens[$args['token']])
			||
			! isset($this->tokens[$args['token']][$args['destination']])
		) {
			return;
		}

		global $wp_filter;

		foreach ($args['source'] as $source_id) {
			if (! isset($this->tokens[$args['token']][$source_id])) {
				continue;
			}

			$wp_filter[$source_id] = $this->tokens[$args['token']][$source_id];
		}

		$wp_filter[$args['destination']]->callbacks = $this->tokens[$args['token']][$args['destination']];
	}
	 */
}

