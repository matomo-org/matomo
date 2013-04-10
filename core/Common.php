<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Static class providing functions used by both the CORE of Piwik and the visitor Tracking engine.
 *
 * This is the only external class loaded by the /piwik.php file.
 * This class should contain only the functions that are used in
 * both the CORE and the piwik.php statistics logging engine.
 *
 * @package Piwik
 */
class Piwik_Common
{
    /**
     * Const used to map the referer type to an integer in the log_visit table
     */
    const REFERER_TYPE_DIRECT_ENTRY = 1;
    const REFERER_TYPE_SEARCH_ENGINE = 2;
    const REFERER_TYPE_WEBSITE = 3;
    const REFERER_TYPE_CAMPAIGN = 6;

    /**
     * Flag used with htmlspecialchar
     * See php.net/htmlspecialchars
     */
    const HTML_ENCODING_QUOTE_STYLE = ENT_QUOTES;

    /*
     * Database
     */

    /**
     * Hashes a string into an integer which should be very low collision risks
     * @param string $string  String to hash
     * @return int  Resulting int hash
     */
    public static function hashStringToInt($string)
    {
        $stringHash = substr(md5($string), 0, 8);
        return base_convert($stringHash, 16, 10);
    }

    public static $cachedTablePrefix = null;

    /**
     * Returns the table name prefixed by the table prefix.
     * Works in both Tracker and UI mode.
     *
     * @param string $table  The table name to prefix, ie "log_visit"
     * @return string  The table name prefixed, ie "piwik-production_log_visit"
     */
    public static function prefixTable($table)
    {
        if (is_null(self::$cachedTablePrefix)) {
            self::$cachedTablePrefix = Piwik_Config::getInstance()->database['tables_prefix'];
        }
        return self::$cachedTablePrefix . $table;
    }

    /**
     * Returns an array containing the prefixed table names of every passed argument.
     *
     * @param string ... The table names to prefix, ie "log_visit"
     * @return array The prefixed names in an array.
     */
    public static function prefixTables()
    {
        $result = array();
        foreach (func_get_args() as $table) {
            $result[] = self::prefixTable($table);
        }
        return $result;
    }

    /**
     * Returns the table name, after removing the table prefix
     *
     * @param string $table
     * @return string
     */
    public static function unprefixTable($table)
    {
        static $prefixTable = null;
        if (is_null($prefixTable)) {
            $prefixTable = Piwik_Config::getInstance()->database['tables_prefix'];
        }
        if (empty($prefixTable)
            || strpos($table, $prefixTable) !== 0
        ) {
            return $table;
        }
        $count = 1;
        return str_replace($prefixTable, '', $table, $count);
    }

    /*
     * Tracker
     */
    public static function isGoalPluginEnabled()
    {
        return Piwik_PluginsManager::getInstance()->isPluginActivated('Goals');
    }

    /*
     * URLs
     */

    /**
     * Returns the path and query part from a URL.
     * Eg. http://piwik.org/test/index.php?module=CoreHome will return /test/index.php?module=CoreHome
     *
     * @param string $url  either http://piwik.org/test or /
     * @return string
     */
    public static function getPathAndQueryFromUrl($url)
    {
        $parsedUrl = parse_url($url);
        $result = '';
        if (isset($parsedUrl['path'])) {
            $result .= substr($parsedUrl['path'], 1);
        }
        if (isset($parsedUrl['query'])) {
            $result .= '?' . $parsedUrl['query'];
        }
        return $result;
    }

    /**
     * Returns the value of a GET parameter $parameter in an URL query $urlQuery
     *
     * @param string $urlQuery  result of parse_url()['query'] and htmlentitied (& is &amp;) eg. module=test&amp;action=toto or ?page=test
     * @param string $parameter
     * @return string|bool  Parameter value if found (can be the empty string!), null if not found
     */
    public static function getParameterFromQueryString($urlQuery, $parameter)
    {
        $nameToValue = self::getArrayFromQueryString($urlQuery);
        if (isset($nameToValue[$parameter])) {
            return $nameToValue[$parameter];
        }
        return null;
    }

    /**
     * Returns an URL query string in an array format
     *
     * @param string $urlQuery
     * @return array  array( param1=> value1, param2=>value2)
     */
    public static function getArrayFromQueryString($urlQuery)
    {
        if (strlen($urlQuery) == 0) {
            return array();
        }
        if ($urlQuery[0] == '?') {
            $urlQuery = substr($urlQuery, 1);
        }
        $separator = '&';

        $urlQuery = $separator . $urlQuery;
        //		$urlQuery = str_replace(array('%20'), ' ', $urlQuery);
        $refererQuery = trim($urlQuery);

        $values = explode($separator, $refererQuery);

        $nameToValue = array();

        foreach ($values as $value) {
            $pos = strpos($value, '=');
            if ($pos !== false) {
                $name = substr($value, 0, $pos);
                $value = substr($value, $pos + 1);
                if ($value === false) {
                    $value = '';
                }
            } else {
                $name = $value;
                $value = false;
            }
            if (!empty($name)) {
                $name = Piwik_Common::sanitizeInputValue($name);
            }
            if (!empty($value)) {
                $value = Piwik_Common::sanitizeInputValue($value);
            }

            // if array without indexes
            $count = 0;
            $tmp = preg_replace('/(\[|%5b)(]|%5d)$/i', '', $name, -1, $count);
            if (!empty($tmp) && $count) {
                $name = $tmp;
                if (isset($nameToValue[$name]) == false || is_array($nameToValue[$name]) == false) {
                    $nameToValue[$name] = array();
                }
                array_push($nameToValue[$name], $value);
            } else if (!empty($name)) {
                $nameToValue[$name] = $value;
            }
        }
        return $nameToValue;
    }

