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
	private $future;
	private $deactivated;



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {
		$this->future = [];
		$this->deactivated = [];
	}



	/**
	 * Update the plugins list
	 */
	public function update($cron = false) {

		// Cache flag
		static $updated;
		if (isset($updated)) {
			return;
		}

		// Set status
		$updated = true;

		// Retrieve active plugins
		$plugins = wp_get_active_and_valid_plugins();
		if (empty($plugins) || !is_array($plugins)) {
			return false;
		}

		// Get the blacklist
		// ... $blacklist = $this->plugin->factory->blacklist()->read();

		// Initialize
		$allowed = [];

		// Expected path
		$prefix = WP_PLUGIN_DIR.'/';
		$prefixLength = strlen($prefix);

		// Enum plugins paths
		foreach ($plugins as $path) {

			// Check path start
			if (0 !== strpos($path, $prefix)) {
				continue;
			}

			// Set relative
			$relativePath = substr($path, $prefixLength);
			if (empty($relativePath)) {
				continue;
			}

			// Compare with blaclist
			if (false !== stripos('/'.$relativePath, '404-to-homepage')) {

				// Check file
				if (@file_exists($path)) {

					// Detected
					$this->deactivated[] = $path;

					// Next
					continue;
				}
			}

			// Allowed
			$allowed[] = $relativePath;
		}

		// Check matches
		if (empty($this->deactivated)) {
			return false;
		}

		// Update plugins
		update_option('active_plugins', $allowed);

		// Done
		return true;
	}



	/**
	 * Return deactivated plugins
	 */
	public function deactivated() {
		return $this->deactivated;
	}



}