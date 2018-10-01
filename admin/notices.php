<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Admin;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Notices class
 *
 * @package Plugin Blacklist
 * @subpackage Admin
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

			<div class="notice notice-warning">

				<p><?php echo $this->message('deactivated'); ?></p>

				<ul>
					<li><?php echo implode('</li><li>', $this->deactivated); ?></li>
				</ul>

			</div><?php

		endif;

		// Future plugins
		if (!empty($this->future)) : ?>

			<div class="notice notice-info">

				<p><?php echo $this->message('future'); ?></p>

				<ul>
					<li><?php echo implode('</li><li>', $this->future); ?></li>
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
					$this->deactivated[] = $this->getPluginName($path);
				}
			}
		}

		// Future deactivated plugins
		$future = get_option($this->plugin->prefix.'_future_plugins');
		if (!empty($future) && is_array($future)) {
			foreach ($future as $path) {
				if (@file_exists($path)) {
					$this->future[] = $this->getPluginName($path);
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
			$message = get_option($this->plugin->prefix.'_future_message');
			if (empty($message)) {
				$message = 'The following plugins will be deactivated shortly:';
			}
		}

		// Escape message
		$message = implode("<br />", array_map('esc_html', explode("\n", $message)));

		// Done
		return $message;
	}



	/**
	 * Retrieve plugin name
	 */
	private function getPluginName($path) {

		// Check WP function
		if (!function_exists('get_plugin_data')) {
			return $this->getPluginRelativePath($path);
		}

		// Extract data
		$data = get_plugin_data($path, false);
		if (empty($data) || !is_array($data) || empty($data['Name'])) {
			return $this->getPluginRelativePath($path);
		}

		// Compose title and description
		$title = '<strong>'.esc_html($data['Name']).'</strong>';
		if (!empty($data['Description'])) {
			$title .= '<br />'.esc_html($data['Description']);
		}

		// Done
		return $title;
	}



	/**
	 * From full path to relative to the plugins directory path
	 */
	private function getPluginRelativePath($path) {

		// Expected path
		$prefix = WP_PLUGIN_DIR.'/';
		$prefixLength = strlen($prefix);

		// Check path start
		if (0 !== strpos($path, $prefix)) {
			return $path;
		}

		// Set relative
		$relativePath = substr($path, $prefixLength);
		if (empty($relativePath)) {
			return $path;
		}

		// Done
		return $relativePath;
	}



}