    /**
     * Builds a URL from the result of parse_url function
     * Copied from the PHP comments at http://php.net/parse_url
     * @param array $parsed
     * @return bool|string
     */
    public static function getParseUrlReverse($parsed)
    {
        if (!is_array($parsed)) {
            return false;
        }

        $uri = !empty($parsed['scheme']) ? $parsed['scheme'] . ':' . (!strcasecmp($parsed['scheme'], 'mailto') ? '' : '//') : '';
        $uri .= !empty($parsed['user']) ? $parsed['user'] . (!empty($parsed['pass']) ? ':' . $parsed['pass'] : '') . '@' : '';
        $uri .= !empty($parsed['host']) ? $parsed['host'] : '';
        $uri .= !empty($parsed['port']) ? ':' . $parsed['port'] : '';

        if (!empty($parsed['path'])) {
            $uri .= (!strncmp($parsed['path'], '/', 1))
                ? $parsed['path']
                : ((!empty($uri) ? '/' : '') . $parsed['path']);
        }

        $uri .= !empty($parsed['query']) ? '?' . $parsed['query'] : '';
        $uri .= !empty($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
        return $uri;
    }

    /**
     * Returns true if the string passed may be a URL.
     * We don't need a precise test here because the value comes from the website
     * tracked source code and the URLs may look very strange.
     *
     * @param string $url
     * @return bool
     */
    public static function isLookLikeUrl($url)
    {
        return preg_match('~^(ftp|news|http|https)?://(.*)$~D', $url, $matches) !== 0
            && strlen($matches[2]) > 0;
    }

    /*
     * File operations
     */

    /**
     * ending WITHOUT slash
     *
     * @return string
     */
    public static function getPathToPiwikRoot()
    {
        return realpath(dirname(__FILE__) . "/..");
    }

    /**
     * Create directory if permitted
     *
     * @param string $path
     * @param bool $denyAccess
     */
    public static function mkdir($path, $denyAccess = true)
    {
        if (!is_dir($path)) {
            // the mode in mkdir is modified by the current umask
            @mkdir($path, $mode = 0755, $recursive = true);
        }

        // try to overcome restrictive umask (mis-)configuration
        if (!is_writable($path)) {
            @chmod($path, 0755);
            if (!is_writable($path)) {
                @chmod($path, 0775);

                // enough! we're not going to make the directory world-writeable
            }
        }

        if ($denyAccess) {
            self::createHtAccess($path, $overwrite = false);
        }
    }

    /**
     * Create .htaccess file in specified directory
     *
     * Apache-specific; for IIS @see web.config
     *
     * @param string $path     without trailing slash
     * @param bool $overwrite whether to overwrite an existing file or not
     * @param string $content
     */
    public static function createHtAccess($path, $overwrite = true, $content = "<Files \"*\">\n<IfModule mod_access.c>\nDeny from all\n</IfModule>\n<IfModule !mod_access_compat>\n<IfModule mod_authz_host.c>\nDeny from all\n</IfModule>\n</IfModule>\n<IfModule mod_access_compat>\nDeny from all\n</IfModule>\n</Files>\n")
    {
        if (self::isApache()) {
            $file = $path . '/.htaccess';
            if ($overwrite || !file_exists($file)) {
                @file_put_contents($file, $content);
            }
        }
    }

    /**
     * Get canonicalized absolute path
     * See http://php.net/realpath
     *
     * @param string $path
     * @return string  canonicalized absolute path
     */
    public static function realpath($path)
    {
        if (file_exists($path)) {
            return realpath($path);
        }
        return $path;
    }

    /**
     * Returns true if the string is a valid filename
     * File names that start with a-Z or 0-9 and contain a-Z, 0-9, underscore(_), dash(-), and dot(.) will be accepted.
     * File names beginning with anything but a-Z or 0-9 will be rejected (including .htaccess for example).
     * File names containing anything other than above mentioned will also be rejected (file names with spaces won't be accepted).
     *
     * @param string $filename
     * @return bool
     *
     */
    public static function isValidFilename($filename)
    {
        return (0 !== preg_match('/(^[a-zA-Z0-9]+([a-zA-Z_0-9.-]*))$/D', $filename));
    }

    /*
     * String operations
     */

    /**
     * byte-oriented substr() - ASCII
     *
     * @param string $string
     * @param int $start
     * @param int     ...      optional length
     * @return string
     */
    public static function substr($string, $start)
    {
        // in case mbstring overloads substr function
        $substr = function_exists('mb_orig_substr') ? 'mb_orig_substr' : 'substr';

        $length = func_num_args() > 2
            ? func_get_arg(2)
            : self::strlen($string);

        return $substr($string, $start, $length);
    }

    /**
     * byte-oriented strlen() - ASCII
     *
     * @param string $string
     * @return int
     */
    public static function strlen($string)
    {
        // in case mbstring overloads strlen function
        $strlen = function_exists('mb_orig_strlen') ? 'mb_orig_strlen' : 'strlen';
        return $strlen($string);
    }

    /**
     * multi-byte substr() - UTF-8
     *
     * @param string $string
     * @param int $start
     * @param int     ...      optional length
     * @return string
     */
    public static function mb_substr($string, $start)
    {
        $length = func_num_args() > 2
            ? func_get_arg(2)
            : self::mb_strlen($string);

        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length, 'UTF-8');
        }

