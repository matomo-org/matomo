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

/**
 * @since PHP 5
 */
if(!defined('E_STRICT')) {            define('E_STRICT', 2048); }

/**
 * @since PHP 5.2.0
 */
if(!defined('E_RECOVERABLE_ERROR')) { define('E_RECOVERABLE_ERROR', 4096); }

/**
 * @since PHP 5.3.0
 */
if(!defined('E_DEPRECATED')) {        define('E_DEPRECATED', 8192); }
if(!defined('E_USER_DEPRECATED')) {   define('E_USER_DEPRECATED', 16384); }

/**
 *                                   ------------------------------ 5.2 ---
 * @group 5_2
 * @since 5.2
 *
 * Additions of PHP 5.2.0
 * - some listed here might have appeared earlier or in release candidates
 *
 * @emulated
 *    json_encode
 *    json_decode
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
 * Converts PHP variable or array into a "JSON" (JavaScript value expression
 * or "object notation") string.
 *
 * @compat
 *    Output seems identical to PECL versions. "Only" 20x slower than PECL version.
 * @bugs
 *    Doesn't take care with unicode too much - leaves UTF-8 sequences alone.
 *
 * @param  $var mixed  PHP variable/array/object
 * @return string      transformed into JSON equivalent
 */
if (!function_exists("json_encode")) {
   function json_encode($var, /*emu_args*/$obj=FALSE) {
   
      #-- prepare JSON string
      $json = "";
      
      #-- add array entries
      if (is_array($var) || ($obj=is_object($var))) {

         #-- check if array is associative
         if (!$obj) foreach ((array)$var as $i=>$v) {
            if (!is_int($i)) {
               $obj = 1;
               break;
            }
         }

         #-- concat invidual entries
         foreach ((array)$var as $i=>$v) {
            $json .= ($json ? "," : "")    // comma separators
                   . ($obj ? ("\"$i\":") : "")   // assoc prefix
                   . (json_encode($v));    // value
         }

         #-- enclose into braces or brackets
         $json = $obj ? "{".$json."}" : "[".$json."]";
      }

      #-- strings need some care
      elseif (is_string($var)) {
         if (!utf8_decode($var)) {
            $var = utf8_encode($var);
         }
         $var = str_replace(array("\\", "\"", "/", "\b", "\f", "\n", "\r", "\t"), array("\\\\", '\"', "\\/", "\\b", "\\f", "\\n", "\\r", "\\t"), $var);
         $json = '"' . $var . '"';
         //@COMPAT: for fully-fully-compliance   $var = preg_replace("/[\000-\037]/", "", $var);
      }

      #-- basic types
      elseif (is_bool($var)) {
         $json = $var ? "true" : "false";
      }
      elseif ($var === NULL) {
         $json = "null";
      }
      elseif (is_int($var) || is_float($var)) {
         $json = "$var";
      }

      #-- something went wrong
      else {
         trigger_error("json_encode: don't know what a '" .gettype($var). "' is.", E_USER_ERROR);
      }
      
      #-- done
      return($json);
   }
}

/**
 * Parses a JSON (JavaScript value expression) string into a PHP variable
 * (array or object).
 *
 * @compat
 *    Behaves similar to PECL version, but is less quiet on errors.
 *    Now even decodes unicode \uXXXX string escapes into UTF-8.
 *    "Only" 27 times slower than native function.
 * @bugs
 *    Might parse some misformed representations, when other implementations
 *    would scream error or explode.
 * @code
 *    This is state machine spaghetti code. Needs the extranous parameters to
 *    process subarrays, etc. When it recursively calls itself, $n is the
 *    current position, and $waitfor a string with possible end-tokens.
 *
 * @param   $json string   JSON encoded values
 * @param   $assoc bool    (optional) if outer shell should be decoded as object always
 * @return  mixed          parsed into PHP variable/array/object
 */
