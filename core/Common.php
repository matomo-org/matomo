<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\CliMulti\Process;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\LanguageDataProvider;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Tracker\Cache as TrackerCache;

/**
 * Contains helper methods used by both Piwik Core and the Piwik Tracking engine.
 *
 * This is the only non-Tracker class loaded by the **\/piwik.php** file.
 */
class Common
{
    // constants used to map the referrer type to an integer in the log_visit table
    const REFERRER_TYPE_DIRECT_ENTRY = 1;
    const REFERRER_TYPE_SEARCH_ENGINE = 2;
    const REFERRER_TYPE_WEBSITE = 3;
    const REFERRER_TYPE_CAMPAIGN = 6;
    const REFERRER_TYPE_SOCIAL_NETWORK = 7;

    // Flag used with htmlspecialchar. See php.net/htmlspecialchars.
    const HTML_ENCODING_QUOTE_STYLE = ENT_QUOTES;

    public static $isCliMode = null;

    /*
     * Database
     */
    const LANGUAGE_CODE_INVALID = 'xx';

    /**
     * Hashes a string into an integer which should be very low collision risks
     * @param string $string String to hash
     * @return int  Resulting int hash
     */
    public static function hashStringToInt($string)
    {
        $stringHash = substr(md5($string), 0, 8);
        return base_convert($stringHash, 16, 10);
    }

