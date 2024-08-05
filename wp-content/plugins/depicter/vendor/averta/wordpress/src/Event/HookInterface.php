<?php
namespace Averta\WordPress\Event;


interface HookInterface
{
	/**
	 * Adds a callback function to a hook.
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 * @param int      $accepted_args
	 *
	 * @return mixed
	 */
	public function add( $hook_name, $callback, $priority = 10, $accepted_args = 1 );

	/**
	 * Removes a callback function from a hook.
	 *
	 * @param string   $hook_name
	 * @param callable $callback
	 * @param int      $priority
	 *
	 * @return mixed
	 */
	public function remove( $hook_name, $callback, $priority = 10 );

	/**
	 * Checks if any action has been registered for a hook.
	 *
	 * @param string        $hook_name
	 * @param callable|bool $callback
	 *
	 * @return mixed
	 */
	public function has( $hook_name, $callback = false );

	/**
	 * Retrieves the number of times an hook-event has been fired during the current request.
	 *
	 * @param string $hook_name
	 *
	 * @return mixed
	 */
	public function did( $hook_name );
}
