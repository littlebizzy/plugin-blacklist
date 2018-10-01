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

		// Save future data
		$this->saveFuture();

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

		// Save again future
		$this->saveFuture();
	}



	/**
	 * Save future data and message
	 */
	private function saveFuture() {
		update_option($this->plugin->prefix.'_future_plugins', $this->plugin->factory->disabler()->future(), true);
		update_option($this->plugin->prefix.'_future_message', $this->plugin->factory->disabler()->futureMessage(), true);
	}



}