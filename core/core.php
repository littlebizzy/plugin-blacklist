<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Core;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Core class
 *
 * @package Plugin Blacklist
 * @subpackage Core
 */
final class Core extends Helpers\Singleton {



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {

		// Factory object
		$this->plugin->factory = new Factory($this->plugin);

		// Configure cron requests
		$this->plugin->factory->cron();

		// Check plugin activation
		$this->plugin->factory->activation();

		// Admin notices
		if (is_admin()) {

			// Avoid AJAX requests
			if (!(defined('DOING_AJAX') && DOING_AJAX)) {
				$this->plugin->factory->notices();
			}
		}
	}



}