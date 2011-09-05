<?php
/**
 * Inspekt - main source file
 *
 * @author Chris Shiflett <chris@shiflett.org>
 * @author Ed Finkler <coj@funkatron.com>
 *
 * @package Inspekt
 */

/**
 * Inspekt_Error
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Inspekt/Error.php');

/**
 * Inspekt_Cage
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Inspekt/Cage.php');

/**
 * Inspekt_Cage_Session
 */
//require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Inspekt/Cage/Session.php');

/**
 * Inspekt_Supercage
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Inspekt/Supercage.php');


/**
 * Options for isHostname() that specify which types of hostnames
 * to allow.
 *
 * HOST_ALLOW_DNS:   Allows Internet domain names (e.g.,
 *                   example.com).
 */
define('ISPK_HOST_ALLOW_DNS', 1);

/**
 * Options for isHostname() that specify which types of hostnames
 * to allow.
 *
 * HOST_ALLOW_IP:    Allows IP addresses.
 */
define('ISPK_HOST_ALLOW_IP', 2);

/**
 * Options for isHostname() that specify which types of hostnames
 * to allow.
 *
 * HOST_ALLOW_LOCAL: Allows local network names (e.g., localhost,
 *                   www.localdomain) and Internet domain names.
 */
define('ISPK_HOST_ALLOW_LOCAL', 4);

/**
 * Options for isHostname() that specify which types of hostnames
 * to allow.
 *
 * HOST_ALLOW_ALL:   Allows all of the above types of hostnames.
 */
define('ISPK_HOST_ALLOW_ALL', 7);

/**
 * Options for isUri that specify which types of URIs to allow.
 *
 * URI_ALLOW_COMMON: Allow only "common" hostnames: http, https, ftp
 */
define('ISPK_URI_ALLOW_COMMON', 1);

/**
 * @package    Inspekt
 */
class Inspekt
{
    protected static $useFilterExtension = true;

    /**
     * regex used to define what we're calling a valid domain name
     */
    const VALID_DNS_REGEX = '/^(?:[^\W_]((?:[^\W_]|-){0,61}[^\W_])?\.)+[a-zA-Z]{2,6}\.?$/';
    /**
     * regex used to define what we're calling a valid email
     *
     * we're taking a "match 99%" approach here, rather than a strict
     * interpretation of the RFC.
     *
     * @see http://www.regular-expressions.info/email.html
     */
    const VALID_EMAIL_REGEX = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/';
    /**
     * regex used to validate a US postal (zip) code, ZIP or ZIP+4 allowed
     */
    const VALID_POSTAL_CODE_REGEX = '/(^\d{5}$)|(^\d{5}-\d{4}$)/';
    /**
     * Returns the $_SERVER data wrapped in an Inspekt_Cage object
     *
     * This utilizes a singleton pattern to get around scoping issues
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal array
     * @return Inspekt_Cage
     *
     * @assert()
     */
    static public function makeServerCage($config_file = null, $strict = true)
    {
        /**
         * @staticvar $_instance
         */
        static $_instance;

        if (!isset($_instance)) {
            $_instance = Inspekt_Cage::Factory($_SERVER, $config_file, '_SERVER', $strict);
        }
        $GLOBALS['HTTP_SERVER_VARS'] = null;
        return $_instance;
    }

    /**
     * Returns the $_GET data wrapped in an Inspekt_Cage object
     *
     * This utilizes a singleton pattern to get around scoping issues
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal array
     * @return Inspekt_Cage
     */
    static public function makeGetCage($config_file = null, $strict = true)
    {
        /**
         * @staticvar $_instance
         */
        static $_instance;

        if (!isset($_instance)) {
            $_instance = Inspekt_Cage::Factory($_GET, $config_file, '_GET', $strict);
        }
        $GLOBALS['HTTP_GET_VARS'] = null;
        return $_instance;
    }

    /**
     * Returns the $_POST data wrapped in an Inspekt_Cage object
     *
     * This utilizes a singleton pattern to get around scoping issues
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal array
     * @return Inspekt_Cage
     */
    static public function makePostCage($config_file = null, $strict = true)
    {
        /**
         * @staticvar $_instance
         */
        static $_instance;

        if (!isset($_instance)) {
            $_instance = Inspekt_Cage::Factory($_POST, $config_file, '_POST', $strict);
        }
        $GLOBALS['HTTP_POST_VARS'] = null;
        return $_instance;
    }

