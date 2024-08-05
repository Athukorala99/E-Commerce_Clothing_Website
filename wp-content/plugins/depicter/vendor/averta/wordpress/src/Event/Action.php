<?php
namespace Averta\WordPress\Event;


class Action implements HookInterface
{
	/**
	 * Adds a callback function to an action hook.
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 * @param int      $accepted_args
	 *
	 * @return mixed
	 */
	public function add( $hook_name, $callback, $priority = 10, $accepted_args = 1 ){
		return add_action( $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Removes a callback function from an action hook.
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 *
	 * @return mixed
	 */
	public function remove( $hook_name, $callback, $priority = 10 ){
		return remove_action( $hook_name, $callback, $priority );
	}

	/**
	 * Checks if any action has been registered for a hook.
	 *
	 * @param string        $hook_name
	 * @param callable|bool $callback
	 *
	 * @return mixed
	 */
	public function has( $hook_name, $callback = false ){
		return has_action( $hook_name, $callback );
	}

	/**
	 * Retrieves the number of times an action has been fired during the current request.
	 *
	 * @param string $hook_name
	 *
	 * @return mixed
	 */
	public function did( $hook_name ){
		return did_action( $hook_name );
	}

	/**
	 * Calls the callback functions that have been added to an action hook.
	 *
	 * @param string $hook_name
	 * @param mixed  $arg
	 *
	 * @return mixed
	 */
	public function do( $hook_name, ...$arg  ){
		return do_action( $hook_name, $arg );
	}
}
