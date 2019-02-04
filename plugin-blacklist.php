<?php
/*
Plugin Name: Plugin Blacklist
Plugin URI: https://www.littlebizzy.com/plugins/plugin-blacklist
Description: Allows web hosts, agencies, or other WordPress site managers to disallow a custom list of plugins from being activated for security or other reasons.
Version: 1.1.1
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Prefix: PLBLST
*/

// Plugin namespace
namespace LittleBizzy\PluginBlacklist;

// Block direct calls
if (!function_exists('add_action')) {
	die;
}

// Plugin constants
const FILE = __FILE__;
const PREFIX = 'plblst';
const VERSION = '1.1.1';

// Loader
require_once dirname(FILE).'/helpers/loader.php';

// Run the main class
Helpers\Runner::start('Core\Core', 'instance');