    /**
     * Returns the $_COOKIE data wrapped in an Inspekt_Cage object
     *
     * This utilizes a singleton pattern to get around scoping issues
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal array
     * @return Inspekt_Cage
     */
    static public function makeCookieCage($config_file = null, $strict = true)
    {
        /**
         * @staticvar $_instance
         */
        static $_instance;

        if (!isset($_instance)) {
            $_instance = Inspekt_Cage::Factory($_COOKIE, $config_file, '_COOKIE', $strict);
        }
        $GLOBALS['HTTP_COOKIE_VARS'] = null;
        return $_instance;
    }

    /**
     * Returns the $_ENV data wrapped in an Inspekt_Cage object
     *
     * This utilizes a singleton pattern to get around scoping issues
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal array
     * @return Inspekt_Cage
     */
    static public function makeEnvCage($config_file = null, $strict = true)
    {
        /**
         * @staticvar $_instance
         */
        static $_instance;

        if (!isset($_instance)) {
            $_instance = Inspekt_Cage::Factory($_ENV, $config_file, '_ENV', $strict);
        }
        $GLOBALS['HTTP_ENV_VARS'] = null;
        return $_instance;
    }

    /**
     * Returns the $_FILES data wrapped in an Inspekt_Cage object
     *
     * This utilizes a singleton pattern to get around scoping issues
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal array
     * @return Inspekt_Cage
     */
    static public function makeFilesCage($config_file = null, $strict = true)
    {
        /**
         * @staticvar $_instance
         */
        static $_instance;

        if (!isset($_instance)) {
            $_instance = Inspekt_Cage::Factory($_FILES, $config_file, '_FILES', $strict);
        }
        $GLOBALS['HTTP_POST_FILES'] = null;
        return $_instance;
    }

    /**
     * Returns the $_SESSION data wrapped in an Inspekt_Cage object
     *
     * This utilizes a singleton pattern to get around scoping issues
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal array
     * @return Inspekt_Cage
     * @deprecated
     */
    static public function makeSessionCage($config_file = null, $strict = true)
    {
        Inspekt_Error::raiseError('makeSessionCage is disabled in this version', E_USER_ERROR);

        /**
         * @staticvar $_instance
         */
        static $_instance;

        if (!isset($_SESSION)) {
            return null;
        }

        if (!isset($_instance)) {
            $_instance = Inspekt_Cage_Session::Factory($_SESSION, $config_file, '_SESSION', $strict);
        }
        $GLOBALS['HTTP_SESSION_VARS'] = null;
        return $_instance;
    }

    /**
     * Returns a Supercage object, which wraps ALL input superglobals
     *
     * @param string  $config_file
     * @param boolean $strict whether or not to nullify the superglobal
     * @return Inspekt_Supercage
     */
    static public function makeSuperCage($config_file = null, $strict = true)
    {
        /**
         * @staticvar $_instance
         */
        static $_scinstance;

        if (!isset($_scinstance)) {
            $_scinstance = Inspekt_Supercage::Factory($config_file, $strict);
        }
        return $_scinstance;
    }

    /**
     * Sets and/or retrieves whether we should use the PHP filter extensions where possible
     * If a param is passed, it will set the state in addition to returning it
     *
     * We use this method of storing in a static class property so that we can access the value outside of class instances
     *
     * @param boolean $state optional
     * @return boolean
     */
    static public function useFilterExt($state = null)
    {
        if (isset($state)) {
            Inspekt::$useFilterExtension = (bool) $state;
        }
        return Inspekt::$useFilterExtension;
    }

