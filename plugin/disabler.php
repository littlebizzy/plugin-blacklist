<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Plugin;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Disabler class
 *
 * @package Plugin Blacklist
 * @subpackage Plugin
 */
final class Disabler extends Helpers\Singleton {



	/**
	 * Deactivated plugins
	 */
	private $future = [];
	private $deactivated = [];



	/**
	 * Future deactivation message
	 */
	private $futureMessage = '';



	/**
	 * Current plugins
	 */
	private $plugins;



	/**
	 * Look for blacklisted paths and updates the plugin list
	 */
	public function byPath() {

		// Cache flag
		static $updated;
		if (isset($updated)) {
			return false;
		}

		// Set status
		$updated = true;

		// Retrieve active plugins
		if (!isset($this->plugins)) {
			$this->plugins = wp_get_active_and_valid_plugins();
		}

		// Check active plugins
		if (empty($this->plugins) || !is_array($this->plugins)) {
			return false;
		}

		// Get the blacklist
		if (false === ($blacklist = $this->plugin->factory->blacklist()->read())) {
			return false;
		}

		// Copy the future message
		$this->futureMessage = $this->plugin->factory->blacklist()->getSectionString('message future');

		// Prepare blacklist
		if (empty($blacklist['path']) || !is_array($blacklist['path'])) {
			$blacklist['path'] = [];
		}

		// Prepare future
		if (empty($blacklist['path future']) || !is_array($blacklist['path future'])) {
			$blacklist['path future'] = [];
		}

		// Initialize
		$allowed = [];

		// Expected path
		$prefix = WP_PLUGIN_DIR.'/';
		$prefixLength = strlen($prefix);

		// Enum plugins paths
		foreach ($this->plugins as $path) {


			/* Validation */

			// Check path start
			if (0 !== strpos($path, $prefix)) {
				continue;
			}

			// Set relative
			$relativePath = substr($path, $prefixLength);
			if (empty($relativePath)) {
				continue;
			}


			/* By Path blacklist */

			// Find in blacklist
			$match = false;
			foreach ($blacklist['path'] as $key => $item) {

				// Find in blacklist path
				if (false !== strpos('/'.$relativePath, $item)) {

					// Check file
					if (@file_exists($path)) {

						// Matched
						$match = true;

						// Detected
						$this->deactivated[] = $path;

						// Done
						break;
					}
				}
			}

			// Check if allowed
			if (!$match) {
				$allowed[] = $relativePath;
			}


			/* Future blacklist */

			// Find in blacklist future
			foreach ($blacklist['path future'] as $key => $item) {

				// Find in blacklist path
				if (false !== strpos('/'.$relativePath, $item)) {

					// Check file
					if (@file_exists($path)) {

						// Detected
						$this->future[] = $path;

						// Done
						break;
					}
				}
			}
		}

		// Check matches
		if (empty($this->deactivated)) {
			return false;
		}

		// Update plugins
		$this->plugins = $allowed;
		update_option('active_plugins', $allowed);

		// Done
		return true;
	}



	/**
	 * Look for blacklisted functions and classes and updates the plugin list
	 */
	public function byCode() {



	}



	/**
	 * Return deactivated plugins
	 */
	public function deactivated() {
		return $this->deactivated;
	}



	/**
	 * Return future plugins
	 */
	public function future() {
		return $this->future;
	}



	/**
	 * Future deactivation message
	 */
	public function futureMessage() {
		return $this->futureMessage;
	}



}