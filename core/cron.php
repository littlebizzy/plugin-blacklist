<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Core;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Cron class
 *
 * @package Plugin Blacklist
 * @subpackage Core
 */
final class Cron extends Helpers\Singleton {



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {

		// Compose event name
		$event = $this->plugin->prefix.'_plugins_check';

		// Handle plugins check hook
		add_action($event, [$this, 'onSchedule']);

		// Hourly scheduled event
		if (!wp_next_scheduled($event)) {
			wp_schedule_event(time(), 'hourly', $event);
		}
	}



	/**
	 * Start the checker
	 */
	public function onSchedule() {
		$this->plugin->factory->checker();
	}



}