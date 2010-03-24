<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

/**
 * Sets the default client character set.
 *
 * @compat
 *    Procedural style
 * @bugs
 *    PHP documentation says this function exists in PHP 5 >= 5.0.5,
 *    but it also depends on the versions of external libraries, e.g.,
 *    php_mysqli.dll and libmysql.dll.
 *
 * @param $link    mysqli MySQLi connection resource
 * @param $charset string Character set
 * @return bool           TRUE on success, FALSE on failure
 */
if (in_array('mysqli', @get_loaded_extensions()) && !function_exists('mysqli_set_charset')) {
	function mysqli_set_charset($link, $charset)
	{
		return mysqli_query($link, "SET NAMES '$charset'");
	}
}

/**
 * parse_ini_file() replacement.
 * Behaves like parse_ini_file($filename, $process_sections);
 *
 * @author Andrew Sohn <asohn (at) aircanopy (dot) net>
 * @author anthon (dot) pang (at) gmail (dot) com
 *
 * @param string $filename
 * @param bool $process_sections (defaults to false)
 * @return array
 */
if(function_exists('parse_ini_file')) {
	function _parse_ini_file($filename, $process_sections = false) {
		return parse_ini_file($filename, $process_sections);
	}
} else {
	function _parse_ini_file($filename, $process_sections = false)
	{
		if(function_exists('file_get_contents')) {
			$ini = file_get_contents($filename);
		} else if(function_exists('file') && version_compare(phpversion(), '6') >= 0) {
			$ini = implode(file($filename), FILE_TEXT);
		} else if(function_exists('fopen') && function_exists('fread')) {
			$handle = fopen($filename, 'r');
			$ini = fread($handle, filesize($filename));
			fclose($handle);
		} else {
			return false;
		}

		if(is_string($ini)) { $ini = explode("\n", str_replace("\r", "\n", $ini)); }
		if (count($ini) == 0) { return array(); }

		$sections = array();
		$values = array();
		$result = array();
		$globals = array();
		$i = 0;
		foreach ($ini as $line) {
			$line = trim($line);
			$line = str_replace("\t", " ", $line);

			// Comments
			if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {continue;}

			// Sections
			if ($line{0} == '[') {
				$tmp = explode(']', $line);
				$sections[] = trim(substr($tmp[0], 1));
				$i++;
				continue;
			}

			// Key-value pair
			list($key, $value) = explode('=', $line, 2);
			$key = trim($key);
			$value = trim($value);
			if (strstr($value, ";")) {
				$tmp = explode(';', $value);
				if (count($tmp) == 2) {
					if ((($value{0} != '"') && ($value{0} != "'")) ||
							preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
							preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
						$value = $tmp[0];
					}
				} else {
					if ($value{0} == '"') {
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					} elseif ($value{0} == "'") {
						$value = preg_replace("/^'(.*)'.*/", '$1', $value);
					} else {
						$value = $tmp[0];
					}
				}
			}

			$value = trim($value);
			$value = trim($value, "'\"");

			if ($i == 0) {
				if (substr($key, -2) == '[]') {
					$globals[substr($key, 0, -2)][] = $value;
				} else {
					$globals[$key] = $value;
				}
			} else {
				if (substr($key, -2) == '[]') {
					$values[$i-1][substr($key, 0, -2)][] = $value;
				} else {
					$values[$i-1][$key] = $value;
				}
			}
		}

		for($j = 0; $j < $i; $j++) {
			if ($process_sections === true) {
				$result[$sections[$j]] = $values[$j];
			} else {
				$result[] = $values[$j];
			}
		}

		return $result + $globals;
	}
}
