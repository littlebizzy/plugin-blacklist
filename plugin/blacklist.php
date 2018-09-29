<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Plugin;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Blacklist class
 *
 * @package Plugin Blacklist
 * @subpackage Plugin
 */
final class Blacklist extends Helpers\Singleton {


	private $content;



	public function read($cache = true) {

		// Check content cached
		if ($cache && isset($this->content)) {
			return $content;
		}

	}



}