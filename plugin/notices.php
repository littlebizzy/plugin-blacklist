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
	 * Pseudo constructor
	 */
	protected function onConstruct() {
		add_action('admin_notices', [$this, 'notices']);
	}






	/**
	 * Show notices for deactivated plugins
	 */
	public function deactivated() {

		// Check deactivated
		if (empty($this->deactivated)) {
			return;
		}

		?><style>#message { display: none; }</style>

		<div class="notice notice-error is-dismissible">

			<p>Plugins not allowed</p>

			<?php print_r($disabler->deactivated()); ?>

		</div><?php
	}



}