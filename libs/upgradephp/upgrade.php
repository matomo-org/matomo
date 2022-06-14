<?php
/**
 * api:		php
 * title:	upgrade.php
 * description:	Emulates functions from new PHP versions on older interpreters.
 * version:	17
 * license:	Public Domain
 * url:		http://freshmeat.net/projects/upgradephp
 * type:	functions
 * category:	library
 * priority:	auto
 * load_if:     (PHP_VERSION<5.2)
 * sort:	-255
 * provides:	upgrade-php, api:php5, json
 *
 *
 * By loading this library you get PHP version independence. It provides
 * downwards compatibility to older PHP interpreters by emulating missing
 * functions or constants using IDENTICAL NAMES. So this doesn't slow down
 * script execution on setups where the native functions already exist. It
 * is meant as quick drop-in solution. It spares you from rewriting code or
 * using cumbersome workarounds instead of the more powerful v5 functions.
 *
 * It cannot mirror PHP5s extended OO-semantics and functionality into PHP4
 * however. A few features are added here that weren't part of PHP yet. And
 * some other function collections are separated out into the ext/ directory.
 * It doesn't produce many custom error messages (YAGNI), and instead leaves
 * reporting to invoked functions or for native PHP execution.
 *
 * And further this is PUBLIC DOMAIN (no copyright, no license, no warranty)
 * so therefore compatible to ALL open source licenses. You could rip this
 * paragraph out to republish this instead only under more restrictive terms
 * or your favorite license (GNU LGPL/GPL, BSDL, MPL/CDDL, Artistic/PHPL, ..)
 *
 * Any contribution is appreciated. <milky*users#sf#net>
 *
 */
use Piwik\SettingsServer;

/**
 *                                   ------------------------------ 5.2 ---
 * @group 5_2
 * @since 5.2
 *
 * Additions of PHP 5.2.0
 * - some listed here might have appeared earlier or in release candidates
 *
 * @emulated
 *    error_get_last
 *    preg_last_error
 *    lchown
 *    lchgrp
 *    E_RECOVERABLE_ERROR
 *    M_SQRTPI
 *    M_LNPI
 *    M_EULER
 *    M_SQRT3
 *
 * @missing
 *    sys_getloadavg
 *    inet_ntop
 *    inet_pton
 *    array_fill_keys
 *    array_intersect_key
 *    array_intersect_ukey
 *    array_diff_key
 *    array_diff_ukey
 *    array_product
 *    pdo_drivers
 *    ftp_ssl_connect
 *    XmlReader
 *    XmlWriter
 *    PDO*
 *
 * @unimplementable
 *    stream_*
 *
 */

/**
 * Constants for future 64-bit integer support.
 *
 */
if (!defined("PHP_INT_SIZE")) { define("PHP_INT_SIZE", 4); }
if (!defined("PHP_INT_MAX")) { define("PHP_INT_MAX", 2147483647); }

/*
   These functions emulate the "character type" extension, which is
   present in PHP first since version 4.3 per default. In this variant
   only ASCII and Latin-1 characters are being handled. The first part
   is eventually faster.
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
	// provide a wrapper
	function _parse_ini_file($filename, $process_sections = false) {
		if(!file_exists($filename)) {
            return false;
        }

        return parse_ini_file($filename, $process_sections);
	}
} else {
	// we can't redefine parse_ini_file() if it has been disabled
	function _parse_ini_file($filename, $process_sections = false)
	{
		if(!file_exists($filename)) {
			return false;
		}

		if(function_exists('file_get_contents')) {
			$ini = file_get_contents($filename);
		} else if(function_exists('file')) {
			if($ini = file($filename)) {
				$ini = implode("\n", $ini);
			}
		} else if(function_exists('fopen') && function_exists('fread')) {
			$handle = fopen($filename, 'r');
			if(!$handle) {
				return false;
			}
			$ini = fread($handle, filesize($filename));
			fclose($handle);
		} else {
			return false;
		}

		if($ini === false) {
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
			if ($line[0] == '[') {
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
					if ((($value[0] != '"') && ($value[0] != "'")) ||
							preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
							preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
						$value = $tmp[0];
					}
				} else {
					if ($value[0] == '"') {
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					} elseif ($value[0] == "'") {
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

		for ($j = 0; $j < $i; $j++) {
			if (isset($values[$j])) {
				if ($process_sections === true) {
					$result[$sections[$j]] = $values[$j];
				} else {
					$result[] = $values[$j];
				}
			} else {
				if ($process_sections === true) {
					$result[$sections[$j]] = array();
				}
			}
		}

		return $result + $globals;
	}
}

/**
 * glob() replacement.
 * Behaves like glob($pattern, $flags)
 *
 * @author BigueNique AT yahoo DOT ca
 * @author anthon (dot) pang (at) gmail (dot) com
 *
 * @param string $pattern
 * @param int $flags GLOBL_ONLYDIR, GLOB_MARK, GLOB_NOSORT (other flags not supported; defaults to 0)
 * @return array
 */
