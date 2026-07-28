<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Global helper functions, loaded before the Composer autoloader.
 *
 * Based on the public domain upgrade.php library, which emulated functions from newer PHP versions
 * on older interpreters. Those polyfills have all been removed; only the helpers below remain.
 */

/**
 * Arbitrary limits for safe_unserialize()
 *
 * @license Public Domain
 *
 * @author anthon (dot) pang (at) gmail (dot) com
 */
define('MAX_SERIALIZED_INPUT_LENGTH', 4096);
define('MAX_SERIALIZED_ARRAY_LENGTH', 256);
define('MAX_SERIALIZED_ARRAY_DEPTH', 3);

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
 * Wrapper for _safe_unserialize()
 *
 * @param string $str
 * @return mixed
 */
function safe_unserialize( $str )
{
	return _safe_unserialize($str);
}

/**
 * readfile() replacement.
 * Behaves similar to readfile($filename);
 *
 * @author anthon (dot) pang (at) gmail (dot) com
 *
 * @param string $filename
 * @param int $byteStart offset of the first byte to output
 * @param int $byteEnd offset to stop outputting at (exclusive)
 * @param bool $useIncludePath
 * @param resource $context
 * @return int|false the number of bytes read from the file, or false if an error occurs
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
	$handle = @fopen($filename, "rb");
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
