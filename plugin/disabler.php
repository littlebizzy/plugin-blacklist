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
	 * Plugins path root
	 */
	private $prefix;
	private $prefixLength;



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {
		$this->prefix = WP_PLUGIN_DIR.'/';
		$this->prefixLength = strlen($this->prefix);
	}



	/**
	 * Look for blacklisted paths and updates the plugin list
	 */
	public function byPath() {

		// Retrieve active plugins
		$plugins = wp_get_active_and_valid_plugins();
		if (empty($plugins) || !is_array($plugins)) {
			return false;
		}

		// Get the blacklist
		if (false === ($blacklist = $this->plugin->factory->blacklist()->read())) {
			return false;
		}

		// Copy the future message
		$this->futureMessage = $this->plugin->factory->blacklist()->getSectionString('message future');

		// Prepare blacklist items
		$keys = ['path', 'path future'];
		foreach ($keys as $key) {
			if (empty($blacklist[$key]) || !is_array($blacklist[$key])) {
				$blacklist[$key] = [];
			}
		}

		// Init
		$allowed = [];

		// Enum plugins paths
		foreach ($plugins as $path) {


			/* Validation */

			// Check valid path start
			if (0 !== strpos($path, $this->prefix)) {
				continue;
			}

			// Set relative
			$relativePath = substr($path, $this->prefixLength);
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

						// Detected
						$match = true;

						// Deactivate
						$this->deactivated[] = $path;

						// Done
						break;
					}
				}
			}

			// Check allowed
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

						// Do not add deactivated plugins
						if (!in_array($path, $this->deactivated)) {

							// Detected
							$this->future[] = $path;
						}

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
		update_option('active_plugins', $allowed);

		// Done
		return true;
	}



	/**
	 * Look for blacklisted functions and classes and updates the plugin list
	 */
	public function byCode() {

		// Retrieve active plugins
		$plugins = wp_get_active_and_valid_plugins();
		if (empty($plugins) || !is_array($plugins)) {
			return false;
		}

		// Get the blacklist
		if (false === ($blacklist = $this->plugin->factory->blacklist()->read())) {
			return false;
		}

		// Copy the future message
		$this->futureMessage = $this->plugin->factory->blacklist()->getSectionString('message future');

		// Prepare blacklist items
		$keys = ['classes', 'functions', 'classes future', 'functions future'];
		foreach ($keys as $key) {
			if (empty($blacklist[$key]) || !is_array($blacklist[$key])) {
				$blacklist[$key] = [];
			}
		}


		/* Extract directories */

		// Plugin directories
		$directories = [];
		$directoriesFuture = [];

		// Check classes
		$this->classes($blacklist['classes'], $directories);
		$this->classes($blacklist['classes future'], $directoriesFuture);

		// Check functions
		$this->functions($blacklist['functions'], $directories);
		$this->functions($blacklist['functions future'], $directoriesFuture);

		// Abort if no directories involved
		if (empty($directories) && empty($directoriesFuture)) {
			return false;
		}


		/* Cast plugin paths to relative paths */

		// Populate plugins relative path
		$pluginsRel = [];
		foreach ($plugins as $path) {

			// Check valid path start
			if (0 !== strpos($path, $this->prefix)) {
				continue;
			}

			// Set relative
			$relativePath = substr($path, $this->prefixLength);
			if (empty($relativePath)) {
				continue;
			}

			// Done
			$pluginsRel[$relativePath] = $path;
		}


		/* Detect plugins by directory */

		// Enum directories
		foreach ($directories as $directory) {

			// Enum plugins
			foreach ($pluginsRel as $relativePath => $path) {

				// Check path start
				if (0 === strpos($relativePath, $directory.'/')) {

					// Check if already deactivated
					if (!in_array($path, $this->deactivated)) {

						// Check file
						if (@file_exists($path)) {
							$this->deactivated[] = $path;
						}
					}

					// Done
					break;
				}
			}
		}

		// Enum directories future
		foreach ($directoriesFuture as $directory) {

			// Enum plugins
			foreach ($pluginsRel as $relativePath => $path) {

				// Check path start
				if (0 === strpos($relativePath, $directory.'/')) {

					// Check if already deactivated
					if (!in_array($path, $this->deactivated) &&
						!in_array($path, $this->future)) {

						// Check file
						if (@file_exists($path)) {
							$this->future[] = $path;
						}
					}

					// Done
					break;
				}
			}
		}


		// Abort if no intermediate results
		if (empty($this->future) && empty($this->deactivated)) {
			return false;
		}


		/* Save plugins by path */

		// Check deactivated plugins
		if (!empty($this->deactivated)) {

			// Prepare allowed
			$allowed = [];
			foreach ($pluginsRel as $relativePath => $path) {
				if (!in_array($path, $this->deactivated)) {
					$allowed[] = $relativePath;
				}
			}

			// Update plugins
			update_option('active_plugins', $allowed);
		}

		// Done
		return true;
	}



	/**
	 * Extract the plugin directory by the class path detected
	 */
	private function classes($classes, &$directories) {

		// Enum classes
		foreach ($classes as $key => $class) {

			// Check class
			if (!class_exists($class)) {
				 continue;
			}

			// Extract path
			$reflection = new \ReflectionClass($class);
			if (false === ($path = $reflection->getFileName())) {
				continue;
			}

			// Extract directory
			if (false !== ($directory = $this->directory($path))) {
				$directories[] = $directory;
			}
		}
	}



	/**
	 * Extract the plugin directory by the function path detected
	 */
	private function functions($functions, &$directories) {

		// Enum functions
		foreach ($functions as $key => $function) {

			// Check class
			if (!function_exists($function)) {
				continue;
			}

			// Extract path
			$reflection = new \ReflectionFunction($function);
			if (false === ($path = $reflection->getFileName())) {
				continue;
			}

			// Extract directory
			if (false !== ($directory = $this->directory($path))) {
				$directories[] = $directory;
			}
		}
	}



	/**
	 * Extract directory from class
	 */
	private function directory($path) {

		// Check valid plugin
		if (0 !== strpos($path, $this->prefix)) {
			false;
		}

		// Set relative
		$relativePath = substr($path, $this->prefixLength);
		if (empty($relativePath)) {
			false;
		}

		// Extract first directory
		$directory = explode('/', trim($relativePath, '/'));
		return $directory[0];

		// Check directory
		if (!in_array($directory, $this->directories)) {
			$this->directories[] = $directory;
		}
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