if(function_exists('glob')) {
	// provide a wrapper
	function _glob($pattern, $flags = 0) {
		return glob($pattern, $flags);
	}
} else if(function_exists('opendir') && function_exists('readdir')) {
	// we can't redefine glob() if it has been disabled
	function _glob($pattern, $flags = 0) {
		$path = dirname($pattern);
		$filePattern = basename($pattern);
		if(is_dir($path) && ($handle = opendir($path)) !== false) {
			$matches = array();
			while(($file = readdir($handle)) !== false) {
				if(($file[0] != '.')
						&& fnmatch($filePattern, $file)
						&& (!($flags & GLOB_ONLYDIR) || is_dir("$path/$file"))) {
					$matches[] = "$path/$file" . ($flags & GLOB_MARK ? '/' : '');
				}
			}
			closedir($handle);
			if(!($flags & GLOB_NOSORT)) {
				sort($matches);
			}
			return $matches;
		}
		return false;
	}
} else {
	function _glob($pattern, $flags = 0) {
		return false;
	}
}

/**
 * Reads entire file into a string.
 * This function is not 100% compatible with the native function.
 *
 * @see http://php.net/file_get_contents
 * @since PHP 4.3.0
 *
 * @param string $filename Name of the file to read.
 * @return string The read data or false on failure.
 */
if (!function_exists('file_get_contents'))
{
	function file_get_contents($filename)
	{
		$fhandle = fopen($filename, "r");
		$fcontents = fread($fhandle, filesize($filename));
		fclose($fhandle);
		return $fcontents;
	}
}

/**
 * Safe serialize() and unserialize() replacements
 *
 * @license Public Domain
 *
 * @author anthon (dot) pang (at) gmail (dot) com
 */

/*
 * Arbitrary limits for safe_unserialize()
 */
define('MAX_SERIALIZED_INPUT_LENGTH', 4096);
define('MAX_SERIALIZED_ARRAY_LENGTH', 256);
define('MAX_SERIALIZED_ARRAY_DEPTH', 3);


/**
 * Safe serialize() replacement
 * - output a strict subset of PHP's native serialized representation
 * - does not serialize objects
 *
 * @param mixed $value
 * @return string
 * @throw Exception if $value is malformed or contains unsupported types (e.g., resources, objects)
 */
function _safe_serialize( $value )
{
	if(is_null($value))
	{
		return 'N;';
	}
	if(is_bool($value))
	{
		return 'b:'.(int)$value.';';
	}
	if(is_int($value))
	{
		return 'i:'.$value.';';
	}
	if(is_float($value))
	{
		return 'd:'.str_replace(',', '.', $value).';';
	}
	if(is_string($value))
	{
		return 's:'.strlen($value).':"'.$value.'";';
	}
	if(is_array($value))
	{
		$out = '';
		foreach($value as $k => $v)
		{
			$out .= _safe_serialize($k) . _safe_serialize($v);
		}

		return 'a:'.count($value).':{'.$out.'}';
	}

	// safe_serialize cannot serialize resources or objects
	return false;
}