if (!function_exists("json_decode")) {
   function json_decode($json, $assoc=FALSE, /*emu_args*/$n=0,$state=0,$waitfor=0) {

      #-- result var
      $val = NULL;
      static $lang_eq = array("true" => TRUE, "false" => FALSE, "null" => NULL);
      static $str_eq = array("n"=>"\012", "r"=>"\015", "\\"=>"\\", '"'=>'"', "f"=>"\f", "b"=>"\b", "t"=>"\t", "/"=>"/");

      #-- flat char-wise parsing
      for (/*n*/; $n<strlen($json); /*n*/) {
         $c = $json[$n];

         #-= in-string
         if ($state==='"') {

            if ($c == '\\') {
               $c = $json[++$n];
               // simple C escapes
               if (isset($str_eq[$c])) {
                  $val .= $str_eq[$c];
               }

               // here we transform \uXXXX Unicode (always 4 nibbles) references to UTF-8
               elseif ($c == "u") {
                  // read just 16bit (therefore value can't be negative)
                  $hex = hexdec( substr($json, $n+1, 4) );
                  $n += 4;
                  // Unicode ranges
                  if ($hex < 0x80) {    // plain ASCII character
                     $val .= chr($hex);
                  }
                  elseif ($hex < 0x800) {   // 110xxxxx 10xxxxxx 
                     $val .= chr(0xC0 + $hex>>6) . chr(0x80 + $hex&63);
                  }
                  elseif ($hex <= 0xFFFF) { // 1110xxxx 10xxxxxx 10xxxxxx 
                     $val .= chr(0xE0 + $hex>>12) . chr(0x80 + ($hex>>6)&63) . chr(0x80 + $hex&63);
                  }
                  // other ranges, like 0x1FFFFF=0xF0, 0x3FFFFFF=0xF8 and 0x7FFFFFFF=0xFC do not apply
               }

               // no escape, just a redundant backslash
               //@COMPAT: we could throw an exception here
               else {
                  $val .= "\\" . $c;
               }
            }

            // end of string
            elseif ($c == '"') {
               $state = 0;
            }

            // yeeha! a single character found!!!!1!
            else/*if (ord($c) >= 32)*/ { //@COMPAT: specialchars check - but native json doesn't do it?
               $val .= $c;
            }
         }

         #-> end of sub-call (array/object)
         elseif ($waitfor && (strpos($waitfor, $c) !== false)) {
            return array($val, $n);  // return current value and state
         }
         
         #-= in-array
         elseif ($state===']') {
            list($v, $n) = json_decode($json, 0, $n, 0, ",]");
            $val[] = $v;
            if ($json[$n] == "]") { return array($val, $n); }
         }

         #-= in-object
         elseif ($state==='}') {
            list($i, $n) = json_decode($json, 0, $n, 0, ":");   // this allowed non-string indicies
            list($v, $n) = json_decode($json, 0, $n+1, 0, ",}");
            $val[$i] = $v;
            if ($json[$n] == "}") { return array($val, $n); }
         }

         #-- looking for next item (0)
         else {
         
            #-> whitespace
            if (preg_match("/\s/", $c)) {
               // skip
            }

            #-> string begin
            elseif ($c == '"') {
               $state = '"';
            }

            #-> object
            elseif ($c == "{") {
               list($val, $n) = json_decode($json, $assoc, $n+1, '}', "}");
               if ($val && $n && !$assoc) {
                  $obj = new stdClass();
                  foreach ($val as $i=>$v) {
                     $obj->{$i} = $v;
                  }
                  $val = $obj;
                  unset($obj);
               }
            }
            #-> array
            elseif ($c == "[") {
               list($val, $n) = json_decode($json, $assoc, $n+1, ']', "]");
            }

            #-> comment
            elseif (($c == "/") && ($json[$n+1]=="*")) {
               // just find end, skip over
               ($n = strpos($json, "*/", $n+1)) or ($n = strlen($json));
            }

            #-> numbers
            elseif (preg_match("#^(-?\d+(?:\.\d+)?)(?:[eE]([-+]?\d+))?#", substr($json, $n), $uu)) {
               $val = $uu[1];
               $n += strlen($uu[0]) - 1;
               if (strpos($val, ".")) {  // float
                  $val = (float)$val;
               }
               elseif ($val[0] == "0") {  // oct
                  $val = octdec($val);
               }
               else {
                  $val = (int)$val;
               }
               // exponent?
               if (isset($uu[2])) {
                  $val *= pow(10, (int)$uu[2]);
               }
            }

            #-> boolean or null
            elseif (preg_match("#^(true|false|null)\b#", substr($json, $n), $uu)) {
               $val = $lang_eq[$uu[1]];
               $n += strlen($uu[1]) - 1;
            }

            #-- parsing error
            else {
               // PHPs native json_decode() breaks here usually and QUIETLY
              trigger_error("json_decode: error parsing '$c' at position $n", E_USER_WARNING);
               return $waitfor ? array(NULL, 1<<30) : NULL;
            }

         }//state
         
         #-- next char
         if ($n === NULL) { return NULL; }
         $n++;
      }//for

      #-- final result
      return ($val);
   }
}

