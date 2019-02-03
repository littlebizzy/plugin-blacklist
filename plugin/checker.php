<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Plugin;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Checker class
 *
 * @package Plugin Blacklist
 * @subpackage Plugin
 */
final class Checker extends Helpers\Singleton {



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {

		// Update the plugins list by path
		$this->plugin->factory->disabler()->byPath();

		// Save future and pause data
		$this->saveFutureAndPause();

		// Check cron mode
		if (defined('DOING_CRON') && DOING_CRON) {

			// Already executed the plugins_loaded hook,
			// so we can call directly this function.
			$this->onPluginsLoaded();

		// Normal mode
		} else {

			// Second round
			add_action('plugins_loaded', [$this, 'onPluginsLoaded']);
		}
	}



	/**
	 * Check plugins after loading
	 */
	public function onPluginsLoaded() {

		// Now find in code
		$this->plugin->factory->disabler()->byCode();

		// Save again future and pause
		$this->saveFutureAndPause();
	}



	/**
	 * Save future and pause data and message
	 */
	private function saveFutureAndPause() {
		update_option($this->plugin->prefix.'_future_plugins', $this->plugin->factory->disabler()->future(), true);
		update_option($this->plugin->prefix.'_future_message', $this->plugin->factory->disabler()->futureMessage(), true);
		update_option($this->plugin->prefix.'_pause_plugins', $this->plugin->factory->disabler()->pause(), true);
		update_option($this->plugin->prefix.'_pause_message', $this->plugin->factory->disabler()->pauseMessage(), true);
	}



}