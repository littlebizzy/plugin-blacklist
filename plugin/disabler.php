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
	 * Update the plugins list
	 */
	public function update($cron = false) {

		// Retrieve active plugins
		$plugins = wp_get_active_and_valid_plugins();
		if (empty($plugins) || !is_array($plugins)) {
			return false;
		}

		// Get the blacklist
		// ... $blacklist = $this->plugin->factory->blacklist()->read();

		// Initialize
		$allowed = [];
		$deactivated = [];

		// Enum plugins paths
		foreach ($plugins as $path) {

			// Compare with blaclist
			if (false !== stripos($path, '404-to-homepage')) {

				// Check file
				if (@file_exists($path)) {

					// Detected
					$deactivated[] = $path;

					// Next
					continue;
				}
			}

			// Allowed
			$allowed[] = $path;
		}

		// Check matches
		if (empty($deactivated)) {
			return false;
		}

		// Update plugins
		update_option('active_plugins', $allowed);

		// Done
		return $deactivated;
	}



}