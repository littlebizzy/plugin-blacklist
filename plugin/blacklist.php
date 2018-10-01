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



	/**
	 * Cached config
	 */
	private $config;



	/**
	 * Read the blacklist file
	 */
	public function read($cache = true) {

		// Check content cached
		if ($cache) {
			if (isset($this->config)) {
				return $this->config;
			}
		}

		// Clear any cache
		$this->config = null;

		// Blacklist file path
		$path = WP_CONTENT_DIR.'/blacklist.txt';
		if (!@file_exists($path)) {
			return false;
		}

		// Retrieve content
		$lines = @file_get_contents($path);
		if (empty($lines)) {
			return false;
		}

		// Explode lines
		$lines = str_replace("\r\n", "\n", $lines);
		$lines = explode("\n", $lines);

		// Enum lines
		$lines2 = [];
		foreach ($lines as $line) {

			// Avoid empty lines
			$line = trim($line);
			if ('' === $line || ';' == substr($line, 0, 1)) {
				continue;
			}

			// Remove comments
			$comment = strpos($line, ';');
			if (false !== $comment) {
				$line = trim(substr($line, 0, $comment));
				if ('' === $line) {
					continue;
				}
			}

			// Check section
			if ('[' != substr($line, 0, 1)) {
				if (false === strpos($line, '=')) {
					$line .= ' = 1';
				}
			}

			// Sanitized line
			$lines2[] = $line;
		}

		// Check lines
		if (empty($lines2)) {
			return false;
		}

		// Cast to string
		$content = implode("\n", $lines2);

		// Parse ini file via PHP functions
		if (false === ($this->config = @parse_ini_string($content, true, INI_SCANNER_TYPED))) {
			return false;
		}

		// Done
		return $this->config;
	}



	/**
	 * Retrieve first section line
	 */
	public function getSectionFirst($section) {

		// Retrieve data
		$blacklist = $this->read();
		if (empty($blacklist) || !is_array($blacklist)) {
			return null;
		}

		// Check section
		if (empty($blacklist[$section]) || !is_array($blacklist[$section])) {
			return null;
		}

		// Enum section
		foreach ($blacklist[$section] as $item => $value) {
			return $item;
		}

		// Error
		return '';
	}



}