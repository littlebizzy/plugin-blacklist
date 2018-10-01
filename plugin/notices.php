<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Plugin;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Notices class
 *
 * @package Plugin Blacklist
 * @subpackage Plugin
 */
final class Notices extends Helpers\Singleton {



	/**
	 * Detected plugins
	 */
	private $future = [];
	private $deactivated = [];



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {
		add_action('admin_notices', [$this, 'notices']);
	}



	/**
	 * Show notices for deactivated plugins
	 */
	public function notices() {

		// Collect data
		$this->init();

		// Deactivated plugins
		if (!empty($this->deactivated)) : ?>

			<style>#message { display: none; }</style>

			<div class="notice notice-error is-dismissible">

				<p><?php echo $this->message('deactivated'); ?></p>

				<ul>
					<li><?php echo implode('</li><li>', array_map('esc_html', $this->deactivated)); ?></li>
				</ul>

			</div><?php

		endif;

		// Future plugins
		if (!empty($this->future)) : ?>

			<div class="notice notice-error is-dismissible">

				<p><?php echo $this->message('future'); ?></p>

				<ul>
					<li><?php echo implode('</li><li>', array_map('esc_html', $this->future)); ?></li>
				</ul>

			</div><?php

		endif;
	}



	/**
	 * Initialize detected plugins and prepare to show it
	 */
	private function init() {

		// Detected deactivated plugins
		$deactivated = $this->plugin->factory->disabler()->deactivated();
		if (!empty($deactivated)) {
			foreach ($deactivated as $path) {
				if (@file_exists($path)) {
					$this->deactivated[] = $path;
				}
			}
		}

		// Future deactivated plugins
		$future = get_option('plblst_future_plugins');
		if (!empty($future) && is_array($future)) {
			foreach ($future as $path) {
				if (@file_exists($path)) {
					$this->future[] = $path;
				}
			}
		}
	}



	/**
	 * Retrieve the config message
	 */
	private function message($type) {

		// Init
		$message = '';

		// Deactivation
		if ('deactivated' == $type) {
			$message = $this->plugin->factory->blacklist()->getSectionString('message');
			if (empty($message)) {
				$message = 'The following plugins are not allowed and have been disabled:';
			}
		}

		// Future deactivation
		if ('future' == $type) {
			$message = get_option('plblst_future_message');
			if (empty($message)) {
				$message = 'The following plugins will be deactivated shortly:';
			}
		}

		// Escape message
		$message = implode("<br />", array_map('esc_html', explode("\n", $message)));

		// Done
		return $message;
	}



}