        return substr($string, $start, $length);
    }

    /**
     * multi-byte strlen() - UTF-8
     *
     * @param string $string
     * @return int
     */
    public static function mb_strlen($string)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, 'UTF-8');
        }

        return strlen($string);
    }

    /**
     * multi-byte strtolower() - UTF-8
     *
     * @param string $string
     * @return string
     */
    public static function mb_strtolower($string)
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($string, 'UTF-8');
        }

        return strtolower($string);
    }

    /*
     * Escaping input
     */

    /**
     * Returns the variable after cleaning operations.
     * NB: The variable still has to be escaped before going into a SQL Query!
     *
     * If an array is passed the cleaning is done recursively on all the sub-arrays.
     * The array's keys are filtered as well!
     *
     * How this method works:
     * - The variable returned has been htmlspecialchars to avoid the XSS security problem.
     * - The single quotes are not protected so "Piwik's amazing" will still be "Piwik's amazing".
     *
     * - Transformations are:
     *         - '&' (ampersand) becomes '&amp;'
     *         - '"'(double quote) becomes '&quot;'
     *         - '<' (less than) becomes '&lt;'
     *         - '>' (greater than) becomes '&gt;'
     * - It handles the magic_quotes setting.
     * - A non string value is returned without modification
     *
     * @param mixed $value The variable to be cleaned
     * @param bool $alreadyStripslashed
     * @throws Exception
     * @return mixed  The variable after cleaning
     */
    public static function sanitizeInputValues($value, $alreadyStripslashed = false)
    {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_string($value)) {
            $value = self::sanitizeInputValue($value);

            if (!$alreadyStripslashed) // a JSON array was already stripslashed, don't do it again for each value
            {
                $value = self::undoMagicQuotes($value);
            }
        } elseif (is_array($value)) {
            foreach (array_keys($value) as $key) {
                $newKey = $key;
                $newKey = self::sanitizeInputValues($newKey, $alreadyStripslashed);
                if ($key != $newKey) {
                    $value[$newKey] = $value[$key];
                    unset($value[$key]);
                }

                $value[$newKey] = self::sanitizeInputValues($value[$newKey], $alreadyStripslashed);
            }
        } elseif (!is_null($value)
            && !is_bool($value)
        ) {
            throw new Exception("The value to escape has not a supported type. Value = " . var_export($value, true));
        }
        return $value;
    }

    /**
     * Sanitize a single input value
     *
     * @param string $value
     * @return string  sanitized input
     */
    public static function sanitizeInputValue($value)
    {
        // $_GET and $_REQUEST already urldecode()'d
        // decode
        // note: before php 5.2.7, htmlspecialchars() double encodes &#x hex items
        $value = html_entity_decode($value, Piwik_Common::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        // filter
        $value = str_replace(array("\n", "\r", "\0"), '', $value);

        // escape
        $tmp = @htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        // note: php 5.2.5 and above, htmlspecialchars is destructive if input is not UTF-8
        if ($value != '' && $tmp == '') {
            // convert and escape
            $value = utf8_encode($value);
            $tmp = htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');
        }
        return $tmp;
    }

    /**
     * Unsanitize a single input value
     *
     * @param string $value
     * @return string  unsanitized input
     */
    public static function unsanitizeInputValue($value)
    {
        return htmlspecialchars_decode($value, self::HTML_ENCODING_QUOTE_STYLE);
    }

    /**
     * Unsanitize one or more values.
     *
     * @param string|array $value
     * @return string|array  unsanitized input
     */
    public static function unsanitizeInputValues($value)
    {
        if (is_array($value)) {
            $result = array();
            foreach ($value as $key => $arrayValue) {
                $result[$key] = self::unsanitizeInputValues($arrayValue);
            }
            return $result;
        } else {
            return self::unsanitizeInputValue($value);
        }
    }

    /**
     * Undo the damage caused by magic_quotes; deprecated in php 5.3 but not removed until php 5.4
     *
     * @param string
     * @return string  modified or not
     */
    public static function undoMagicQuotes($value)
    {
        return version_compare(PHP_VERSION, '5.4', '<')
            && get_magic_quotes_gpc()
            ? stripslashes($value)
            : $value;
    }

    /**
     * Returns a sanitized variable value from the $_GET and $_POST superglobal.
     * If the variable doesn't have a value or an empty value, returns the defaultValue if specified.
     * If the variable doesn't have neither a value nor a default value provided, an exception is raised.
     *
     * @see sanitizeInputValues() for the applied sanitization
     *
     * @param string $varName            name of the variable
     * @param string $varDefault         default value. If '', and if the type doesn't match, exit() !
     * @param string $varType            Expected type, the value must be one of the following: array, int, integer, string, json
     * @param array $requestArrayToUse
     *
     * @throws Exception  if the variable type is not known
     *                    or if the variable we want to read doesn't have neither a value nor a default value specified
     *
     * @return mixed The variable after cleaning
     */
    public static function getRequestVar($varName, $varDefault = null, $varType = null, $requestArrayToUse = null)
    {
        if (is_null($requestArrayToUse)) {
            $requestArrayToUse = $_GET + $_POST;
        }
        $varDefault = self::sanitizeInputValues($varDefault);
        if ($varType === 'int') {
            // settype accepts only integer
            // 'int' is simply a shortcut for 'integer'
            $varType = 'integer';
        }

        // there is no value $varName in the REQUEST so we try to use the default value
        if (empty($varName)
            || !isset($requestArrayToUse[$varName])
            || (!is_array($requestArrayToUse[$varName])
                && strlen($requestArrayToUse[$varName]) === 0
            )
        ) {
            if (is_null($varDefault)) {
                throw new Exception("The parameter '$varName' isn't set in the Request, and a default value wasn't provided.");
            } else {
                if (!is_null($varType)
                    && in_array($varType, array('string', 'integer', 'array'))
                ) {
                    settype($varDefault, $varType);
                }
                return $varDefault;
            }
        }

        // Normal case, there is a value available in REQUEST for the requested varName:

        // we deal w/ json differently
        if ($varType == 'json') {
            $value = self::undoMagicQuotes($requestArrayToUse[$varName]);
            $value = Piwik_Common::json_decode($value, $assoc = true);
            return self::sanitizeInputValues($value, $alreadyStripslashed = true);
        }

        $value = self::sanitizeInputValues($requestArrayToUse[$varName]);
        if (!is_null($varType)) {
            $ok = false;

            if ($varType === 'string') {
                if (is_string($value)) $ok = true;
            } elseif ($varType === 'integer') {
                if ($value == (string)(int)$value) $ok = true;
            } elseif ($varType === 'float') {
                if ($value == (string)(float)$value) $ok = true;
            } elseif ($varType === 'array') {
                if (is_array($value)) $ok = true;
            } else {
                throw new Exception("\$varType specified is not known. It should be one of the following: array, int, integer, float, string");
            }

            // The type is not correct
            if ($ok === false) {
                if ($varDefault === null) {
                    throw new Exception("The parameter '$varName' doesn't have a correct type, and a default value wasn't provided.");
                } // we return the default value with the good type set
                else {
                    settype($varDefault, $varType);
                    return $varDefault;
                }
            }
            settype($value, $varType);
        }
        return $value;
    }

    /*
     * Generating unique strings
     */

    /**
     * Returns a 32 characters long uniq ID
     *
     * @return string 32 chars
     */
    public static function generateUniqId()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Get salt from [superuser] section
     *
     * @return string
     */
    public static function getSalt()
    {
        static $salt = null;
        if (is_null($salt)) {
            $salt = @Piwik_Config::getInstance()->superuser['salt'];
        }
        return $salt;
    }

    /**
     * Configureable hash() algorithm (defaults to md5)
     *
     * @param string $str String to be hashed
     * @param bool $raw_output
     * @return string Hash string
     */
    public static function hash($str, $raw_output = false)
    {
        static $hashAlgorithm = null;
        if (is_null($hashAlgorithm)) {
            $hashAlgorithm = @Piwik_Config::getInstance()->General['hash_algorithm'];
        }

        if ($hashAlgorithm) {
            $hash = @hash($hashAlgorithm, $str, $raw_output);
            if ($hash !== false)
                return $hash;
        }

        return md5($str, $raw_output);
    }

    /**
     * Generate random string
     *
     * @param int $length string length
     * @param string $alphabet characters allowed in random string
     * @return string  random string with given length
     */
    public static function getRandomString($length = 16, $alphabet = "abcdefghijklmnoprstuvwxyz0123456789")
    {
        $chars = $alphabet;
        $str = '';

        list($usec, $sec) = explode(" ", microtime());
        $seed = ((float)$sec + (float)$usec) * 100000;
        mt_srand($seed);

        for ($i = 0; $i < $length; $i++) {
            $rand_key = mt_rand(0, strlen($chars) - 1);
            $str .= substr($chars, $rand_key, 1);
        }
        return str_shuffle($str);
    }

    /*
     * Conversions
     */

    /**
     * Convert hexadecimal representation into binary data.
     * !! Will emit warning if input string is not hex!!
     *
     * @see http://php.net/bin2hex
     *
     * @param string $str  Hexadecimal representation
     * @return string
     */
    public static function hex2bin($str)
    {
        return pack("H*", $str);
    }

    /**
     * This function will convert the input string to the binary representation of the ID
     * but it will throw an Exception if the specified input ID is not correct
     *
     * This is used when building segments containing visitorId which could be an invalid string
     * therefore throwing Unexpected PHP error [pack(): Type H: illegal hex digit i] severity [E_WARNING]
     *
     * It would be simply to silent fail the pack() call above but in all other cases, we don't expect an error,
     * so better be safe and get the php error when something unexpected is happening
     * @param string $id
     * @throws Exception
     * @return string  binary string
     */
    public static function convertVisitorIdToBin($id)
    {
        if (strlen($id) !== Piwik_Tracker::LENGTH_HEX_ID_STRING
            || @bin2hex(self::hex2bin($id)) != $id
        ) {
            throw new Exception("visitorId is expected to be a " . Piwik_Tracker::LENGTH_HEX_ID_STRING . " hex char string");
        }
        return self::hex2bin($id);
    }

    /**
     * Convert IP address (in network address format) to presentation format.
     * This is a backward compatibility function for code that only expects
     * IPv4 addresses (i.e., doesn't support IPv6).
     *
     * @see Piwik_IP::N2P()
     *
     * This function does not support the long (or its string representation)
     * returned by the built-in ip2long() function, from Piwik 1.3 and earlier.
     *
     * @deprecated 1.4
     *
     * @param string $ip  IP address in network address format
     * @return string
     */
    public static function long2ip($ip)
    {
        return Piwik_IP::long2ip($ip);
    }

    /**
     * Should we use the replacement json_encode/json_decode functions?
     *
     * @return bool  True if broken; false otherwise
     */
    private static function useJsonLibrary()
    {
        static $useLib;

        if (!isset($useLib)) {
            /*
             * 5.1.x - doesn't have json extension; we use lib/upgradephp instead
             * 5.2 to 5.2.4 - broken in various ways, including:
             *
             * @see https://bugs.php.net/bug.php?id=38680 'json_decode cannot decode basic types'
             * @see https://bugs.php.net/bug.php?id=41403 'json_decode cannot decode floats'
             * @see https://bugs.php.net/bug.php?id=42785 'json_encode outputs numbers according to locale'
             */
            $useLib = false;
            if (version_compare(PHP_VERSION, '5.2.1') < 0) {
                $useLib = true;
            } else if (version_compare(PHP_VERSION, '5.2.5') < 0) {
                $info = localeconv();
                $useLib = $info['decimal_point'] != '.';
            }
        }

        return $useLib;
    }

    /**
     * JSON encode wrapper
     * - missing or broken in some php 5.x versions
     *
     * @param mixed $value
     * @return string
     */
    public static function json_encode($value)
    {
        if (self::useJsonLibrary()) {
            return _json_encode($value);
        }

        return @json_encode($value);
    }

    /**
     * JSON decode wrapper
     * - missing or broken in some php 5.x versions
     *
     * @param string $json
     * @param bool $assoc
     * @return mixed
     */
    public static function json_decode($json, $assoc = false)
    {
        if (self::useJsonLibrary()) {
            return _json_decode($json, $assoc);
        }

        return json_decode($json, $assoc);
    }

    /*
     * DataFiles
     */

    /**
     * Returns list of continent codes
     *
     * @see core/DataFiles/Countries.php
     *
     * @return array  Array of 3 letter continent codes
     */
    public static function getContinentsList()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';

        $continentsList = $GLOBALS['Piwik_ContinentList'];
        return $continentsList;
    }

    /**
     * Returns list of valid country codes
     *
     * @see core/DataFiles/Countries.php
     *
     * @param bool $includeInternalCodes
     * @return array  Array of (2 letter ISO codes => 3 letter continent code)
     */
    public static function getCountriesList($includeInternalCodes = false)
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';

        $countriesList = $GLOBALS['Piwik_CountryList'];
        $extras = $GLOBALS['Piwik_CountryList_Extras'];

        if ($includeInternalCodes) {
            return array_merge($countriesList, $extras);
        }
        return $countriesList;
    }

    /**
     * Returns list of valid language codes
     *
     * @see core/DataFiles/Languages.php
     *
     * @return array  Array of 2 letter ISO codes => Language name (in English)
     */
    public static function getLanguagesList()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';

        $languagesList = $GLOBALS['Piwik_LanguageList'];
        return $languagesList;
    }

    /**
     * Returns list of language to country mappings
     *
     * @see core/DataFiles/LanguageToCountry.php
     *
     * @return array  Array of ( 2 letter ISO language codes => 2 letter ISO country codes )
     */
    public static function getLanguageToCountryList()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/LanguageToCountry.php';

        $languagesList = $GLOBALS['Piwik_LanguageToCountry'];
        return $languagesList;
    }

    /**
     * Returns list of search engines by URL
     *
     * @see core/DataFiles/SearchEngines.php
     *
     * @return array  Array of ( URL => array( searchEngineName, keywordParameter, path, charset ) )
     */
    public static function getSearchEngineUrls()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/SearchEngines.php';

        $searchEngines = $GLOBALS['Piwik_SearchEngines'];
        return $searchEngines;
    }

    /**
     * Returns list of search engines by name
     *
     * @see core/DataFiles/SearchEngines.php
     *
     * @return array  Array of ( searchEngineName => URL )
     */
    public static function getSearchEngineNames()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/SearchEngines.php';

        $searchEngines = $GLOBALS['Piwik_SearchEngines_NameToUrl'];
        return $searchEngines;
    }

    /**
     * Returns list of provider names
     *
     * @see core/DataFiles/Providers.php
     *
     * @return array  Array of ( dnsName => providerName )
     */
    public static function getProviderNames()
    {
        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Providers.php';

        $providers = $GLOBALS['Piwik_ProviderNames'];
        return $providers;
    }

    /*
     * Language, country, continent
     */

    /**
     * Returns the browser language code, eg. "en-gb,en;q=0.5"
     *
     * @param string $browserLang  Optional browser language, otherwise taken from the request header
     * @return string
     */
    public static function getBrowserLanguage($browserLang = NULL)
    {
        static $replacementPatterns = array(
            // extraneous bits of RFC 3282 that we ignore
            '/(\\\\.)/', // quoted-pairs
            '/(\s+)/', // CFWcS white space
            '/(\([^)]*\))/', // CFWS comments
            '/(;q=[0-9.]+)/', // quality

            // found in the LANG environment variable
            '/\.(.*)/', // charset (e.g., en_CA.UTF-8)
            '/^C$/', // POSIX 'C' locale
        );

        if (is_null($browserLang)) {
            $browserLang = self::sanitizeInputValues(@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (empty($browserLang) && self::isPhpCliMode()) {
                $browserLang = @getenv('LANG');
            }
        }

        if (is_null($browserLang)) {
            // a fallback might be to infer the language in HTTP_USER_AGENT (i.e., localized build)
            $browserLang = "";
        } else {
            // language tags are case-insensitive per HTTP/1.1 s3.10 but the region may be capitalized per ISO3166-1;
            // underscores are not permitted per RFC 4646 or 4647 (which obsolete RFC 1766 and 3066),
            // but we guard against a bad user agent which naively uses its locale
            $browserLang = strtolower(str_replace('_', '-', $browserLang));

            // filters
            $browserLang = preg_replace($replacementPatterns, '', $browserLang);

            $browserLang = preg_replace('/((^|,)chrome:.*)/', '', $browserLang, 1); // Firefox bug
            $browserLang = preg_replace('/(,)(?:en-securid,)|(?:(^|,)en-securid(,|$))/', '$1', $browserLang, 1); // unregistered language tag

            $browserLang = str_replace('sr-sp', 'sr-rs', $browserLang); // unofficial (proposed) code in the wild
        }

        return $browserLang;
    }

    /**
     * Returns the visitor country based on the Browser 'accepted language'
     * information, but provides a hook for geolocation via IP address.
     *
     * @param string $lang                          browser lang
     * @param bool $enableLanguageToCountryGuess  If set to true, some assumption will be made and detection guessed more often, but accuracy could be affected
     * @param string $ip
     * @return string  2 letter ISO code
     */
    public static function getCountry($lang, $enableLanguageToCountryGuess, $ip)
    {
        $country = null;
        Piwik_PostEvent('Common.getCountry', $country, $ip);
        if (!empty($country)) {
            return strtolower($country);
        }

        if (empty($lang) || strlen($lang) < 2 || $lang == 'xx') {
            return 'xx';
        }

        $validCountries = self::getCountriesList();
        return self::extractCountryCodeFromBrowserLanguage($lang, $validCountries, $enableLanguageToCountryGuess);
    }

    /**
     * Returns list of valid country codes
     *
     * @param string $browserLanguage
     * @param array $validCountries                 Array of valid countries
     * @param bool $enableLanguageToCountryGuess  (if true, will guess country based on language that lacks region information)
     * @return array Array of 2 letter ISO codes
     */
    public static function extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $enableLanguageToCountryGuess)
    {
        $langToCountry = self::getLanguageToCountryList();

        if ($enableLanguageToCountryGuess) {
            if (preg_match('/^([a-z]{2,3})(?:,|;|$)/', $browserLanguage, $matches)) {
                // match language (without region) to infer the country of origin
                if (array_key_exists($matches[1], $langToCountry)) {
                    return $langToCountry[$matches[1]];
                }
            }
        }

        if (!empty($validCountries) && preg_match_all('/[-]([a-z]{2})/', $browserLanguage, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $parts) {
                // match location; we don't make any inferences from the language
                if (array_key_exists($parts[1], $validCountries)) {
                    return $parts[1];
                }
            }
        }
        return 'xx';
    }

    /**
     * Returns the visitor language based only on the Browser 'accepted language' information
     *
     * @param $browserLanguage  Browser's accepted langauge header
     * @param $validLanguages   array of valid language codes
     * @return string  2 letter ISO 639 code
     */
    public static function extractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages)
    {
        // assumes language preference is sorted;
        // does not handle language-script-region tags or language range (*)
        if (!empty($validLanguages) && preg_match_all('/(?:^|,)([a-z]{2,3})([-][a-z]{2})?/', $browserLanguage, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $parts) {
                if (count($parts) == 3) {
                    // match locale (language and location)
                    if (in_array($parts[1] . $parts[2], $validLanguages)) {
                        return $parts[1] . $parts[2];
                    }
                }
                // match language only (where no region provided)
                if (in_array($parts[1], $validLanguages)) {
                    return $parts[1];
                }
            }
        }
        return 'xx';
    }

    /**
     * Returns the continent of a given country
     *
     * @param string $country  2 letters isocode
     *
     * @return string  Continent (3 letters code : afr, asi, eur, amn, ams, oce)
     */
    public static function getContinent($country)
    {
        $countryList = self::getCountriesList();
        if (isset($countryList[$country])) {
            return $countryList[$country];
        }
        return 'unk';
    }

    /*
     * Campaign
     */

    /**
     * Returns the list of Campaign parameter names that will be read to classify
     * a visit as coming from a Campaign
     *
     * @return array array(
     *            0 => array( ... ) // campaign names parameters
     *            1 => array( ... ) // campaign keyword parameters
     * );
     */
    public static function getCampaignParameters()
    {
        $return = array(
            Piwik_Config::getInstance()->Tracker['campaign_var_name'],
            Piwik_Config::getInstance()->Tracker['campaign_keyword_var_name'],
        );

        foreach ($return as &$list) {
            if (strpos($list, ',') !== false) {
                $list = explode(',', $list);
            } else {
                $list = array($list);
            }
        }

        array_walk_recursive($return, 'trim');
        return $return;
    }

    /*
     * Referrer
     */

    /**
     * Reduce URL to more minimal form.  2 letter country codes are
     * replaced by '{}', while other parts are simply removed.
     *
     * Examples:
     *   www.example.com -> example.com
     *   search.example.com -> example.com
     *   m.example.com -> example.com
     *   de.example.com -> {}.example.com
     *   example.de -> example.{}
     *   example.co.uk -> example.{}
     *
     * @param string $url
     * @return string
     */
    public static function getLossyUrl($url)
    {
        static $countries;
        if (!isset($countries)) {
            $countries = implode('|', array_keys(self::getCountriesList(true)));
        }

        return preg_replace(
            array(
                 '/^(w+[0-9]*|search)\./',
                 '/(^|\.)m\./',
                 '/(\.(com|org|net|co|it|edu))?\.(' . $countries . ')(\/|$)/',
                 '/(^|\.)(' . $countries . ')\./',
            ),
            array(
                 '',
                 '$1',
                 '.{}$4',
                 '$1{}.',
            ),
            $url);
    }

    /**
     * Extracts a keyword from a raw not encoded URL.
     * Will only extract keyword if a known search engine has been detected.
     * Returns the keyword:
     * - in UTF8: automatically converted from other charsets when applicable
     * - strtolowered: "QUErY test!" will return "query test!"
     * - trimmed: extra spaces before and after are removed
     *
     * Lists of supported search engines can be found in /core/DataFiles/SearchEngines.php
     * The function returns false when a keyword couldn't be found.
     *     eg. if the url is "http://www.google.com/partners.html" this will return false,
     *       as the google keyword parameter couldn't be found.
     *
     * @see unit tests in /tests/core/Common.test.php
     * @param string $referrerUrl  URL referer URL, eg. $_SERVER['HTTP_REFERER']
     * @return array|false false if a keyword couldn't be extracted,
     *                        or array(
     *                            'name' => 'Google',
     *                            'keywords' => 'my searched keywords')
     */
    public static function extractSearchEngineInformationFromUrl($referrerUrl)
    {
        $refererParsed = @parse_url($referrerUrl);
        $refererHost = '';
        if (isset($refererParsed['host'])) {
            $refererHost = $refererParsed['host'];
        }
        if (empty($refererHost)) {
            return false;
        }
        // some search engines (eg. Bing Images) use the same domain
        // as an existing search engine (eg. Bing), we must also use the url path
        $refererPath = '';
        if (isset($refererParsed['path'])) {
            $refererPath = $refererParsed['path'];
        }

        // no search query
        if (!isset($refererParsed['query'])) {
            $refererParsed['query'] = '';
        }
        $query = $refererParsed['query'];

        // Google Referrers URLs sometimes have the fragment which contains the keyword
        if (!empty($refererParsed['fragment'])) {
            $query .= '&' . $refererParsed['fragment'];
        }

        $searchEngines = self::getSearchEngineUrls();

        $hostPattern = self::getLossyUrl($refererHost);
        if (array_key_exists($refererHost . $refererPath, $searchEngines)) {
            $refererHost = $refererHost . $refererPath;
        } elseif (array_key_exists($hostPattern . $refererPath, $searchEngines)) {
            $refererHost = $hostPattern . $refererPath;
        } elseif (array_key_exists($hostPattern, $searchEngines)) {
            $refererHost = $hostPattern;
        } elseif (!array_key_exists($refererHost, $searchEngines)) {
            if (!strncmp($query, 'cx=partner-pub-', 15)) {
                // Google custom search engine
                $refererHost = 'google.com/cse';
            } elseif (!strncmp($refererPath, '/pemonitorhosted/ws/results/', 28)) {
                // private-label search powered by InfoSpace Metasearch
                $refererHost = 'wsdsold.infospace.com';
            } elseif (strpos($refererHost, '.images.search.yahoo.com') != false) {
                // Yahoo! Images
                $refererHost = 'images.search.yahoo.com';
            } elseif (strpos($refererHost, '.search.yahoo.com') != false) {
                // Yahoo!
                $refererHost = 'search.yahoo.com';
            } else {
                return false;
            }
        }
        $searchEngineName = $searchEngines[$refererHost][0];
        $variableNames = null;
        if (isset($searchEngines[$refererHost][1])) {
            $variableNames = $searchEngines[$refererHost][1];
        }
        if (!$variableNames) {
            $searchEngineNames = self::getSearchEngineNames();
            $url = $searchEngineNames[$searchEngineName];
            $variableNames = $searchEngines[$url][1];
        }
        if (!is_array($variableNames)) {
            $variableNames = array($variableNames);
        }

        $key = null;
        if ($searchEngineName === 'Google Images'
            || ($searchEngineName === 'Google' && strpos($referrerUrl, '/imgres') !== false)
        ) {
            if (strpos($query, '&prev') !== false) {
                $query = urldecode(trim(self::getParameterFromQueryString($query, 'prev')));
                $query = str_replace('&', '&amp;', strstr($query, '?'));
            }
            $searchEngineName = 'Google Images';
        } else if ($searchEngineName === 'Google'
            && (strpos($query, '&as_') !== false || strpos($query, 'as_') === 0)
        ) {
            $keys = array();
            $key = self::getParameterFromQueryString($query, 'as_q');
            if (!empty($key)) {
                array_push($keys, $key);
            }
            $key = self::getParameterFromQueryString($query, 'as_oq');
            if (!empty($key)) {
                array_push($keys, str_replace('+', ' OR ', $key));
            }
            $key = self::getParameterFromQueryString($query, 'as_epq');
            if (!empty($key)) {
                array_push($keys, "\"$key\"");
            }
            $key = self::getParameterFromQueryString($query, 'as_eq');
            if (!empty($key)) {
                array_push($keys, "-$key");
            }
            $key = trim(urldecode(implode(' ', $keys)));
        }

        if ($searchEngineName === 'Google') {
            // top bar menu
            $tbm = self::getParameterFromQueryString($query, 'tbm');
            switch ($tbm) {
                case 'isch':
                    $searchEngineName = 'Google Images';
                    break;
                case 'vid':
                    $searchEngineName = 'Google Video';
                    break;
                case 'shop':
                    $searchEngineName = 'Google Shopping';
                    break;
            }
        }

        if (empty($key)) {
            foreach ($variableNames as $variableName) {
                if ($variableName[0] == '/') {
                    // regular expression match
                    if (preg_match($variableName, $referrerUrl, $matches)) {
                        $key = trim(urldecode($matches[1]));
                        break;
                    }
                } else {
                    // search for keywords now &vname=keyword
                    $key = self::getParameterFromQueryString($query, $variableName);
                    $key = trim(urldecode($key));

                    // Special case: Google & empty q parameter
                    if (empty($key)
                        && $variableName == 'q'

                        && (
                            // Google search with no keyword
                            ($searchEngineName == 'Google'
                                && ( // First, they started putting an empty q= parameter
                                    strpos($query, '&q=') !== false
                                        || strpos($query, '?q=') !== false
                                        // then they started sending the full host only (no path/query string)
                                        || (empty($query) && (empty($refererPath) || $refererPath == '/') && empty($refererParsed['fragment']))
                                )
                            )
                                // search engines with no keyword
                                || $searchEngineName == 'Google Images'
                                || $searchEngineName == 'DuckDuckGo')
                    ) {
                        $key = false;
                    }
                    if (!empty($key)
                        || $key === false
                    ) {
                        break;
                    }
                }
            }
        }

        // $key === false is the special case "No keyword provided" which is a Search engine match
        if ($key === null
            || $key === ''
        ) {
            return false;
        }

        if (!empty($key)) {
            if (function_exists('iconv')
                && isset($searchEngines[$refererHost][3])
            ) {
                // accepts string, array, or comma-separated list string in preferred order
                $charsets = $searchEngines[$refererHost][3];
                if (!is_array($charsets)) {
                    $charsets = explode(',', $charsets);
                }

                if (!empty($charsets)) {
                    $charset = $charsets[0];
                    if (count($charsets) > 1
                        && function_exists('mb_detect_encoding')
                    ) {
                        $charset = mb_detect_encoding($key, $charsets);
                        if ($charset === false) {
                            $charset = $charsets[0];
                        }
                    }

                    $newkey = @iconv($charset, 'UTF-8//IGNORE', $key);
                    if (!empty($newkey)) {
                        $key = $newkey;
                    }
                }
            }

            $key = self::mb_strtolower($key);
        }

        return array(
            'name'     => $searchEngineName,
            'keywords' => $key,
        );
    }

    /*
     * System environment
     */

    /**
     * Returns true if PHP was invoked from command-line interface (shell)
     *
     * @since added in 0.4.4
     * @return bool true if PHP invoked as a CGI or from CLI
     */
    public static function isPhpCliMode()
    {
        $remoteAddr = @$_SERVER['REMOTE_ADDR'];
        return PHP_SAPI == 'cli' ||
            (!strncmp(PHP_SAPI, 'cgi', 3) && empty($remoteAddr));
    }

    /**
     * Is the current script execution triggered by misc/cron/archive.php ?
     *
     * Helpful for error handling: directly throw error without HTML (eg. when DB is down)
     * @return bool
     */
    public static function isArchivePhpTriggered()
    {
        return !empty($_GET['trigger'])
            && $_GET['trigger'] == 'archivephp';
    }

    /**
     * Assign CLI parameters as if they were REQUEST or GET parameters.
     * You can trigger Piwik from the command line by
     * # /usr/bin/php5 /path/to/piwik/index.php -- "module=API&method=Actions.getActions&idSite=1&period=day&date=previous8&format=php"
     */
    public static function assignCliParametersToRequest()
    {
        if (isset($_SERVER['argc'])
            && $_SERVER['argc'] > 0
        ) {
            for ($i = 1; $i < $_SERVER['argc']; $i++) {
                parse_str($_SERVER['argv'][$i], $tmp);
                $_GET = array_merge($_GET, $tmp);
            }
        }
    }

    /**
     * Returns true if running on a Windows operating system
     *
     * @since 0.6.5
     * @return bool true if PHP detects it is running on Windows; else false
     */
    public static function isWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    /**
     * Returns true if running on MacOS
     *
     * @return bool true if PHP detects it is running on MacOS; else false
     */
    public static function isMacOS()
    {
        return PHP_OS === 'Darwin';
    }

    /**
     * Returns true if running on an Apache web server
     *
     * @return bool
     */
    public static function isApache()
    {
        $apache = isset($_SERVER['SERVER_SOFTWARE']) &&
            !strncmp($_SERVER['SERVER_SOFTWARE'], 'Apache', 6);

        return $apache;
    }

    /**
     * Returns true if running on Microsoft IIS 7 (or above)
     *
     * @return bool
     */
    public static function isIIS()
    {
        $iis = isset($_SERVER['SERVER_SOFTWARE']) &&
            preg_match('/^Microsoft-IIS\/(.+)/', $_SERVER['SERVER_SOFTWARE'], $matches) &&
            version_compare($matches[1], '7') >= 0;

        return $iis;
    }

    /**
     * Takes a list of fields defining numeric values and returns the corresponding
     * unnamed parameters to be bound to the field names in the where clause of a SQL query
     *
     * @param array|string $fields  array( fieldName1, fieldName2, fieldName3)  Names of the mysql table fields to load
     * @return string "?, ?, ?"
     */
    public static function getSqlStringFieldsArray($fields)
    {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        $count = count($fields);
        if ($count == 0) {
            return "''";
        }
        return '?' . str_repeat(',?', $count - 1);
    }

    /**
     * Sets outgoing header.
     *
     * @param string $header The header.
     * @param bool $replace Whether to replace existing or not.
     */
    public static function sendHeader($header, $replace = true)
    {
        if (isset($GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING']) && $GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING']) {
            @header($header, $replace);
        } else {
            header($header, $replace);
        }
    }

    /**
     * Returns the ID of the current LocationProvider (see UserCountry plugin code) from
     * the Tracker cache.
     */
    public static function getCurrentLocationProviderId()
    {
        $cache = Piwik_Tracker_Cache::getCacheGeneral();
        return empty($cache['currentLocationProviderId'])
            ? Piwik_UserCountry_LocationProvider_Default::ID
            : $cache['currentLocationProviderId'];
    }
}

/**
 * Mark orphaned object for garbage collection
 *
 * For more information: @link http://dev.piwik.org/trac/ticket/374
 * @param $var
 */
function destroy(&$var)
{
    if (is_object($var)) $var->__destruct();
    unset($var);
    $var = null;
}

if (!function_exists('printDebug')) {
    function printDebug($info = '')
    {
        if (isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG']) {
            if (is_array($info) || is_object($info)) {
                print("<pre>");
                print(htmlspecialchars(var_export($info, true), ENT_QUOTES));
                print("</pre>");
            } else {
                print(htmlspecialchars($info, ENT_QUOTES) . "<br />\n");
            }
        }
    }
}