/**
 * Constants for future 64-bit integer support.
 *
 */
if (!defined("PHP_INT_SIZE")) { define("PHP_INT_SIZE", 4); }
if (!defined("PHP_INT_MAX")) { define("PHP_INT_MAX", 2147483647); }

/**
 * @flag bugfix
 * @see #33895
 *
 * Missing constants in 5.1, originally appeared in 4.0.
 */
if (!defined("M_SQRTPI")) { define("M_SQRTPI", 1.7724538509055); }
if (!defined("M_LNPI")) { define("M_LNPI", 1.1447298858494); }
if (!defined("M_EULER")) { define("M_EULER", 0.57721566490153); }
if (!defined("M_SQRT3")) { define("M_SQRT3", 1.7320508075689); }

/**
 * removes entities &lt; &gt; &amp; and eventually &quot; from HTML string
 *
 */
if (!function_exists("htmlspecialchars_decode")) {
   if (!defined("ENT_COMPAT")) { define("ENT_COMPAT", 2); }
   if (!defined("ENT_QUOTES")) { define("ENT_QUOTES", 3); }
   if (!defined("ENT_NOQUOTES")) { define("ENT_NOQUOTES", 0); }
   function htmlspecialchars_decode($string, $quotes=2) {
      $d = $quotes & ENT_COMPAT;
      $s = $quotes & ENT_QUOTES;
      return str_replace(
         array("&lt;", "&gt;", ($s ? "&quot;" : "&.-;"), ($d ? "&#039;" : "&.-;"), "&amp;"),
         array("<",    ">",    "'",                      "\"",                     "&"),
         $string
      );
   }
}

/*
   These functions emulate the "character type" extension, which is
   present in PHP first since version 4.3 per default. In this variant
   only ASCII and Latin-1 characters are being handled. The first part
   is eventually faster.
*/


#-- regex variants
if (!function_exists("ctype_alnum")) {
   function ctype_alnum($text) {
      return preg_match("/^[A-Za-z\d\300-\377]+$/", $text);
   }
   function ctype_alpha($text) {
      return preg_match("/^[a-zA-Z\300-\377]+$/", $text);
   }
   function ctype_digit($text) {
      return preg_match("/^\d+$/", $text);
   }
   function ctype_xdigit($text) {
      return preg_match("/^[a-fA-F0-9]+$/", $text);
   }
   function ctype_cntrl($text) {
      return preg_match("/^[\000-\037]+$/", $text);
   }
   function ctype_space($text) {
      return preg_match("/^\s+$/", $text);
   }
   function ctype_upper($text) {
      return preg_match("/^[A-Z\300-\337]+$/", $text);
   }
   function ctype_lower($text) {
      return preg_match("/^[a-z\340-\377]+$/", $text);
   }
   function ctype_graph($text) {
      return preg_match("/^[\041-\176\241-\377]+$/", $text);
   }
   function ctype_punct($text) {
      return preg_match("/^[^0-9A-Za-z\000-\040\177-\240\300-\377]+$/", $text);
   }
   function ctype_print($text) {
      return ctype_punct($text) && ctype_graph($text);
   }
}

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
		return parse_ini_file($filename, $process_sections);
	}
} else {
	// we can't redefine parse_ini_file() if it has been disabled
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

/**
 * fnmatch() replacement
 *
 * @since fnmatch() added to PHP 4.3.0; PHP 5.3.0 on Windows
 * @author jk at ricochetsolutions dot com
 * @author anthon (dot) pang (at) gmail (dot) com
 *
 * @param string $pattern shell wildcard pattern
 * @param string $string tested string
 * @param int $flags FNM_CASEFOLD (other flags not supported)
 * @return bool True if there is a match, false otherwise
 */
if(!defined('FNM_CASEFOLD')) { define('FNM_CASEFOLD', 16); }
if(function_exists('fnmatch')) {
	// provide a wrapper
	function _fnmatch($pattern, $string, $flags = 0) {
		return fnmatch($pattern, $string, $flags);
	}
} else {
    function _fnmatch($pattern, $string, $flags = 0) {
		$regex = '#^' . strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.')) . '$#' . ($flags & FNM_CASEFOLD ? 'i' : '');
		return preg_match($regex, $string);
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
						&& _fnmatch($filePattern, $file)
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