/**
 * Wrapper for _safe_serialize() that handles exceptions and multibyte encoding issue
 *
 * @param mixed $value
 * @return string
 */
function safe_serialize( $value )
{
	// ensure we use the byte count for strings even when strlen() is overloaded by mb_strlen()
	if (function_exists('mb_internal_encoding') &&
		(((int) ini_get('mbstring.func_overload')) & 2))
	{
		$mbIntEnc = mb_internal_encoding();
		mb_internal_encoding('ASCII');
	}

	$out = _safe_serialize($value);

	if (isset($mbIntEnc))
	{
		mb_internal_encoding($mbIntEnc);
	}
	return $out;
}

/**
 * Safe unserialize() replacement
 * - accepts a strict subset of PHP's native serialized representation
 * - does not unserialize objects
 *
 * @param string $str
 * @return mixed
 * @throw Exception if $str is malformed or contains unsupported types (e.g., resources, objects)
 */
function _safe_unserialize($str)
{
	if(strlen($str) > MAX_SERIALIZED_INPUT_LENGTH)
	{
		// input exceeds MAX_SERIALIZED_INPUT_LENGTH
		return false;
	}

	if(empty($str) || !is_string($str))
	{
		return false;
	}

	$stack = array();
	$expected = array();

	/*
	 * states:
	 *   0 - initial state, expecting a single value or array
	 *   1 - terminal state
	 *   2 - in array, expecting end of array or a key
	 *   3 - in array, expecting value or another array
	 */
	$state = 0;
	while($state != 1)
	{
		$type = isset($str[0]) ? $str[0] : '';

		if($type == '}')
		{
			$str = substr($str, 1);
		}
		else if($type == 'N' && $str[1] == ';')
		{
			$value = null;
			$str = substr($str, 2);
		}
		else if($type == 'b' && preg_match('/^b:([01]);/', $str, $matches))
		{
			$value = $matches[1] == '1' ? true : false;
			$str = substr($str, 4);
		}
		else if($type == 'i' && preg_match('/^i:(-?[0-9]+);(.*)/s', $str, $matches))
		{
			$value = (int)$matches[1];
			$str = $matches[2];
		}
		else if($type == 'd' && preg_match('/^d:(-?[0-9]+\.?[0-9]*(E[+-][0-9]+)?);(.*)/s', $str, $matches))
		{
			$value = (float)$matches[1];
			$str = $matches[3];
		}
		else if($type == 's' && preg_match('/^s:([0-9]+):"(.*)/s', $str, $matches) && substr($matches[2], (int)$matches[1], 2) == '";')
		{
			$value = substr($matches[2], 0, (int)$matches[1]);
			$str = substr($matches[2], (int)$matches[1] + 2);
		}
		else if($type == 'a' && preg_match('/^a:([0-9]+):{(.*)/s', $str, $matches) && $matches[1] < MAX_SERIALIZED_ARRAY_LENGTH)
		{
			$expectedLength = (int)$matches[1];
			$str = $matches[2];
		}
		else
		{
			// object or unknown/malformed type
			return false;
		}

		switch($state)
		{
			case 3: // in array, expecting value or another array
				if($type == 'a')
				{
					if(count($stack) >= MAX_SERIALIZED_ARRAY_DEPTH)
					{
						// array nesting exceeds MAX_SERIALIZED_ARRAY_DEPTH
						return false;
					}

					$stack[] = &$list;
					$list[$key] = array();
					$list = &$list[$key];
					$expected[] = $expectedLength;
					$state = 2;
					break;
				}
				if($type != '}')
				{
					$list[$key] = $value;
					$state = 2;
					break;
				}

				// missing array value
				return false;

			case 2: // in array, expecting end of array or a key
				if($type == '}')
				{
					if(count($list) < end($expected))
					{
						// array size less than expected
						return false;
					}

					unset($list);
					$list = &$stack[count($stack)-1];
					array_pop($stack);

					// go to terminal state if we're at the end of the root array
					array_pop($expected);
					if(count($expected) == 0) {
						$state = 1;
					}
					break;
				}
				if($type == 'i' || $type == 's')
				{
					if(count($list) >= MAX_SERIALIZED_ARRAY_LENGTH)
					{
						// array size exceeds MAX_SERIALIZED_ARRAY_LENGTH
						return false;
					}
					if(count($list) >= end($expected))
					{
						// array size exceeds expected length
						return false;
					}

					$key = $value;
					$state = 3;
					break;
				}

				// illegal array index type
				return false;

			case 0: // expecting array or value
				if($type == 'a')
				{
					if(count($stack) >= MAX_SERIALIZED_ARRAY_DEPTH)
					{
						// array nesting exceeds MAX_SERIALIZED_ARRAY_DEPTH
						return false;
					}

					$data = array();
					$list = &$data;
					$expected[] = $expectedLength;
					$state = 2;
					break;
				}
				if($type != '}')
				{
					$data = $value;
					$state = 1;
					break;
				}

				// not in array
				return false;
		}
	}

	if(!empty($str))
	{
		// trailing data in input
		return false;
	}
	return $data;
}

