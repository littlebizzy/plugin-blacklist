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
	 * Disabler object copy
	 */
	private $disabler;



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {

		// Update the plugins list by path
		$this->disabler = $this->plugin->factory->disabler();
		$this->disabler->byPath();

		// Save future data
		$this->saveFuture();

		// Second round
		add_action('plugins_loaded', [$this, 'onPluginsLoaded']);
	}



	/**
	 * Check plugins after loading
	 */
	public function onPluginsLoaded() {

		// Now find in code
		$this->disabler->byCode();

		// Save again future
		$this->saveFuture();
	}



	/**
	 * Save future data and message
	 */
	private function saveFuture() {
		update_option($this->plugin->prefix.'_future_plugins', $this->disabler->future(), true);
		update_option($this->plugin->prefix.'_future_message', $this->disabler->futureMessage(), true);
	}



}