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
		//add_action('activate_plugin', [$this, 'activatePlugin'], 10 ,2); //do_action( 'activate_plugin', $plugin, $network_wide );

		// Factory object
		//$this->plugin->factory = new Factory($this->plugin);
	}


	public function activatePlugin($plugin, $network_wide) {

		/* if (function_exists('ntfthp_is_frontend')) {
			$refFunc = new \ReflectionFunction('ntfthp_is_frontend');
			error_log($refFunc->getFileName());
		} */

		// echo 'Unallowed plugin'; // <-- disables plugin activation
		//error_log($plugin); // file
		//error_log($network_wide); // boolean
	}


	public function pluginsLoaded() {

		//global $wp_plugin_paths;
		//print_r($wp_plugin_paths);die;

		//$plugins = wp_get_active_and_valid_plugins();
		//print_r($plugins);die;
	}



}