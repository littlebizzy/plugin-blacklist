<?php
/*
Plugin Name: Plugin Blacklist
Plugin URI: https://www.littlebizzy.com/plugins/plugin-blacklist
Description: A carefully selected security suite for WordPress that combines only the most effective methods of guarding against hackers and other common attacks.
Version: 1.0.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Prefix: PLBLST
*/

// Plugin namespace
namespace LittleBizzy\PluginBlacklist;

// Block direct calls
if (!function_exists('add_action'))
	die;

// Plugin constants
const FILE = __FILE__;
const PREFIX = 'plblst';
const VERSION = '1.0.0';

// Loader
require_once dirname(FILE).'/helpers/loader.php';

// Run the main class
Helpers\Runner::start('Core\Core', 'instance');