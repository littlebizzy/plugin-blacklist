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
		add_action('activate_plugin', [$this, 'onActivation'], 10 ,2);
		// deactivation also to remove the future  ...
	}



	/**
	 * Handle activation event
	 */
	public function onActivation($plugin, $network_wide) {
		if (!$network_wide) {
			update_option('plblst_check_activation', '1', true);
		}
	}



	/**
	 * Checks last activation
	 */
	private function check() {

		// Activation flag
		$activation = get_option('plblst_check_activation');
		if (empty($activation)) {
			return;
		}

		// No more checks in this thread
		update_option('plblst_check_activation', '', true);

		// Update the plugins list
		$disabler = $this->plugin->factory->disabler();
		$disabler->update();

		// Add the notices
		if (!empty($disabler->deactivated())) {
			add_action('admin_notices', [$this, 'notices']);
		}
	}



	/**
	 * Show notices for deactivated plugins
	 */
	public function notices() {

		// Check deactivated
		$disabler = $this->plugin->factory->disabler();
		if (empty($disabler->deactivated())) {
			return;
		}

		?><div class="notice notice-error is-dismissible">

			<p>Plugin not allowed</p>

			<?php print_r($disabler->deactivated()); ?>

		</div>

		<style>#message { display: none; }</style><?php

	}


}