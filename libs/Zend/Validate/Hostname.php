<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Hostname.php 9432 2008-05-09 14:18:37Z jokke $
 */


/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @see Zend_Validate_Ip
 */
require_once 'Zend/Validate/Ip.php';

/**
 * Please note there are two standalone test scripts for testing IDN characters due to problems
 * with file encoding.
 *
 * The first is tests/Zend/Validate/HostnameTestStandalone.php which is designed to be run on
 * the command line.
 *
 * The second is tests/Zend/Validate/HostnameTestForm.php which is designed to be run via HTML
 * to allow users to test entering UTF-8 characters in a form.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Hostname extends Zend_Validate_Abstract
{

    const IP_ADDRESS_NOT_ALLOWED  = 'hostnameIpAddressNotAllowed';
    const UNKNOWN_TLD             = 'hostnameUnknownTld';
    const INVALID_DASH            = 'hostnameDashCharacter';
    const INVALID_HOSTNAME_SCHEMA = 'hostnameInvalidHostnameSchema';
    const UNDECIPHERABLE_TLD      = 'hostnameUndecipherableTld';
    const INVALID_HOSTNAME        = 'hostnameInvalidHostname';
    const INVALID_LOCAL_NAME      = 'hostnameInvalidLocalName';
    const LOCAL_NAME_NOT_ALLOWED  = 'hostnameLocalNameNotAllowed';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::IP_ADDRESS_NOT_ALLOWED  => "'%value%' appears to be an IP address, but IP addresses are not allowed",
        self::UNKNOWN_TLD             => "'%value%' appears to be a DNS hostname but cannot match TLD against known list",
        self::INVALID_DASH            => "'%value%' appears to be a DNS hostname but contains a dash (-) in an invalid position",
        self::INVALID_HOSTNAME_SCHEMA => "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
        self::UNDECIPHERABLE_TLD      => "'%value%' appears to be a DNS hostname but cannot extract TLD part",
        self::INVALID_HOSTNAME        => "'%value%' does not match the expected structure for a DNS hostname",
        self::INVALID_LOCAL_NAME      => "'%value%' does not appear to be a valid local network name",
        self::LOCAL_NAME_NOT_ALLOWED  => "'%value%' appears to be a local network name but local network names are not allowed"
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'tld' => '_tld'
    );

    /**
     * Allows Internet domain names (e.g., example.com)
     */
    const ALLOW_DNS   = 1;

    /**
     * Allows IP addresses
     */
    const ALLOW_IP    = 2;

    /**
     * Allows local network names (e.g., localhost, www.localdomain)
     */
    const ALLOW_LOCAL = 4;

    /**
     * Allows all types of hostnames
     */
    const ALLOW_ALL   = 7;

    /**
     * Whether IDN domains are validated
     *
     * @var boolean
     */
    private $_validateIdn = true;

    /**
     * Whether TLDs are validated against a known list
     *
     * @var boolean
     */
    private $_validateTld = true;

    /**
     * Bit field of ALLOW constants; determines which types of hostnames are allowed
     *
     * @var integer
     */
    protected $_allow;

    /**
     * Bit field of CHECK constants; determines what additional hostname checks to make
     *
     * @var unknown_type
     */
    // protected $_check;

    /**
     * Array of valid top-level-domains
     *
     * @var array
     * @see ftp://data.iana.org/TLD/tlds-alpha-by-domain.txt  List of all TLDs by domain
     */
    protected $_validTlds = array(
        'ac', 'ad', 'ae', 'aero', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao',
        'aq', 'ar', 'arpa', 'as', 'asia', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb',
        'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'biz', 'bj', 'bm', 'bn', 'bo',
        'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cat', 'cc', 'cd',
        'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'com', 'coop',
        'cr', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do',
        'dz', 'ec', 'edu', 'ee', 'eg', 'er', 'es', 'et', 'eu', 'fi', 'fj',
        'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh',
        'gi', 'gl', 'gm', 'gn', 'gov', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu',
        'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il',
        'im', 'in', 'info', 'int', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm',
        'jo', 'jobs', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw',
        'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu',
        'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mil', 'mk', 'ml', 'mm',
        'mn', 'mo', 'mobi', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'museum', 'mv',
        'mw', 'mx', 'my', 'mz', 'na', 'name', 'nc', 'ne', 'net', 'nf', 'ng',
        'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'org', 'pa', 'pe',
        'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'pro', 'ps', 'pt',
        'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd',
        'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr',
        'st', 'su', 'sv', 'sy', 'sz', 'tc', 'td', 'tel', 'tf', 'tg', 'th', 'tj',
        'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'travel', 'tt', 'tv', 'tw',
        'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've',
        'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'yu', 'za', 'zm',
        'zw'
        );

    /**
     * @var string
     */
    protected $_tld;

    /**
     * Sets validator options
     *
     * @param integer          $allow       OPTIONAL Set what types of hostname to allow (default ALLOW_DNS)
     * @param boolean          $validateIdn OPTIONAL Set whether IDN domains are validated (default true)
     * @param boolean          $validateTld OPTIONAL Set whether the TLD element of a hostname is validated (default true)
     * @param Zend_Validate_Ip $ipValidator OPTIONAL
     * @return void
     * @see http://www.iana.org/cctld/specifications-policies-cctlds-01apr02.htm  Technical Specifications for ccTLDs
     */
    public function __construct($allow = self::ALLOW_DNS, $validateIdn = true, $validateTld = true, Zend_Validate_Ip $ipValidator = null)
    {
        // Set allow options
        $this->setAllow($allow);

        // Set validation options
        $this->_validateIdn = $validateIdn;
        $this->_validateTld = $validateTld;

        $this->setIpValidator($ipValidator);
    }

    /**
     * @param Zend_Validate_Ip $ipValidator OPTIONAL
     * @return void;
     */
    public function setIpValidator(Zend_Validate_Ip $ipValidator = null)
    {
        if ($ipValidator === null) {
            $ipValidator = new Zend_Validate_Ip();
        }
        $this->_ipValidator = $ipValidator;
    }

    /**
     * Returns the allow option
     *
     * @return integer
     */
    public function getAllow()
    {
        return $this->_allow;
    }

    /**
     * Sets the allow option
     *
     * @param  integer $allow
     * @return Zend_Validate_Hostname Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->_allow = $allow;
        return $this;
    }

    /**
     * Set whether IDN domains are validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param boolean $allowed Set allowed to true to validate IDNs, and false to not validate them
     */
    public function setValidateIdn ($allowed)
    {
        $this->_validateIdn = (bool) $allowed;
    }

    /**
     * Set whether the TLD element of a hostname is validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param boolean $allowed Set allowed to true to validate TLDs, and false to not validate them
     */
    public function setValidateTld ($allowed)
    {
        $this->_validateTld = (bool) $allowed;
    }

    /**
     * Sets the check option
     *
     * @param  integer $check
     * @return Zend_Validate_Hostname Provides a fluent interface
     */
    /*
    public function setCheck($check)
    {
        $this->_check = $check;
        return $this;
    }
     */

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the $value is a valid hostname with respect to the current allow option
     *
     * @param  string $value
     * @throws Zend_Validate_Exception if a fatal error occurs for validation process
     * @return boolean
     */
    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        // Check input against IP address schema
        if ($this->_ipValidator->setTranslator($this->getTranslator())->isValid($valueString)) {
            if (!($this->_allow & self::ALLOW_IP)) {
                $this->_error(self::IP_ADDRESS_NOT_ALLOWED);
                return false;
            } else{
                return true;
            }
        }

        // Check input against DNS hostname schema
        $domainParts = explode('.', $valueString);
        if ((count($domainParts) > 1) && (strlen($valueString) >= 4) && (strlen($valueString) <= 254)) {
            $status = false;

            do {
                // First check TLD
                if (preg_match('/([a-z]{2,10})$/i', end($domainParts), $matches)) {

                    reset($domainParts);

                    // Hostname characters are: *(label dot)(label dot label); max 254 chars
                    // label: id-prefix [*ldh{61} id-prefix]; max 63 chars
                    // id-prefix: alpha / digit
                    // ldh: alpha / digit / dash

                    // Match TLD against known list
                    $this->_tld = strtolower($matches[1]);
                    if ($this->_validateTld) {
                        if (!in_array($this->_tld, $this->_validTlds)) {
                            $this->_error(self::UNKNOWN_TLD);
                            $status = false;
                            break;
                        }
                    }

                    /**
                     * Match against IDN hostnames
                     * @see Zend_Validate_Hostname_Interface
                     */
                    $labelChars = 'a-z0-9';
                    $utf8 = false;
                    $classFile = 'Zend/Validate/Hostname/' . ucfirst($this->_tld) . '.php';
                    if ($this->_validateIdn) {
                        if (Zend_Loader::isReadable($classFile)) {

                            // Load additional characters
                            $className = 'Zend_Validate_Hostname_' . ucfirst($this->_tld);
                            Zend_Loader::loadClass($className);
                            $labelChars .= call_user_func(array($className, 'getCharacters'));
                            $utf8 = true;
                        }
                    }

                    // Keep label regex short to avoid issues with long patterns when matching IDN hostnames
                    $regexLabel = '/^[' . $labelChars . '\x2d]{1,63}$/i';
                    if ($utf8) {
                        $regexLabel .= 'u';
                    }

                    // Check each hostname part
                    $valid = true;
                    foreach ($domainParts as $domainPart) {

                        // Check dash (-) does not start, end or appear in 3rd and 4th positions
                        if (strpos($domainPart, '-') === 0 ||
                        (strlen($domainPart) > 2 && strpos($domainPart, '-', 2) == 2 && strpos($domainPart, '-', 3) == 3) ||
                        strrpos($domainPart, '-') === strlen($domainPart) - 1) {

                            $this->_error(self::INVALID_DASH);
                            $status = false;
                            break 2;
                        }

                        // Check each domain part
                        $status = @preg_match($regexLabel, $domainPart);
                        if ($status === false) {
                            /**
                             * Regex error
                             * @see Zend_Validate_Exception
                             */
                            require_once 'Zend/Validate/Exception.php';
                            throw new Zend_Validate_Exception('Internal error: DNS validation failed');
                        } elseif ($status === 0) {
                            $valid = false;
                        }
                    }

                    // If all labels didn't match, the hostname is invalid
                    if (!$valid) {
                        $this->_error(self::INVALID_HOSTNAME_SCHEMA);
                        $status = false;
                    }

                } else {
                    // Hostname not long enough
                    $this->_error(self::UNDECIPHERABLE_TLD);
                    $status = false;
                }
            } while (false);

            // If the input passes as an Internet domain name, and domain names are allowed, then the hostname
            // passes validation
            if ($status && ($this->_allow & self::ALLOW_DNS)) {
                return true;
            }
        } else {
            $this->_error(self::INVALID_HOSTNAME);
        }

        // Check input against local network name schema; last chance to pass validation
        $regexLocal = '/^(([a-zA-Z0-9\x2d]{1,63}\x2e)*[a-zA-Z0-9\x2d]{1,63}){1,254}$/';
        $status = @preg_match($regexLocal, $valueString);
        if (false === $status) {
            /**
             * Regex error
             * @see Zend_Validate_Exception
             */
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Internal error: local network name validation failed');
        }

        // If the input passes as a local network name, and local network names are allowed, then the
        // hostname passes validation
        $allowLocal = $this->_allow & self::ALLOW_LOCAL;
        if ($status && $allowLocal) {
            return true;
        }

        // If the input does not pass as a local network name, add a message
        if (!$status) {
            $this->_error(self::INVALID_LOCAL_NAME);
        }

        // If local network names are not allowed, add a message
        if ($status && !$allowLocal) {
            $this->_error(self::LOCAL_NAME_NOT_ALLOWED);
        }

        return false;
    }

    /**
     * Throws an exception if a regex for $type does not exist
     *
     * @param  string $type
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_Hostname Provides a fluent interface
     */
    /*
    protected function _checkRegexType($type)
    {
        if (!isset($this->_regex[$type])) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("'$type' must be one of ('" . implode(', ', array_keys($this->_regex))
                                            . "')");
        }
        return $this;
    }
     */

}
