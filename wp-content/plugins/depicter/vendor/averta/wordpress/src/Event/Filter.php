<?php
namespace Averta\WordPress\Event;


class Filter implements HookInterface
{
	/**
	 * Adds a callback function to a filter hook.
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 * @param int      $accepted_args
	 *
	 * @return mixed
	 */
	public function add( $hook_name, $callback, $priority = 10, $accepted_args = 1 ){
		return add_filter( $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Removes a callback function from a filter hook.
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 *
	 * @return mixed
	 */
	public function remove( $hook_name, $callback, $priority = 10 ){
		return remove_filter( $hook_name, $callback, $priority );
	}

	/**
	 * Checks if any filter has been registered for a hook.
	 *
	 * @param string        $hook_name
	 * @param callable|bool $callback
	 *
	 * @return mixed
	 */
	public function has( $hook_name, $callback = false ){
		return has_filter( $hook_name, $callback );
	}

	/**
	 * Returns whether or not a filter hook is currently being processed.
	 *
	 * @param string $hook_name
	 *
	 * @return mixed
	 */
	public function did( $hook_name ){
		return doing_filter( $hook_name );
	}

	/**
	 * Calls the callback functions that have been added to a filter hook.
	 *
	 * @param string $hook_name
	 * @param        $value
	 *
	 * @return mixed
	 */
	public function do( $hook_name, $value ){
		return apply_filters( $hook_name, $value );
	}

	/**
	 * Calls the callback functions that have been added to a filter hook.
	 *
	 * @param string $hook_name
	 * @param        $value
	 *
	 * @return mixed
	 */
	public function apply( $hook_name, $value ){
		return apply_filters( $hook_name, $value );
	}
}
