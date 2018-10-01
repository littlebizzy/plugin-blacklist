<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Plugin;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Cron class
 *
 * @package Plugin Blacklist
 * @subpackage Plugin
 */
final class Activation extends Helpers\Singleton {



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {
		$this->hooks();
		$this->check();
	}



	/**
	 * Start the activation hooks
	 */
	private function hooks() {
		add_action('activate_plugin', [$this, 'onChange'], 10 ,2);
		add_action('deactivate_plugin', [$this, 'onChange'], 10 ,2);
	}



	/**
	 * Handle activation event
	 */
	public function onChange($plugin, $network_wide) {
		if (!$network_wide) {
			update_option('plblst_check_changes', '1', true);
		}
	}



	/**
	 * Check last activation
	 */
	private function check() {

		// Activation flag
		$changes = get_option('plblst_check_changes');
		if (empty($changes)) {
			return;
		}

		// No more checks in this thread
		update_option('plblst_check_changes', '', true);

		// Update the plugins list by path
		$disabler = $this->plugin->factory->disabler();
		$disabler->byPath();

		// Save future
		update_option('plblst_future_plugins', $disabler->future(), true);
		update_option('plblst_future_message', $disabler->futureMessage(), true);

		// Second round
		add_action('plugins_loaded', [$this, 'onPluginsLoaded']);
	}



	/**
	 * Check plugins after loading
	 */
	public function onPluginsLoaded() {

		// Now find in code
		$disabler = $this->plugin->factory->disabler();
		$disabler->byCode();

		// Save again future
		update_option('plblst_future_plugins', $disabler->future(), true);
		update_option('plblst_future_message', $disabler->futureMessage(), true);
	}



}