    /**
     * Recursively walks an array and applies a given filter method to
     * every value in the array.
     *
     * This should be considered a "protected" method, and not be called
     * outside of the class
     *
     * @param array|ArrayObject $input
     * @param string $inspektor  The name of a static filtering method, like get* or no*
     * @return array
     */
    static protected function _walkArray($input, $method, $classname = null)
    {
        if (!isset($classname)) {
            $classname = __CLASS__;
        }

        if (!self::isArrayOrArrayObject($input) ) {
            Inspekt_Error::raiseError('$input must be an array or ArrayObject', E_USER_ERROR);
            return false;
        }

        if (!is_callable(array($classname, $method))) {
            Inspekt_Error::raiseError('Inspektor ' . $classname . '::' . $method . ' is invalid', E_USER_ERROR);
            return false;
        }

        foreach ($input as $key => $val) {
            if (is_array($val)) {
                $input[$key]=self::_walkArray($val, $method, $classname);
            } else {
                $val = call_user_func(array($classname, $method), $val);
                $input[$key] = $val;
            }
        }
        return $input;
    }

    /**
     * Checks to see if this is an ArrayObject
     * @param mixed
     * @return boolean
     * @deprecated
     * @link http://php.net/arrayobject
     */
    static public function isArrayObject($obj)
    {
        $is = false;
        //$is = (is_object($obj) && get_class($obj) === 'ArrayObject');
        $is = $obj instanceof ArrayObject;
        return $is;
    }

    /**
     * Checks to see if this is an array or an ArrayObject
     * @param mixed
     * @return boolean
     * @link http://php.net/arrayobject
     * @link http://php.net/array
     */
    static public function isArrayOrArrayObject($arr)
    {
        $is = false;
        $is = $arr instanceof ArrayObject || is_array($arr);
        return $is;
    }