    /**
     * Returns a prefixed table name.
     *
     * The table prefix is determined by the `[database] tables_prefix` INI config
     * option.
     *
     * @param string $table The table name to prefix, ie "log_visit"
     * @return string  The prefixed name, ie "piwik-production_log_visit".
     * @api
     */
    public static function prefixTable($table)
    {
        $prefix = Config::getInstance()->database['tables_prefix'];
        return $prefix . $table;
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
     * Removes the prefix from a table name and returns the result.
     *
     * The table prefix is determined by the `[database] tables_prefix` INI config
     * option.
     *
     * @param string $table The prefixed table name, eg "piwik-production_log_visit".
     * @return string The unprefixed table name, eg "log_visit".
     * @api
     */
    public static function unprefixTable($table)
    {
        static $prefixTable = null;
        if (is_null($prefixTable)) {
            $prefixTable = Config::getInstance()->database['tables_prefix'];
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
        return Plugin\Manager::getInstance()->isPluginActivated('Goals');
    }

    public static function isActionsPluginEnabled()
    {
        return Plugin\Manager::getInstance()->isPluginActivated('Actions');
    }

    /**
     * Returns true if PHP was invoked from command-line interface (shell)
     *
     * @since added in 0.4.4
     * @return bool true if PHP invoked as a CGI or from CLI
     */
    public static function isPhpCliMode()
    {
        if (is_bool(self::$isCliMode)) {
            return self::$isCliMode;
        }

        if(PHP_SAPI === 'cli'){
            return true;
        }

        if(self::isPhpCgiType() && (!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR']))){
            return true;
        }

        return false;
    }

    /**
     * Returns true if PHP is executed as CGI type.
     *
     * @since added in 0.4.4
     * @return bool true if PHP invoked as a CGI
     */
    public static function isPhpCgiType()
    {
        $sapiType = php_sapi_name();

        return substr($sapiType, 0, 3) === 'cgi';
    }

    /**
     * Returns true if the current request is a console command, eg.
     * ./console xx:yy
     * or
     * php console xx:yy
     *
     * @return bool
     */
    public static function isRunningConsoleCommand()
    {
        $searched = 'console';
        $consolePos = strpos($_SERVER['SCRIPT_NAME'], $searched);
        $expectedConsolePos = strlen($_SERVER['SCRIPT_NAME']) - strlen($searched);
        $isScriptIsConsole = ($consolePos === $expectedConsolePos);
        return self::isPhpCliMode() && $isScriptIsConsole;
    }

    /*
     * String operations
     */

    /**
     * Multi-byte substr() - works with UTF-8.
     *
     * Calls `mb_substr` if available and falls back to `substr` if it's not.
     *
     * @param string $string
     * @param int $start
     * @param int|null $length      optional length
     * @return string
     * @deprecated since 4.4 - directly use mb_substr instead
     */
    public static function mb_substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Gets the current process ID.
     * Note: If getmypid is disabled, a random ID will be generated once and used throughout the request. There is a
     * small chance that two processes at the same time may generated the same random ID. If you need to rely on the
     * value being 100% unique, then you may need to use `getmypid` directly or some other logic. Eg in CliMulti it is
     * fine to use `getmypid` directly as the logic won't be used if getmypid is disabled...
     * If you are wanting to use the pid to check if the process is running eg using `ps`, then you also have to use
     * getmypid directly.
     *
     * @return int|null
     */
    public static function getProcessId()
    {
        static $pid;
        if (!isset($pid)) {
            if (Process::isMethodDisabled('getmypid')) {
                $pid = Common::getRandomInt(12);
            } else {
                $pid = getmypid();
            }
        }

        return $pid;
    }

    /**
     * Multi-byte strlen() - works with UTF-8
     *
     * Calls `mb_substr` if available and falls back to `substr` if not.
     *
     * @param string $string
     * @return int
     * @deprecated since 4.4 - directly use mb_strlen instead
     */
    public static function mb_strlen($string)
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * Multi-byte strtolower() - works with UTF-8.
     *
     * Calls `mb_strtolower` if available and falls back to `strtolower` if not.
     *
     * @param string $string
     * @return string
     * @deprecated since 4.4 - directly use mb_strtolower instead
     */
    public static function mb_strtolower($string)
    {
        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Multi-byte strtoupper() - works with UTF-8.
     *
     * Calls `mb_strtoupper` if available and falls back to `strtoupper` if not.
     *
     * @param string $string
     * @return string
     * @deprecated since 4.4 - directly use mb_strtoupper instead
     */
    public static function mb_strtoupper($string)
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    /**
     * Timing attack safe string comparison.
     *
     * @param string $stringA
     * @param string $stringB
     * @return bool
     */
    public static function hashEquals(string $stringA, string $stringB)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($stringA, $stringB);
        }

        if (strlen($stringA) !== strlen($stringB)) {
            return false;
        }

        $result = "\0";
        $stringA^= $stringB;
        for ($i = 0; $i < strlen($stringA); $i++) {
            $result|= $stringA[$i];
        }

        return $result === "\0";
    }

    /**
     * Secure wrapper for unserialize, which by default disallows unserializing classes
     *
     * @param string $string String to unserialize
     * @param array $allowedClasses Class names that should be allowed to unserialize
     * @param bool $rethrow Whether to rethrow exceptions or not.
     * @return mixed
     */
    public static function safe_unserialize($string, $allowedClasses = [], $rethrow = false)
    {
        try {
            // phpcs:ignore Generic.PHP.ForbiddenFunctions
            return unserialize($string ?? '', ['allowed_classes' => empty($allowedClasses) ? false : $allowedClasses]);
        } catch (\Throwable $e) {
            if ($rethrow) {
                throw $e;
            }

            $logger = StaticContainer::get('Psr\Log\LoggerInterface');
            $logger->debug('Unable to unserialize a string: {exception} (string = {string})', [
                'exception' => $e,
                'string' => $string,
            ]);
            return false;
        }
    }

    /*
     * Escaping input
     */

    /**
     * Sanitizes a string to help avoid XSS vulnerabilities.
     *
     * This function is automatically called when {@link getRequestVar()} is called,
     * so you should not normally have to use it.
     *
     * This function should be used when outputting data that isn't escaped and was
     * obtained from the user (for example when using the `|raw` twig filter on goal names).
     *
     * _NOTE: Sanitized input should not be used directly in an SQL query; SQL placeholders
     * should still be used._
     *
     * **Implementation Details**
     *
     * - [htmlspecialchars](http://php.net/manual/en/function.htmlspecialchars.php) is used to escape text.
     * - Single quotes are not escaped so **Piwik's amazing community** will still be
     *   **Piwik's amazing community**.
     * - Use of the `magic_quotes` setting will not break this method.
     * - Boolean, numeric and null values are not modified.
     *
     * @param mixed $value The variable to be sanitized. If an array is supplied, the contents
     *                     of the array will be sanitized recursively. The keys of the array
     *                     will also be sanitized.
     * @param bool $alreadyStripslashed Implementation detail, ignore.
     * @throws Exception If `$value` is of an incorrect type.
     * @return mixed  The sanitized value.
     * @api
     */
    public static function sanitizeInputValues($value, $alreadyStripslashed = false)
    {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_string($value)) {
            $value = self::sanitizeString($value);
        } elseif (is_array($value)) {
            foreach (array_keys($value) as $key) {
                $newKey = $key;
                $newKey = self::sanitizeInputValues($newKey, $alreadyStripslashed);
                if ($key !== $newKey) {
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
     * Sanitize a single input value and removes line breaks, tabs and null characters.
     *
     * @param string $value
     * @return string  sanitized input
     */
    public static function sanitizeInputValue($value)
    {
        $value = self::sanitizeLineBreaks($value);
        $value = self::sanitizeString($value);
        return $value;
    }

    /**
     * Sanitize a single input value
     *
     * @param $value
     * @return string
     */
    private static function sanitizeString($value)
    {
        // $_GET and $_REQUEST already urldecode()'d
        // decode
        // note: before php 5.2.7, htmlspecialchars() double encodes &#x hex items
        $value = html_entity_decode($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        $value = self::sanitizeNullBytes($value);

        // escape
        $tmp = @htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        // note: php 5.2.5 and above, htmlspecialchars is destructive if input is not UTF-8
        if ($value !== '' && $tmp === '') {
            // convert and escape
            $value = utf8_encode($value);
            $tmp = htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');
            return $tmp;
        }
        return $tmp;
    }

    /**
     * Unsanitizes a single input value and returns the result.
     *
     * @param string $value
     * @return string  unsanitized input
     * @api
     */
    public static function unsanitizeInputValue($value)
    {
        return htmlspecialchars_decode($value ?? '', self::HTML_ENCODING_QUOTE_STYLE);
    }

    /**
     * Unsanitizes one or more values and returns the result.
     *
     * This method should be used when you need to unescape data that was obtained from
     * the user.
     *
     * Some data in Piwik is stored sanitized (such as site name). In this case you may
     * have to use this method to unsanitize it in order to, for example, output it in JSON.
     *
     * @param string|array $value The data to unsanitize. If an array is passed, the
     *                            array is sanitized recursively. Key values are not unsanitized.
     * @return string|array The unsanitized data.
     * @api
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
     * @param string $value
     * @return string Line breaks and line carriage removed
     */
    public static function sanitizeLineBreaks($value)
    {
        return is_null($value) ? '' : str_replace(array("\n", "\r"), '', $value);
    }

    /**
     * @param string $value
     * @return string Null bytes removed
     */
    public static function sanitizeNullBytes($value)
    {
        return str_replace(array("\0"), '', $value);
    }

    /**
     * Gets a sanitized request parameter by name from the `$_GET` and `$_POST` superglobals.
     *
     * Use this function to get request parameter values. **_NEVER use `$_GET` and `$_POST` directly._**
     *
     * If the variable cannot be found, and a default value was not provided, an exception is raised.
     *
     * _See {@link sanitizeInputValues()} to learn more about sanitization._
     *
     * @param string $varName Name of the request parameter to get. By default, we look in `$_GET[$varName]`
     *                        and `$_POST[$varName]` for the value.
     * @param string|null $varDefault The value to return if the request parameter cannot be found or has an empty value.
     * @param string|null $varType Expected type of the request variable. This parameters value must be one of the following:
     *                             `'array'`, `'int'`, `'integer'`, `'string'`, `'json'`.
     *
     *                             If `'json'`, the string value will be `json_decode`-d and then sanitized.
     * @param array|null $requestArrayToUse The array to use instead of `$_GET` and `$_POST`.
     * @throws Exception If the request parameter doesn't exist and there is no default value, or if the request parameter
     *                   exists but has an incorrect type.
     * @return mixed The sanitized request parameter.
     * @api
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
        if ($varType === 'json') {
            $value = $requestArrayToUse[$varName];
            $value = json_decode($value, $assoc = true);
            return self::sanitizeInputValues($value, $alreadyStripslashed = true);
        }

        $value = self::sanitizeInputValues($requestArrayToUse[$varName]);
        if (isset($varType)) {
            $ok = false;

            if ($varType === 'string') {
                if (is_string($value) || is_int($value)) {
                    $ok = true;
                } elseif (is_float($value)) {
                    $value = Common::forceDotAsSeparatorForDecimalPoint($value);
                    $ok    = true;
                }
            } elseif ($varType === 'integer') {
                if ($value == (string)(int)$value) {
                    $ok = true;
                }
            } elseif ($varType === 'float') {
                $valueToCompare = (string)(float)$value;
                $valueToCompare = Common::forceDotAsSeparatorForDecimalPoint($valueToCompare);

                if ($value == $valueToCompare) {
                    $ok = true;
                }
            } elseif ($varType === 'array') {
                if (is_array($value)) {
                    $ok = true;
                }
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

    /**
     * Replaces lbrace with an encoded entity to prevent angular from parsing the content
     *
     * @deprecated Will be removed, once the vue js migration is done
     *
     * @param $string
     * @return array|string|string[]|null
     */
    public static function fixLbrace($string)
    {
        $chars = array('{', '&#x7B;', '&#123;', '&lcub;', '&lbrace;', '&#x0007B;');

        static $search;
        static $replace;

        if (!isset($search)) {
            $search = array_map(function ($val) { return $val . $val; }, $chars);
        }
        if (!isset($replace)) {
            $replace = array_map(function ($val) { return $val . '&#8291;' . $val; }, $chars);
        }

        $replacedString = is_null($string) ? $string : str_replace($search, $replace, $string);

        // try to replace characters until there are no changes
        if ($string !== $replacedString) {
            return self::fixLbrace($replacedString);
        }

        return $string;
    }

    /*
     * Generating unique strings
     */

    /**
     * Generates a random integer
     *
     * @param int $min
     * @param null|int $max Defaults to max int value
     * @return int
     */
    public static function getRandomInt($min = 0, $max = null)
    {
        if (!isset($max)) {
            $max = PHP_INT_MAX;
        }
        return random_int($min, $max);
    }

    /**
     * Returns a 32 characters long uniq ID
     *
     * @return string 32 chars
     */
    public static function generateUniqId()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Configurable hash() algorithm (defaults to md5)
     *
     * @param string $str String to be hashed
     * @param bool $raw_output
     * @return string Hash string
     */
    public static function hash($str, $raw_output = false)
    {
        static $hashAlgorithm = null;

        if (is_null($hashAlgorithm)) {
            $hashAlgorithm = @Config::getInstance()->General['hash_algorithm'];
        }

        if ($hashAlgorithm) {
            $hash = @hash($hashAlgorithm, $str, $raw_output);
            if ($hash !== false) {
                return $hash;
            }
        }

        return md5($str, $raw_output);
    }

    /**
     * Generate random string.
     *
     * @param int $length string length
     * @param string $alphabet characters allowed in random string
     * @return string  random string with given length
     */
    public static function getRandomString($length = 16, $alphabet = "abcdefghijklmnoprstuvwxyz0123456789")
    {
        $chars = $alphabet;
        $str   = '';

        for ($i = 0; $i < $length; $i++) {
            $rand_key = self::getRandomInt(0, strlen($chars) - 1);
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
     * @param string $str Hexadecimal representation
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
        if (strlen($id) !== Tracker::LENGTH_HEX_ID_STRING
            || @bin2hex(self::hex2bin($id)) != $id
        ) {
            throw new Exception("visitorId is expected to be a " . Tracker::LENGTH_HEX_ID_STRING . " hex char string");
        }

        return self::hex2bin($id);
    }

    /**
     * Converts a User ID string to the Visitor ID Binary representation.
     *
     * @param $userId
     * @return string
     */
    public static function convertUserIdToVisitorIdBin($userId)
    {
        $userIdHashed = \MatomoTracker::getUserIdHashed($userId);

        return self::convertVisitorIdToBin($userIdHashed);
    }

    /**
     * Detects whether an error occurred during the last json encode/decode.
     * @return bool
     */
    public static function hasJsonErrorOccurred()
    {
        return json_last_error() != JSON_ERROR_NONE;
    }

    /**
     * Returns a human readable error message in case an error occurred during the last json encode/decode.
     * Returns an empty string in case there was no error.
     *
     * @return string
     */
    public static function getLastJsonError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return '';
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }

        return 'Unknown error';
    }

    public static function stringEndsWith($haystack, $needle)
    {
        if (strlen(strval($needle)) === 0) {
            return true;
        }

        if (strlen(strval($haystack)) === 0) {
            return false;
        }

        $lastCharacters = substr($haystack, -strlen($needle));

        return $lastCharacters === $needle;
    }

    /**
     * Returns the list of parent classes for the given class.
     *
     * @param  string    $class   A class name.
     * @return string[]  The list of parent classes in order from highest ancestor to the descended class.
     */
    public static function getClassLineage($class)
    {
        $classes = array_merge(array($class), array_values(class_parents($class, $autoload = false)));

        return array_reverse($classes);
    }

    /*
     * DataFiles
     */

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
     * @param string|null $browserLang Optional browser language, otherwise taken from the request header
     * @return string
     */
    public static function getBrowserLanguage($browserLang = null)
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
            $browserLang = self::sanitizeInputValues($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
            if (empty($browserLang) && self::isPhpCliMode()) {
                $browserLang = @getenv('LANG');
            }
        }

        if (empty($browserLang)) {
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
     * @param string $lang browser lang
     * @param bool $enableLanguageToCountryGuess If set to true, some assumption will be made and detection guessed more often, but accuracy could be affected
     * @param string $ip
     * @return string  2 letter ISO code
     */
    public static function getCountry($lang, $enableLanguageToCountryGuess, $ip)
    {
        if (empty($lang) || strlen($lang) < 2 || $lang === self::LANGUAGE_CODE_INVALID) {
            return self::LANGUAGE_CODE_INVALID;
        }

        /** @var RegionDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $validCountries = $dataProvider->getCountryList();

        return self::extractCountryCodeFromBrowserLanguage($lang, $validCountries, $enableLanguageToCountryGuess);
    }

    /**
     * Returns list of valid country codes
     *
     * @param string $browserLanguage
     * @param array $validCountries Array of valid countries
     * @param bool $enableLanguageToCountryGuess (if true, will guess country based on language that lacks region information)
     * @return array Array of 2 letter ISO codes
     */
    public static function extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $enableLanguageToCountryGuess)
    {
        /** @var LanguageDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\LanguageDataProvider');

        $langToCountry = $dataProvider->getLanguageToCountryList();

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
        return self::LANGUAGE_CODE_INVALID;
    }

    /**
     * Returns the language string, based only on the Browser 'accepted language' information.
     * * The language tag is defined by ISO 639-1
     *
     * @param string $browserLanguage Browser's accepted language header
     * @param array $validLanguages array of valid language codes
     * @return string  2 letter ISO 639 code  'es' (Spanish)
     */
    public static function extractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages = array())
    {
        $languageRegionCode = self::extractLanguageAndRegionCodeFromBrowserLanguage($browserLanguage, $validLanguages);
        $validLanguages = self::checkValidLanguagesIsSet($validLanguages);

        if (strlen($languageRegionCode) === 2) {
            $languageCode = $languageRegionCode;
        } else {
            $languageCode = substr($languageRegionCode, 0, 2);
        }
        if (in_array($languageCode, $validLanguages)) {
            return $languageCode;
        }
        return self::LANGUAGE_CODE_INVALID;
    }

    /**
     * Returns the language and region string, based only on the Browser 'accepted language' information.
     * * The language tag is defined by ISO 639-1
     * * The region tag is defined by ISO 3166-1
     *
     * @param string $browserLanguage Browser's accepted language header
     * @param array $validLanguages array of valid language/region codes.
     * @return string 2-letter ISO 639 code 'es' (Spanish) or if found, includes the region as well: 'es-ar'
     */
    public static function extractLanguageAndRegionCodeFromBrowserLanguage($browserLanguage, $validLanguages = array())
    {
        $forceRegionValidation = !empty($validLanguages);
        $validLanguages = self::checkValidLanguagesIsSet($validLanguages);

        if (!preg_match_all('/(?:^|,)([a-z]{2,3})(?:[-][a-z]{4})?([-][a-z]{2})?/', $browserLanguage, $matches, PREG_SET_ORDER)) {
            return self::LANGUAGE_CODE_INVALID;
        }
        foreach ($matches as $parts) {
            $langIso639 = $parts[1];
            if (empty($langIso639)) {
                continue;
            }

            // If a region tag is found eg. "fr-ca"
            if (count($parts) === 3) {
                $regionIso3166 = $parts[2]; // eg. "-ca"

                if (in_array($langIso639 . $regionIso3166, $validLanguages)) {
                    return $langIso639 . $regionIso3166;
                }

                // if a set of valid codes was provided, we do not append the region if it was not included
                if (in_array($langIso639, $validLanguages) && !$forceRegionValidation) {
                    return $langIso639 . $regionIso3166;
                }
            }
            // eg. "fr" or "es"
            if (in_array($langIso639, $validLanguages)) {
                return $langIso639;
            }
        }
        return self::LANGUAGE_CODE_INVALID;
    }

    /**
     * Returns the continent of a given country
     *
     * @param string $country 2 letters iso code
     *
     * @return string  Continent (3 letters code : afr, asi, eur, amn, ams, oce)
     */
    public static function getContinent($country)
    {
        /** @var RegionDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $countryList = $dataProvider->getCountryList();

        if ($country === 'ti') {
            $country = 'cn';
        }

        return isset($countryList[$country]) ? $countryList[$country] : 'unk';
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
            Config::getInstance()->Tracker['campaign_var_name'],
            Config::getInstance()->Tracker['campaign_keyword_var_name'],
        );

        foreach ($return as &$list) {
            if (strpos($list, ',') !== false) {
                $list = explode(',', $list);
            } else {
                $list = array($list);
            }
            $list = array_map('trim', $list);
        }

        return $return;
    }

    /*
     * Referrer
     */

    /**
     * Returns a string with a comma separated list of placeholders for use in an SQL query. Used mainly
     * to fill the `IN (...)` part of a query.
     *
     * @param array|string $fields The names of the mysql table fields to bind, e.g.
     *                             `array(fieldName1, fieldName2, fieldName3)`.
     *
     *                             _Note: The content of the array isn't important, just its length._
     * @return string The placeholder string, e.g. `"?, ?, ?"`.
     * @api
     */
    public static function getSqlStringFieldsArray($fields)
    {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        $count = count($fields);
        if ($count === 0) {
            return "''";
        }
        return '?' . str_repeat(',?', $count - 1);
    }

    /**
     * Force the separator for decimal point to be a dot. See https://github.com/piwik/piwik/issues/6435
     * If for instance a German locale is used it would be a comma otherwise.
     *
     * @param  float|string $value
     * @return string
     */
    public static function forceDotAsSeparatorForDecimalPoint($value)
    {
        if (null === $value || false === $value) {
            return $value;
        }

        return str_replace(',', '.', $value);
    }

    /**
     * Sets outgoing header.
     *
     * @param string $header The header.
     * @param bool $replace Whether to replace existing or not.
     */
    public static function sendHeader($header, $replace = true)
    {
        // don't send header in CLI mode
        if (!Common::isPhpCliMode() and !headers_sent()) {
            header($header, $replace);
        }
    }

    /**
     * Strips outgoing header.
     *
     * @param string $name The header name.
     */
    public static function stripHeader($name)
    {
        // don't strip header in CLI mode
        if (!Common::isPhpCliMode() and !headers_sent()) {
            header_remove($name);
        }
    }

    /**
     * Sends the given response code if supported.
     *
     * @param int $code  Eg 204
     *
     * @throws Exception
     */
    public static function sendResponseCode($code)
    {
        $messages = array(
            200 => 'Ok',
            204 => 'No Response',
            301 => 'Moved Permanently',
            302 => 'Found',
            304 => 'Not Modified',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        );

        if (!array_key_exists($code, $messages)) {
            throw new Exception('Response code not supported: ' . $code);
        }

        if (strpos(PHP_SAPI, '-fcgi') === false) {
            $key = 'HTTP/1.1';

            if (array_key_exists('SERVER_PROTOCOL', $_SERVER)
                && strlen($_SERVER['SERVER_PROTOCOL']) < 15
                && strlen($_SERVER['SERVER_PROTOCOL']) > 1) {
                $key = $_SERVER['SERVER_PROTOCOL'];
            }
        } else {
            // FastCGI
            $key = 'Status:';
        }

        $message = $messages[$code];
        Common::sendHeader($key . ' ' . $code . ' ' . $message);
    }

    /**
     * Returns the ID of the current LocationProvider (see UserCountry plugin code) from
     * the Tracker cache.
     */
    public static function getCurrentLocationProviderId()
    {
        $cache = TrackerCache::getCacheGeneral();
        return empty($cache['currentLocationProviderId'])
            ? Plugins\UserCountry\LocationProvider::getDefaultProviderId()
            : $cache['currentLocationProviderId'];
    }

    /**
     * Marks an orphaned object for garbage collection.
     *
     * For more information: {@link https://github.com/piwik/piwik/issues/374}
     * @param mixed $var The object to destroy.
     * @api
     */
    public static function destroy(&$var)
    {
        if (is_object($var) && method_exists($var, '__destruct')) {
            $var->__destruct();
        }
        unset($var);
        $var = null;
    }

    /**
     * @deprecated Use the logger directly instead.
     */
    public static function printDebug($info = '')
    {
        if (is_object($info)) {
            $info = var_export($info, true);
        }

        $logger = StaticContainer::get('Psr\Log\LoggerInterface');
        if (is_array($info) || is_object($info)) {
            $out = var_export($info, true);
            $logger->debug($out);
        } else {
            $logger->debug($info);
        }
    }

    /**
     * Returns true if the request is an AJAX request.
     *
     * @return bool
     */
    public static function isXmlHttpRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * @param $validLanguages
     * @return array
     */
    protected static function checkValidLanguagesIsSet($validLanguages)
    {
        /** @var LanguageDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\LanguageDataProvider');

        if (empty($validLanguages)) {
            $validLanguages = array_keys($dataProvider->getLanguageList());
            return $validLanguages;
        }
        return $validLanguages;
    }
}
