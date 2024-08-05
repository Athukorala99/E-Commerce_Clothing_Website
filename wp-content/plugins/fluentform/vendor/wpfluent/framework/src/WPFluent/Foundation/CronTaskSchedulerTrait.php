<?php

namespace FluentForm\Framework\Foundation;

trait CronTaskSchedulerTrait
{
	public function addCronTask($hook, $recurrence, $args = [], $wp_error = false)
	{
		if (!wp_next_scheduled($hook)) {
			wp_schedule_event(time(), $recurrence, $hook, $args, $wp_error);
		}
	}

	public function removeCronTask($hook)
	{
		$timestamp = wp_next_scheduled($hook);
		wp_unschedule_event($timestamp, $hook);
	}
}