/**
 * Wrapper for _safe_unserialize() that handles exceptions and multibyte encoding issue
 *
 * @param string $str
 * @return mixed
 */
function safe_unserialize( $str )
{
	// ensure we use the byte count for strings even when strlen() is overloaded by mb_strlen()
	if (function_exists('mb_internal_encoding') &&
		(((int) ini_get('mbstring.func_overload')) & 2))
	{
		$mbIntEnc = mb_internal_encoding();
		mb_internal_encoding('ASCII');
	}

	$out = _safe_unserialize($str);

	if (isset($mbIntEnc))
	{
		mb_internal_encoding($mbIntEnc);
	}
	return $out;
}

/**
 * readfile() replacement.
 * Behaves similar to readfile($filename);
 *
 * @author anthon (dot) pang (at) gmail (dot) com
 *
 * @param string $filename
 * @param bool $useIncludePath
 * @param resource $context
 * @return int the number of bytes read from the file, or false if an error occurs
 */
function _readfile($filename, $byteStart, $byteEnd, $useIncludePath = false, $context = null)
{
	$count = @filesize($filename);

	// built-in function has a 2 MB limit when using mmap
	if (function_exists('readfile')
        && $count <= (2 * 1024 * 1024)
        && $byteStart == 0
        && $byteEnd == $count
    ) {
		return @readfile($filename, $useIncludePath, $context);
	}

	// when in doubt (or when readfile() function is disabled)
	$handle = @fopen($filename, SettingsServer::isWindows() ? "rb" : "r");
	if ($handle) {
        fseek($handle, $byteStart);

        for ($pos = $byteStart; $pos < $byteEnd && !feof($handle); $pos = ftell($handle)) {
			echo fread($handle, min(8192, $byteEnd - $pos));

			@ob_flush();
			@flush();
		}

		fclose($handle);
		return $byteEnd - $byteStart;
	}
	return false;
}

/**
 * utf8_encode replacement
 *
 * @param string $data
 * @return string
 */
if (!function_exists('utf8_encode')) {
	function utf8_encode($data) {
		if (function_exists('iconv')) {
			return @iconv('ISO-8859-1', 'UTF-8', $data);
		}
		return $data;
	}
}

/**
 * utf8_decode replacement
 *
 * @param string $data
 * @return string
 */
if (!function_exists('utf8_decode')) {
	function utf8_decode($data) {
		if (function_exists('iconv')) {
			return @iconv('UTF-8', 'ISO-8859-1', $data);
		}
		return $data;
	}
}

/**
 * On ubuntu in some cases, there is a bug that gzopen does not exist and one must use gzopen64 instead
 */
if (!function_exists('gzopen')
    && function_exists('gzopen64')) {
    function gzopen($filename , $mode = 'r', $use_include_path = 0 )
    {
        return gzopen64($filename , $mode, $use_include_path);
    }
}

/**
 * Need to catch that PHP7 error object on php5
 */
if( !class_exists('\Error')) {
	class Error {

	}
}

if(!function_exists('fnmatch')) {

	function fnmatch($pattern, $string) {
		return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
	} // end

} // end if