    /**
     * Converts an array into an ArrayObject. We use ArrayObjects when walking arrays in Inspekt
     * @param array
     * @return ArrayObject
     */
    static public function convertArrayToArrayObject(&$arr)
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = new ArrayObject($value);
                $arr[$key] = $value;
                //echo $key." is an array\n";
                Inspekt::convertArrayToArrayObject($arr[$key]);
            }
        }

        return new ArrayObject($arr);
    }

    /**
     * Returns only the alphabetic characters in value.
     *
     * @param mixed $value
     * @return mixed
     *
     * @tag filter
     */
    static public function getAlpha($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'getAlpha');
        } else {
            return preg_replace('/[^[:alpha:]]/', '', $value);
        }
    }

    /**
     * Returns only the alphabetic characters and digits in value.
     *
     * @param mixed $value
     * @return mixed
     *
     * @tag filter
     *
     * @assert('1)@*(&UR)HQ)W(*(HG))') === '1URHQWHG'
     */
    static public function getAlnum($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'getAlnum');
        } else {
            return preg_replace('/[^[:alnum:]]/', '', $value);
        }
    }

    /**
     * Returns only the digits in value.
     *
     * @param mixed $value
     * @return mixed
     *
     * @tag filter
     *
     * @assert('1)@*(&UR)HQ)56W(*(HG))') === '156'
     */
    static public function getDigits($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'getDigits');
        } else {
            return preg_replace('/[^[:digit:]]/', '', $value);
        }
    }

    /**
     * Returns dirname(value).
     *
     * @param mixed $value
     * @return mixed
     *
     * @tag filter
     *
     * @assert('/usr/lib/php/Pear.php') === '/usr/lib/php'
     */
    static public function getDir($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'getDir');
        } else {
            return dirname($value);
        }
    }

    /**
     * Returns (int) value.
     *
     * @param mixed $value
     * @return int
     *
     * @tag filter
     *
     * @assert('1)45@*(&UR)HQ)W.0000(*(HG))') === 1
     * @assert('A1)45@*(&UR)HQ)W.0000(*(HG))') === 0
     */
    static public function getInt($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'getInt');
        } else {
            return (int) $value;
        }
    }

    /**
     * Returns realpath(value).
     *
     * @param mixed $value
     * @return mixed
     *
     * @tag filter
     */
    static public function getPath($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'getPath');
        } else {
            return realpath($value);
        }
    }

    /**
     * Returns the value encoded as ROT13 (or decoded, if already was ROT13)
     *
     * @param mixed $value
     * @return mixed
     *
     * @link http://php.net/manual/en/function.str-rot13.php
     */
    static public function getROT13($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'getROT13');
        } else {
            return str_rot13($value);
        }
    }

    /**
     * Returns true if every character is alphabetic or a digit,
     * false otherwise.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     *
     * @assert('NCOFWIERNVOWIEBHV12047057y0650ytg0314') === true
     * @assert('NCOFWIERNVOWIEBHV2@12047057y0650ytg0314') === false
     * @assert('funkatron') === true
     * @assert('funkatron_user') === false
     * @assert('funkatron-user') === false
     * @assert('_funkatronuser') === false
     */
    static public function isAlnum($value)
    {
        return ctype_alnum($value);
    }

    /**
     * Returns true if every character is alphabetic, false
     * otherwise.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     *
     * @assert('NCOFWIERNVOWIEBHV12047057y0650ytg0314') === false
     * @assert('NCOFWIERNVOWIEBHV2@12047057y0650ytg0314') === false
     * @assert('funkatron') === true
     * @assert('funkatron_user') === false
     * @assert('funkatron-user') === false
     * @assert('_funkatronuser') === false
     */
    static public function isAlpha($value)
    {
        return ctype_alpha($value);
    }

    /**
     * Returns true if value is greater than or equal to $min and less
     * than or equal to $max, false otherwise. If $inc is set to
     * false, then the value must be strictly greater than $min and
     * strictly less than $max.
     *
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @return boolean
     *
     * @tag validator
     *
     * @assert(12, 0, 12) === true
     * @assert(12, 0, 12, false) === false
     * @assert('f', 'a', 'm', false) === true
     * @assert('p', 'a', 'm', false) === false
     */
    static public function isBetween($value, $min, $max, $inc = true)
    {
        if ($value > $min &&
            $value < $max) {
            return true;
        }

        if ($inc &&
            $value >= $min &&
            $value <= $max) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if it is a valid credit card number format. The
     * optional second argument allows developers to indicate the
     * type.
     *
     * @param mixed $value
     * @param mixed $type
     * @return boolean
     *
     * @tag validator
     */
    static public function isCcnum($value, $type = null)
    {
        /**
         * @todo Type-specific checks
         */
        if (isset($type)) {
            Inspekt_Error::raiseError('Type-specific cc checks are not yet supported');
        }

        $value = self::getDigits($value);
        $length = strlen($value);

        if ($length < 13 || $length > 19) {
            return false;
        }

        $sum = 0;
        $weight = 2;

        for ($i = $length - 2; $i >= 0; $i--) {
            $digit = $weight * $value[$i];
            $sum += floor($digit / 10) + $digit % 10;
            $weight = $weight % 2 + 1;
        }

        $mod = (10 - $sum % 10) % 10;

        return ($mod == $value[$length - 1]);
    }

    /**
     * Returns true if value is a valid date, false otherwise. The
     * date is required to be in ISO 8601 format.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     *
     * @assert('2009-06-30') === true
     * @assert('2009-06-31') === false
     * @assert('2009-6-30') === true
     * @assert('2-6-30') === true
     */
    static public function isDate($value)
    {
        list($year, $month, $day) = sscanf($value, '%d-%d-%d');

        return checkdate($month, $day, $year);
    }

    /**
     * Returns true if every character is a digit, false otherwise.
     * This is just like isInt(), except there is no upper limit.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     *
     * @assert('1029438750192730t91740987023948') === false
     * @assert('102943875019273091740987023948') === true
     * @assert(102943875019273091740987023948) === false
     */
    static public function isDigits($value)
    {
        return ctype_digit((string) $value);
    }

    /**
     * Returns true if value is a valid email format, false otherwise.
     *
     * @param string $value
     * @return boolean
     * @see http://www.regular-expressions.info/email.html
     * @see Inspekt::VALID_EMAIL_REGEX
     *
     * @tag validator
     *
     * @assert('coj@poop.com') === true
     * @assert('coj+booboo@poop.com') === true
     * @assert('coj!booboo@poop.com') === false
     * @assert('@poop.com') === false
     * @assert('a@b') === false
     * @assert('webmaster') === false
     */
    static public function isEmail($value)
    {
        return (bool) preg_match(self::VALID_EMAIL_REGEX, $value);
    }

    /**
     * Returns true if value is a valid float value, false otherwise.
     *
     * @param string $value
     * @return boolean
     *
     * @assert(10244578109.234451) === true
     * @assert('10244578109.234451') === false
     * @assert('10,244,578,109.234451') === false
     *
     * @tag validator
     */
    static public function isFloat($value)
    {
        $locale = localeconv();
        $value = str_replace($locale['decimal_point'], '.', $value);
        $value = str_replace($locale['thousands_sep'], '', $value);

        return (strval(floatval($value)) == $value);
    }

    /**
     * Returns true if value is greater than $min, false otherwise. Note that
     * comparisons with NULL do not work the same way as SQL, ex. "1 > null" is
     * true
     *
     * @param mixed $value
     * @param mixed $min
     * @return boolean
     *
     * @tag validator
     *
     * @assert(5, 0) === true
     * @assert(2, 10) === false
     * @assert('b', 'a') === true
     * @assert('a', 'b') === false
     *
     * @todo missing $min is a really bad idea considering the odd null behavior. should that throw an error?
     */
    static public function isGreaterThan($value, $min)
    {
        return ($value > $min);
    }

    /**
     * Returns true if value is a valid hexadecimal format, false
     * otherwise.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     *
     * @assert('6F') === true
     * @assert('F6') === true
     *
     */
    static public function isHex($value)
    {
        return ctype_xdigit($value);
    }

    /**
     * Returns true if value is a valid hostname, false otherwise.
     * Depending upon the value of $allow, Internet domain names, IP
     * addresses, and/or local network names are considered valid.
     * The default is HOST_ALLOW_ALL, which considers all of the
     * above to be valid.
     *
     * @param mixed $value
     * @param integer $allow bitfield for ISPK_HOST_ALLOW_DNS, ISPK_HOST_ALLOW_IP, ISPK_HOST_ALLOW_LOCAL
     * @return boolean
     *
     * @tag validator
     */
    static public function isHostname($value, $allow = ISPK_HOST_ALLOW_ALL)
    {
        if (!is_numeric($allow) || !is_int($allow)) {
            Inspekt_Error::raiseError('Illegal value for $allow; expected an integer', E_USER_WARNING);
        }

        if ($allow < ISPK_HOST_ALLOW_DNS || ISPK_HOST_ALLOW_ALL < $allow) {
            Inspekt_Error::raiseError('Illegal value for $allow; expected integer between ' . ISPK_HOST_ALLOW_DNS . ' and ' . ISPK_HOST_ALLOW_ALL, E_USER_WARNING);
        }

        // determine whether the input is formed as an IP address
        $status = self::isIp($value);

        // if the input looks like an IP address
        if ($status) {
            // if IP addresses are not allowed, then fail validation
            if (($allow & ISPK_HOST_ALLOW_IP) == 0) {
                return false;
            }

            // IP passed validation
            return true;
        }

        // check input against domain name schema
        $status = @preg_match(ISPK_DNS_VALID, $value);
        if ($status === false) {
            Inspekt_Error::raiseError('Internal error: DNS validation failed', E_USER_WARNING);
        }

        // if the input passes as an Internet domain name, and domain names are allowed, then the hostname
        // passes validation
        if ($status == 1 && ($allow & ISPK_HOST_ALLOW_DNS) != 0) {
            return true;
        }

        // if local network names are not allowed, then fail validation
        if (($allow & ISPK_HOST_ALLOW_LOCAL) == 0) {
            return false;
        }

        // check input against local network name schema; last chance to pass validation
        $status = @preg_match('/^(?:[^\W_](?:[^\W_]|-){0,61}[^\W_]\.)*(?:[^\W_](?:[^\W_]|-){0,61}[^\W_])\.?$/',
            $value);
        if ($status === false) {
            Inspekt_Error::raiseError('Internal error: local network name validation failed', E_USER_WARNING);
        }

        if ($status == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns true if value is a valid integer value, false otherwise.
     *
     * @param string|array $value
     * @return boolean
     *
     * @tag validator
     *
     * @todo better handling of diffs b/t 32-bit and 64-bit
     */
    static public function isInt($value)
    {
        $locale = localeconv();

        $value = str_replace($locale['decimal_point'], '.', $value);
        $value = str_replace($locale['thousands_sep'], '', $value);

        $is_valid = (
            is_numeric($value)  // Must be able to be converted to a number
                && preg_replace("/^-?([0-9]+)$/", "", $value) == ""  // Must be an integer (no floats or e-powers)
                && bccomp($value, "-9223372036854775807") >= 0  // Must be greater than than min of 64-bit
                && bccomp($value, "9223372036854775807") <= 0  // Must be less than max of 64-bit
        );
        if (!$is_valid) {
            return false;
        } else {
            return true;
        }
        // return (strval(intval($value)) === $value);
    }

    /**
     * Returns true if value is a valid IP format, false otherwise.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     */
    static public function isIp($value)
    {
        return (bool) ip2long($value);
    }

    /**
     * Returns true if value is less than $max, false otherwise.
     *
     * @param mixed $value
     * @param mixed $max
     * @return boolean
     *
     * @tag validator
     */
    static public function isLessThan($value, $max)
    {
        return ($value < $max);
    }

    /**
     * Returns true if value is one of $allowed, false otherwise.
     *
     * @param mixed $value
     * @param array|string $allowed
     * @return boolean
     *
     * @tag validator
     */
    static public function isOneOf($value, $allowed)
    {
        /**
         * @todo: Consider allowing a string for $allowed, where each
         * character in the string is an allowed character in the
         * value.
         */

        if (is_string($allowed)) {
            $allowed = str_split($allowed);
        }

        return in_array($value, $allowed);
    }

    /**
     * Returns true if value is a valid phone number format, false
     * otherwise. The optional second argument indicates the country.
     * This method requires that the value consist of only digits.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     */
    static public function isPhone($value, $country = 'US')
    {
        if (!ctype_digit($value)) {
            return false;
        }

        switch ($country) {
            case 'US':
                if (strlen($value) != 10) {
                    return false;
                }

                $areaCode = substr($value, 0, 3);

                $areaCodes = array(201, 202, 203, 204, 205, 206, 207, 208,
                    209, 210, 212, 213, 214, 215, 216, 217,
                    218, 219, 224, 225, 226, 228, 229, 231,
                    234, 239, 240, 242, 246, 248, 250, 251,
                    252, 253, 254, 256, 260, 262, 264, 267,
                    268, 269, 270, 276, 281, 284, 289, 301,
                    302, 303, 304, 305, 306, 307, 308, 309,
                    310, 312, 313, 314, 315, 316, 317, 318,
                    319, 320, 321, 323, 325, 330, 334, 336,
                    337, 339, 340, 345, 347, 351, 352, 360,
                    361, 386, 401, 402, 403, 404, 405, 406,
                    407, 408, 409, 410, 412, 413, 414, 415,
                    416, 417, 418, 419, 423, 424, 425, 430,
                    432, 434, 435, 438, 440, 441, 443, 445,
                    450, 469, 470, 473, 475, 478, 479, 480,
                    484, 501, 502, 503, 504, 505, 506, 507,
                    508, 509, 510, 512, 513, 514, 515, 516,
                    517, 518, 519, 520, 530, 540, 541, 555,
                    559, 561, 562, 563, 564, 567, 570, 571,
                    573, 574, 580, 585, 586, 600, 601, 602,
                    603, 604, 605, 606, 607, 608, 609, 610,
                    612, 613, 614, 615, 616, 617, 618, 619,
                    620, 623, 626, 630, 631, 636, 641, 646,
                    647, 649, 650, 651, 660, 661, 662, 664,
                    670, 671, 678, 682, 684, 700, 701, 702,
                    703, 704, 705, 706, 707, 708, 709, 710,
                    712, 713, 714, 715, 716, 717, 718, 719,
                    720, 724, 727, 731, 732, 734, 740, 754,
                    757, 758, 760, 763, 765, 767, 769, 770,
                    772, 773, 774, 775, 778, 780, 781, 784,
                    785, 786, 787, 800, 801, 802, 803, 804,
                    805, 806, 807, 808, 809, 810, 812, 813,
                    814, 815, 816, 817, 818, 819, 822, 828,
                    829, 830, 831, 832, 833, 835, 843, 844,
                    845, 847, 848, 850, 855, 856, 857, 858,
                    859, 860, 863, 864, 865, 866, 867, 868,
                    869, 870, 876, 877, 878, 888, 900, 901,
                    902, 903, 904, 905, 906, 907, 908, 909,
                    910, 912, 913, 914, 915, 916, 917, 918,
                    919, 920, 925, 928, 931, 936, 937, 939,
                    940, 941, 947, 949, 951, 952, 954, 956,
                    959, 970, 971, 972, 973, 978, 979, 980,
                    985, 989);

                return in_array($areaCode, $areaCodes);
                break;
            default:
                Inspekt_Error::raiseError('isPhone() does not yet support this country.', E_USER_WARNING);
                return false;
                break;
        }
    }

    /**
     * Returns true if value matches $pattern, false otherwise. Uses
     * preg_match() for the matching.
     *
     * @param mixed $value
     * @param mixed $pattern
     * @return mixed
     *
     * @tag validator
     */
    static public function isRegex($value, $pattern)
    {
        return (bool) preg_match($pattern, $value);
    }

    /**
     * Enter description here...
     *
     * @param string $value
     * @param integer $mode
     * @return boolean
     *
     * @link http://www.ietf.org/rfc/rfc2396.txt
     *
     * @tag validator
     */
    static public function isUri($value, $mode = ISPK_URI_ALLOW_COMMON)
    {
        /**
         * @todo
         */
        $regex = '';
        switch ($mode) {

            // a common absolute URI: ftp, http or https
            case ISPK_URI_ALLOW_COMMON:

                $regex .= '&';
                $regex .= '^(ftp|http|https):';					// protocol
                $regex .= '(//)';								// authority-start
                $regex .= '([-a-z0-9/~;:@=+$,.!*()\']+@)?';		// userinfo
                $regex .= '(';
                $regex .= '((?:[^\W_]((?:[^\W_]|-){0,61}[^\W_])?\.)+[a-zA-Z]{2,6}\.?)';		// domain name
                $regex .= '|';
                $regex .= '([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?(\.[0-9]{1,3})?)';	// OR ipv4
                $regex .= ')';
                $regex .= '(:([0-9]*))?';						// port
                $regex .= '(/((%[0-9a-f]{2}|[-_a-z0-9/~;:@=+$,.!*()\'\&]*)*)/?)?';	// path
                $regex .= '(\?[^#]*)?';							// query
                $regex .= '(#([-a-z0-9_]*))?';					// anchor (fragment)
                $regex .= '$&i';
                //echo "<pre>"; echo print_r($regex, true); echo "</pre>\n";

                break;

            case ISPK_URI_ALLOW_ABSOLUTE:

                Inspekt_Error::raiseError('isUri() for ISPK_URI_ALLOW_ABSOLUTE has not been implemented.', E_USER_WARNING);
                return false;

//				$regex .= '&';
//				$regex .= '^(ftp|http|https):';					// protocol
//				$regex .= '(//)';								// authority-start
//				$regex .= '([-a-z0-9/~;:@=+$,.!*()\']+@)?';		// userinfo
//				$regex .= '(';
//					$regex .= '((?:[^\W_]((?:[^\W_]|-){0,61}[^\W_])?\.)+[a-zA-Z]{2,6}\.?)';		// domain name
//				$regex .= '|';
//					$regex .= '([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?(\.[0-9]{1,3})?)';	// OR ipv4
//				$regex .= ')';
//				$regex .= '(:([0-9]*))?';						// port
//				$regex .= '(/((%[0-9a-f]{2}|[-a-z0-9/~;:@=+$,.!*()\'\&]*)*)/?)?';	// path
//				$regex .= '(\?[^#]*)?';							// query
//				$regex .= '(#([-a-z0-9_]*))?';					// anchor (fragment)
//				$regex .= '$&i';
                //echo "<pre>"; echo print_r($regex, true); echo "</pre>\n";

                break;

        }
        $result = preg_match($regex, $value);

        if ($result === 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if value is a valid US postal (zip) code, false otherwise.
     *
     * @param mixed $value
     * @return boolean
     *
     * @tag validator
     */
    static public function isZip($value)
    {
        return (bool) preg_match(self::VALID_POSTAL_CODE_REGEX, $value);
    }

    /**
     * Returns value with all tags removed.
     *
     * This will utilize the PHP Filter extension if available
     *
     * @param mixed $value
     * @return mixed
     *
     * @tag filter
     */
    static public function noTags($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'noTags');
        } else {
            if (Inspekt::useFilterExt()) {
                return filter_var($value, FILTER_SANITIZE_STRING);
            } else {
                return strip_tags($value);
            }
        }
    }

    /**
     * returns value with tags stripped and the chars '"&<> and all ascii chars 
     * under 32 encoded as html entities
     *
     * This will utilize the PHP Filter extension if available
     *
     * @param mixed $value
     * @return @mixed
     *
     * @tag filter
     *
     */
    static public function noTagsOrSpecial($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'noTagsOrSpecial');
        } else {
            if (Inspekt::useFilterExt()) {
                $newval = filter_var($value, FILTER_SANITIZE_STRING);
                $newval = filter_var($newval, FILTER_SANITIZE_SPECIAL_CHARS);
                return $newval;
            } else {
                $newval = strip_tags($value);
                //for sake of simplicity and safety we assume UTF-8
                $newval = htmlspecialchars($newval, ENT_QUOTES, 'UTF-8'); 

                /*
				 *	convert low ascii chars to entities
                 */
                $newval = str_split($newval);
                for ($i=0; $i < count($newval); $i++) {
                    $ascii_code = ord($newval[$i]);
                    if ($ascii_code < 32) {
                        $newval[$i] = "&#{$ascii_code};";
                    }
                }
                $newval = implode($newval);

                return $newval;
            }
        }
    }

    /**
     * Returns basename(value).
     *
     * @param mixed $value
     * @return mixed
     *
     * @tag filter
     */
    static public function noPath($value)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'noPath');
        } else {
            return basename($value);
        }
    }

    /**
     * Escapes the value given with mysql_real_escape_string
     *
     * @param mixed $value
     * @param resource $conn the mysql connection. If none is given, it will use
     *                       the last link opened, per behavior of mysql_real_escape_string
     * @return mixed
     *
     * @link http://php.net/manual/en/function.mysql-real-escape-string.php
     *
     * @tag filter
     */
    static public function escMySQL($value, $conn = null)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'escMySQL');
        } else {
            //no explicit func to check if the connection is live, but if it's not $conn would be false
            if (isset($conn) && is_resource($conn)) {
                return mysql_real_escape_string($value, $conn);
            } else {
                return mysql_real_escape_string($value);
            }
        }
    }

    /**
     * Escapes the value given with pg_escape_string
     *
     * If the data is for a column of the type bytea, use Inspekt::escPgSQLBytea()
     *
     * @param mixed $value
     * @param resource $conn the postgresql connection. If none is given, it 
     *                       will use the last link opened, per behavior of pg_escape_string
     * @return mixed
     *
     * @link http://php.net/manual/en/function.pg-escape-string.php
     */
    static public function escPgSQL($value, $conn = null)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'escPgSQL');
        } else {
            //might also check is_resource if pg_connection_status is too much
            if (isset($conn) && pg_connection_status($conn) === PGSQL_CONNECTION_OK) {
                return pg_escape_string($conn, $value);
            } else {
                return pg_escape_string($value);
            }
        }
    }

    /**
     * Escapes the value given with pg_escape_bytea
     *
     * @param mixed $value
     * @param resource $conn the postgresql connection. If none is given, it 
     *                       will use the last link opened, per behavior of pg_escape_bytea
     * @return mixed
     *
     * @link http://php.net/manual/en/function.pg-escape-bytea.php
     */
    static public function escPgSQLBytea($value, $conn = null)
    {
        if (Inspekt::isArrayOrArrayObject($value)) {
            return Inspekt::_walkArray($value, 'escPgSQL');
        } else {
            //might also check is_resource if pg_connection_status is too much
            if (isset($conn) && pg_connection_status($conn) === PGSQL_CONNECTION_OK) {
                return pg_escape_bytea($conn, $value);
            } else {
                return pg_escape_bytea($value);
            }
        }
    }
}
