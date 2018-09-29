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

		//add_action('plugins_loaded', [$this, 'pluginsLoaded']);
		add_action('activate_plugin', [$this, 'onActivation'], 10 ,2); //do_action( 'activate_plugin', $plugin, $network_wide );

		$activated = get_option('plblst_check_activation');
		if (!empty($activated)) {

			// No more checks
			update_option('plblst_check_activation', '', true);

			$plugins = wp_get_active_and_valid_plugins();
			if (empty($plugins) || !is_array($plugins)) {
				return;
			}

			/** This goes to another class */
			$allowed = [];
			$matches = false;
			foreach ($plugins as $path) {
				if (false !== stripos($path, '404-to-homepage')) {
					if (@file_exists($path)) {

						// At least one match
						$matches = true;

						// Next
						continue;
					}
				}
				$allowed[] = $path;
			}

			if ($matches) {
				update_option('active_plugins', $allowed);
			}
		}

		// Factory object
		//$this->plugin->factory = new Factory($this->plugin);
	}



	public function onActivation($plugin, $network_wide) {
		update_option('plblst_check_activation', '1', true);
	}



	public function pluginsLoaded() {

		//global $wp_plugin_paths;
		//print_r($wp_plugin_paths);die;

		//$plugins = wp_get_active_and_valid_plugins();
		//print_r($plugins);die;
	}



	public function notices() {

		?><div class="notice notice-error is-dismissible">

			<p>Plugin not allowed</p>

		</div><?php

	}



}