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

		// Factory object
		//$this->plugin->factory = new Factory($this->plugin);
	}



	public function onActivation($plugin, $network_wide) {
		update_option('plblst_check_activation', 1, true);
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