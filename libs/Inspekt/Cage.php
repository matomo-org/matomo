<?php
/**
 * Inspekt Cage - main source file
 *
 * @author Chris Shiflett <chris@shiflett.org>
 * @author Ed Finkler <coj@funkatron.com>
 *
 * @package Inspekt
 */

/**
 * require main Inspekt file
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Inspekt.php';

define('ISPK_ARRAY_PATH_SEPARATOR', '/');

define('ISPK_RECURSION_MAX', 15);

/**
 * @package Inspekt
 */
class Inspekt_Cage implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * {@internal The raw source data.  Although tempting, NEVER EVER
     * EVER access the data directly using this property!}}
     *
     * Don't try to access this.  ever.  Now that we're safely on PHP5, we'll
     * enforce this with the "protected" keyword.
     *
     * @var array
     */
    protected $_source = null;

    /**
     * where we store user-defined methods
     *
     * @var array
     */
    public $_user_accessors = array();

    /**
     * the holding property for autofilter config
     *
     * @var array
     */
    public $_autofilter_conf = null;

    /**
     *
     * @var HTMLPurifer
     */
    protected $_purifier = null;

    /**
     * Takes an array and wraps it inside an object. If $strict is not set to
     * false, the original array will be destroyed, and the data can only be
     * accessed via the object's accessor methods
     *
     * @param array $source
     * @param string $conf_file
     * @param string $conf_section
     * @param boolean $strict
     * @return Inspekt_Cage
     */
    static public function Factory(&$source, $conf_file = null, $conf_section = null, $strict = true)
    {
        if (!is_array($source)) {
            Inspekt_Error::raiseError('$source '.$source.' is not an array', E_USER_WARNING);
        }

        $cage = new Inspekt_Cage();
        $cage->_setSource($source);
        $cage->_parseAndApplyAutoFilters($conf_file, $conf_section);

        if ($strict) {
            $source = null;
        }

        return $cage;
    }

    /**
     * {@internal we use this to set the data array in Factory()}}
     *
     * @see Factory()
     * @param array $newsource
     */
    private function _setSource(&$newsource)
    {
        $this->_source = Inspekt::convertArrayToArrayObject($newsource);
    }

    /**
     * Returns an iterator for looping through an ArrayObject.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->_source->getIterator();
    }


    /**
     * Sets the value at the specified $offset to value$ in $this->_source.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_source->offsetSet($offset, $value);
    }

    /**
     * Returns whether the $offset exists in $this->_source.
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->_source->offsetExists($offset);
    }

    /**
     * Unsets the value in $this->_source at $offset.
     *
     * @param mixed $offset
     * @access public
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->_source->offsetUnset($offset);
    }

    /**
     * Returns the value at $offset from $this->_source.
     *
     * @param mixed $offset
     * @access public
     * @return void
     */
    public function offsetGet($offset)
    {
        return $this->_source->offsetGet($offset);
    }

    /**
     * Returns the number of elements in $this->_source.
     *
     * @access public
     * @return int
     */
    public function count()
    {
        return $this->_source->count();
    }

    /**
     * Load the HTMLPurifier library and instantiate the object
     * @param string $path the full path to the HTMLPurifier.auto.php base file.
     *                     Optional if HTMLPurifier is already in your include_path
     */
    public function loadHTMLPurifier($path = null, $opts = null)
    {
        if (!class_exists('HTMLPurifier')) {
            if (isset($path)) {
                include_once $path;
            } else {
                include_once 'HTMLPurifier.auto.php';
            }
	}

        $config = null;
        if (isset($opts) && is_array($opts)) {
            $config = $this->_buildHTMLPurifierConfig($opts);
        }

        $this->_purifier = new HTMLPurifier($config);
    }

    /**
     *
     * @param HTMLPurifier $pobj an HTMLPurifier Object
     */
    public function setHTMLPurifier(HTMLPurifier $pobj)
    {
        $this->_purifier = $pobj;
    }

    /**
     * @return HTMLPurifier
     */
    public function getHTMLPurifier()
    {
        return $this->_purifier;
    }

    protected function _buildHTMLPurifierConfig($opts)
    {
        $config = HTMLPurifier_Config::createDefault();
        foreach ($opts as $key => $val) {
            $config->set($key, $val);
        }
        return $config;
    }

    protected function _parseAndApplyAutoFilters($conf_file, $conf_section)
    {
        if (isset($conf_file)) {
            $conf = parse_ini_file($conf_file, true);
            if ($conf_section) {
                if (isset($conf[$conf_section])) {
                    $this->_autofilter_conf = $conf[$conf_section];
                }
            } else {
                $this->_autofilter_conf = $conf;
            }
            $this->_applyAutoFilters();
        }
    }

    protected function _applyAutoFilters()
    {
        if (isset($this->_autofilter_conf) && is_array($this->_autofilter_conf)) {
            foreach ($this->_autofilter_conf as $key => $filters) {
                // get universal filter key
                if ($key == '*') {
                    // get filters for this key
                    $uni_filters = explode(',', $this->_autofilter_conf[$key]);
                    array_walk($uni_filters, 'trim');

                    // apply uni filters
                    foreach($uni_filters as $this_filter) {
                        foreach ($this->_source as $key => $val) {
                            $this->_source[$key] = $this->$this_filter($key);
                        }
                    }
                    //echo "<pre>UNI FILTERS"; echo var_dump($this->_source); echo "</pre>\n";
                } else if ($val == $this->keyExists($key)) {
                    // get filters for this key
                    $filters = explode(',', $this->_autofilter_conf[$key]);
                    array_walk($filters, 'trim');

                    // apply filters
                    foreach ($filters as $this_filter) {
                        $this->_setValue($key, $this->$this_filter($key));
                    }
                    //echo "<pre> Filter $this_filter/$key: "; echo var_dump($this->_source); echo "</pre>\n";
                }
            }
        }
    }

    public function __call($name, $args)
    {
        if (in_array($name, $this->_user_accessors) ) {
            $acc = new $name($this, $args);
            /*
				this first argument should always be the key we're accessing
            */
            return $acc->run($args[0]);
        } else {
            Inspekt_Error::raiseError("The accessor $name does not exist and is not registered", E_USER_ERROR);
            return false;
        }
    }

    /**
     * This method lets the developer add new accessor methods to a cage object
     * Note that calling these will be quite a bit slower, because we have to
     * use call_user_func()
     *
     * The dev needs to define a procedural function like so:
     *
     * <code>
     * function foo_bar($cage_object, $arg2, $arg3, $arg4, $arg5...) {
     *    ...
     * }
     * </code>
     *
     * @param string $method_name
     * @return void
     * @author Ed Finkler
     */
    public function addAccessor($accessor_name)
    {
        $this->_user_accessors[] = $accessor_name;
    }

    /**
     * Returns only the alphabetic characters in value.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag filter
     */
    public function getAlpha($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::getAlpha($this->_getValue($key));
    }

    /**
     * Returns only the alphabetic characters and digits in value.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag filter
     */
    public function getAlnum($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::getAlnum($this->_getValue($key));
    }

    /**
     * Returns only the digits in value. This differs from getInt().
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag filter
     */
    public function getDigits($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::getDigits($this->_getValue($key));
    }

    /**
     * Returns dirname(value).
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag filter
     */
    public function getDir($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::getDir($this->_getValue($key));
    }

    /**
     * Returns (int) value.
     *
     * @param mixed $key
     * @return int
     *
     * @tag filter
     */
    public function getInt($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::getInt($this->_getValue($key));
    }

    /**
     * Returns realpath(value).
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag filter
     */
    public function getPath($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::getPath($this->_getValue($key));
    }

    /**
     * Returns ROT13-encoded version
     *
     * @param string $key
     * @return mixed
     * @tag hash
     */
    public function getROT13($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::getROT13($this->_getValue($key));
    }

    /**
     * This returns the value of the given key passed through the HTMLPurifer
     * object, if it is instantiated with Inspekt_Cage::loadHTMLPurifer
     *
     * @param string $key
     * @return mixed purified HTML version of input
     * @tag filter
     */
    public function getPurifiedHTML($key)
    {
        if (!isset($this->_purifier)) {
            Inspekt_Error::raiseError("HTMLPurifier was not loaded", E_USER_WARNING);
            return false;
        }

        if (!$this->keyExists($key)) {
            return false;
        }
        $val = $this->_getValue($key);
        if (Inspekt::isArrayOrArrayObject($val)) {
            return $this->_purifier->purifyArray($val);
        } else {
            return $this->_purifier->purify($val);
        }
    }

    /**
     * Returns value.
     *
     * @param string $key
     * @return mixed
     *
     * @tag filter
     */
    public function getRaw($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return $this->_getValue($key);
    }

    /**
     * Returns value if every character is alphabetic or a digit,
     * false otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testAlnum($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isAlnum($this->_getValue($key))) {
            return $this->_getValue($key);
        }
        return false;
    }

    /**
     * Returns value if every character is alphabetic, false
     * otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testAlpha($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isAlpha($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is greater than or equal to $min and less
     * than or equal to $max, false otherwise. If $inc is set to
     * false, then the value must be strictly greater than $min and
     * strictly less than $max.
     *
     * @param mixed $key
     * @param mixed $min
     * @param mixed $max
     * @param boolean $inc
     * @return mixed
     *
     * @tag validator
     */
    public function testBetween($key, $min, $max, $inc = true)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isBetween($this->_getValue($key), $min, $max, $inc)) {
            return $this->_getValue($key);
        }
        return false;
    }

    /**
     * Returns value if it is a valid credit card number format. The
     * optional second argument allows developers to indicate the
     * type.
     *
     * @param mixed $key
     * @param mixed $type
     * @return mixed
     *
     * @tag validator
     */
    public function testCcnum($key, $type = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isCcnum($this->_getValue($key), $type)) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns $value if it is a valid date, false otherwise. The
     * date is required to be in ISO 8601 format.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testDate($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isDate($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if every character is a digit, false otherwise.
     * This is just like isInt(), except there is no upper limit.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testDigits($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isDigits($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid email format, false otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testEmail($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isEmail($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid float value, false otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testFloat($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isFloat($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is greater than $min, false otherwise.
     *
     * @param mixed $key
     * @param mixed $min
     * @return mixed
     *
     * @tag validator
     */
    public function testGreaterThan($key, $min = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isGreaterThan($this->_getValue($key), $min)) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid hexadecimal format, false
     * otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testHex($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isHex($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid hostname, false otherwise.
     * Depending upon the value of $allow, Internet domain names, IP
     * addresses, and/or local network names are considered valid.
     * The default is HOST_ALLOW_ALL, which considers all of the
     * above to be valid.
     *
     * @param mixed $key
     * @param integer $allow bitfield for HOST_ALLOW_DNS, HOST_ALLOW_IP, HOST_ALLOW_LOCAL
     * @return mixed
     *
     * @tag validator
     */
    public function testHostname($key, $allow = ISPK_HOST_ALLOW_ALL)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isHostname($this->_getValue($key), $allow)) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid integer value, false otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testInt($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isInt($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid IP format, false otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testIp($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isIp($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is less than $max, false otherwise.
     *
     * @param mixed $key
     * @param mixed $max
     * @return mixed
     *
     * @tag validator
     */
    public function testLessThan($key, $max = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isLessThan($this->_getValue($key), $max)) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is one of $allowed, false otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testOneOf($key, $allowed = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isOneOf($this->_getValue($key), $allowed)) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid phone number format, false
     * otherwise. The optional second argument indicates the country.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testPhone($key, $country = 'US')
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isPhone($this->_getValue($key), $country)) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it matches $pattern, false otherwise. Uses
     * preg_match() for the matching.
     *
     * @param mixed $key
     * @param mixed $pattern
     * @return mixed
     *
     * @tag validator
     */
    public function testRegex($key, $pattern = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isRegex($this->_getValue($key), $pattern)) {
            return $this->_getValue($key);
        }

        return false;
    }


    /**
     * Checks to see if the passed $key references a properly-formed URI
     *
     * @param string $key
     * @return string|false
     *
     * @tag validator
     */
    public function testUri($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isUri($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value if it is a valid US ZIP, false otherwise.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag validator
     */
    public function testZip($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (Inspekt::isZip($this->_getValue($key))) {
            return $this->_getValue($key);
        }

        return false;
    }

    /**
     * Returns value with all tags removed.
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag filter
     */
    public function noTags($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::noTags($this->_getValue($key));
    }

    /**
     * Returns basename(value).
     *
     * @param mixed $key
     * @return mixed
     *
     * @tag filter
     */
    public function noPath($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::noPath($this->_getValue($key));
    }


    public function noTagsOrSpecial($key)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        return Inspekt::noTagsOrSpecial($this->_getValue($key));
    }

    /**
     *
     * @param string $key
     * @param resource $conn
     * @return string|false
     *
     * @todo remove $conn check, redundant with Inspekt::escMySQL
     */
    public function escMySQL($key, $conn = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (isset($conn)) {
            return Inspekt::escMySQL($this->_getValue($key), $conn);
        } else {
            return Inspekt::escMySQL($this->_getValue($key));
        }
    }

    /**
     *
     * @param string $key
     * @param resource $conn
     * @return string|false
     *
     * @todo remove $conn check, redundant with Inspekt::escPgSQL
     */
    public function escPgSQL($key, $conn = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (isset($conn)) {
            return Inspekt::escPgSQL($this->_getValue($key), $conn);
        } else {
            return Inspekt::escPgSQL($this->_getValue($key));
        }

    }

    /**
     *
     * @param string $key
     * @param resource $conn
     * @return string|false
     *
     * @todo remove $conn check, redundant with Inspekt::escPgSQLBytea
     */
    public function escPgSQLBytea($key, $conn = null)
    {
        if (!$this->keyExists($key)) {
            return false;
        }
        if (isset($conn)) {
            return Inspekt::escPgSQLBytea($this->_getValue($key), $conn);
        } else {
            return Inspekt::escPgSQLBytea($this->_getValue($key));
        }

    }

    /**
     * Checks if a key exists
     *
     * @param mixed $key
     * @param boolean $return_value whether or not to return the value if key exists. defaults to false.
     * @return mixed
     */
    public function keyExists($key, $return_value = false)
    {
        if (strpos($key, ISPK_ARRAY_PATH_SEPARATOR) !== false) {
            $key = trim($key, ISPK_ARRAY_PATH_SEPARATOR);
            $keys = explode(ISPK_ARRAY_PATH_SEPARATOR, $key);
            return $this->_keyExistsRecursive($keys, $this->_source);
        } else {
            if (array_key_exists($key, $this->_source)) {
                return ($return_value) ? $this->_source[$key] : true;
            } else {
                return false;
            }
        }
    }

    protected function _keyExistsRecursive($keys, $data_array)
    {
        $thiskey = current($keys);

        if (is_numeric($thiskey)) { // force numeric strings to be integers
            $thiskey = (int)$thiskey;
        }

        if (array_key_exists($thiskey, $data_array)) {
            if (sizeof($keys) == 1) {
                return true;
            } else if ($data_array[$thiskey] instanceof ArrayObject) {
                unset($keys[key($keys)]);
                return $this->_keyExistsRecursive($keys, $data_array[$thiskey]);
            }
        } else { // if any key DNE, return false
            return false;
        }
    }

    /**
     * Retrieves a value from the _source array. This should NOT be called
     * directly, but needs to be public for use by AccessorAbstract. Maybe a
     * different approach should be considered (adapt getRaw()?)
     *
     * @param string $key
     * @return mixed
     * @private
     */
    public function _getValue($key)
    {
        if (strpos($key, ISPK_ARRAY_PATH_SEPARATOR) !== false) {
            $key = trim($key, ISPK_ARRAY_PATH_SEPARATOR);
            $keys = explode(ISPK_ARRAY_PATH_SEPARATOR, $key);
            return $this->_getValueRecursive($keys, $this->_source);
        } else {
            return $this->_source[$key];
        }
    }

    protected function _getValueRecursive($keys, $data_array, $level = 0)
    {
        $thiskey = current($keys);

        if (is_numeric($thiskey)) { // force numeric strings to be integers
            $thiskey = (int) $thiskey;
        }

        if (array_key_exists($thiskey, $data_array)) {
            if (sizeof($keys) == 1) {
                return $data_array[$thiskey];
            } else if ($data_array[$thiskey] instanceof ArrayObject) {
                if ($level < ISPK_RECURSION_MAX) {
                    unset($keys[key($keys)]);
                    return $this->_getValueRecursive($keys, $data_array[$thiskey], $level + 1);
                } else {
                    Inspekt_Error::raiseError('Inspekt recursion limit met', E_USER_WARNING);
                    return false;
                }
            }
        } else { // if any key DNE, return false
            return false;
        }
    }

    /**
     * Sets a value in the _source array
     *
     * @param mixed $key
     * @param mixed $val
     * @return mixed
     */
    protected function _setValue($key, $val)
    {
        if (strpos($key, ISPK_ARRAY_PATH_SEPARATOR) !== false) {
            $key = trim($key, ISPK_ARRAY_PATH_SEPARATOR);
            $keys = explode(ISPK_ARRAY_PATH_SEPARATOR, $key);
            return $this->_setValueRecursive($keys, $this->_source);
        } else {
            $this->_source[$key] = $val;
            return $this->_source[$key];
        }
    }

    protected function _setValueRecursive($keys, $val, $data_array, $level = 0)
    {
        $thiskey = current($keys);

        if (is_numeric($thiskey)) { // force numeric strings to be integers
            $thiskey = (int)$thiskey;
        }

        if (array_key_exists($thiskey, $data_array)) {
            if (sizeof($keys) == 1) {
                $data_array[$thiskey] = $val;
                return $data_array[$thiskey];
            } elseif ($data_array[$thiskey] instanceof ArrayObject) {
                if ($level < ISPK_RECURSION_MAX) {
                    unset($keys[key($keys)]);
                    return $this->_setValueRecursive($keys, $val, $data_array[$thiskey], $level + 1);
                } else {
                    Inspekt_Error::raiseError('Inspekt recursion limit met', E_USER_WARNING);
                    return false;
                }
            }
        } else { // if any key DNE, return false
            return false;
        }
    }
}
