<?php

// Subpackage namespace
namespace LittleBizzy\PluginBlacklist\Core;

// Aliased namespaces
use \LittleBizzy\PluginBlacklist\Plugin;
use \LittleBizzy\PluginBlacklist\Helpers;

/**
 * Object Factory class
 *
 * @package Plugin Blacklist
 * @subpackage Core
 */
class Factory extends Helpers\Factory {



	/**
	 * Cron object
	 */
	protected function createCron() {
		return Cron::instance($this->plugin);
	}



	/**
	 * Plugin activation object
	 */
	protected function createActivation() {
		return Plugin\Activation::instance($this->plugin);
	}



	/**
	 * Plugin blacklist object
	 */
	protected function createBlacklist() {
		return Plugin\Blacklist::instance($this->plugin);
	}



	/**
	 * Plugin update object
	 */
	protected function createDisabler() {
		return Plugin\Disabler::instance($this->plugin);
	}



}