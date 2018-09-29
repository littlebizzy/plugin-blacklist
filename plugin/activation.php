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
	 * Plugins deactivated
	 */
	private $deactivated;



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {
		$this->hooks()
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
	private function checks() {

		// Activation flag
		$activation = get_option('plblst_check_activation');
		if (empty($activation)) {
			return;
		}

		// No more checks in this thread
		update_option('plblst_check_activation', '', true);

		// Update the plugins list
		$this->deactivated = $this->plugin->factory->disabler()->update();

		// Add the notices
		if (!empty($this->deactivated) && is_array($this->deactivated)) {
			add_action('admin_notices', [$this, 'notices']);
		}
	}



	public function notices() {

		?><div class="notice notice-error is-dismissible">

			<p>Plugin not allowed</p>

			<?php print_r($this->deactivated); ?>

		</div><?php

	}


}