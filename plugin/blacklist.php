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
		$index = -1;
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

			// Index
			$index++;

			// Check section
			if ('[' != substr($line, 0, 1)) {

				// Replace double quotes
				if (false !== strpos($line, '"')) {
					$line = str_replace('"', '\\'.'"', $line);
				}

				// Set new line
				$line = 'value'.$index.' = "'.$line.'"';
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
	 * Retrieve entire section as a string
	 */
	public function getSectionString($section) {

		// Retrieve data
		$blacklist = $this->read();
		if (empty($blacklist) || !is_array($blacklist)) {
			return '';
		}

		// Check section
		if (empty($blacklist[$section]) || !is_array($blacklist[$section])) {
			return '';
		}

		// Init
		$message = '';

		// Enum section lines
		foreach ($blacklist[$section] as $item => $value) {
			$message .= (empty($message)? '' : "\n").$value;
		}

		// Error
		return $message;
	}



}