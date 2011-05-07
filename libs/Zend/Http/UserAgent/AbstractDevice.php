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
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

// require_once 'Zend/Http/UserAgent/Device.php';

/**
 * Abstract Class to define a browser device.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Http_UserAgent_AbstractDevice
    implements Zend_Http_UserAgent_Device
{
    /**
     * Browser signature
     *
     * @var string
     */
    protected $_browser = '';

    /**
     * Browser version
     *
     * @var string
     */
    protected $_browserVersion = '';

    /**
     * Configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * User Agent chain
     *
     * @var string
     */
    protected $_userAgent;

    /**
     * Server variable
     *
     * @var array
     */
    protected $_server;

    /**
     * Image types
     *
     * @var array
     */
    protected $_images = array(
        'jpeg',
        'gif',
        'png',
        'pjpeg',
        'x-png',
        'bmp',
    );

    /**
     * Browser/Device features
     *
     * @var array
     */
    protected $_aFeatures = array();

    /**
     * Browser/Device features groups
     *
     * @var array
     */
    protected $_aGroup = array();

    /**
     * Constructor
     *
     * @param  null|string|array $userAgent If array, restores from serialized version
     * @param  array $server
     * @param  array $config
     * @return void
     */
    public function __construct($userAgent = null, array $server = array(), array $config = array())
    {
        if (is_array($userAgent)) {
            // Restoring from serialized array
            $this->_restoreFromArray($userAgent);
        } else {
            // Constructing new object
            $this->setUserAgent($userAgent);
            $this->_server    = $server;
            $this->_config    = $config;
            $this->_getDefaultFeatures();
            $this->_defineFeatures();
        }
    }

    /**
     * Serialize object
     *
     * @return string
     */
    public function serialize()
    {
        $spec = array(
            '_aFeatures'      => $this->_aFeatures,
            '_aGroup'         => $this->_aGroup,
            '_browser'        => $this->_browser,
            '_browserVersion' => $this->_browserVersion,
            '_userAgent'      => $this->_userAgent,
            '_images'         => $this->_images,
        );
        return serialize($spec);
    }

    /**
     * Unserialize
     *
     * @param  string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $spec = unserialize($serialized);
        $this->_restoreFromArray($spec);
    }

    /**
     * Restore object state from array
     *
     * @param  array $spec
     * @return void
     */
    protected function _restoreFromArray(array $spec)
    {
        foreach ($spec as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Look for features
     *
     * @return array|null
     */
    protected function _defineFeatures()
    {
        $features = $this->_loadFeaturesAdapter();

        if (is_array($features)) {
            $this->_aFeatures = array_merge($this->_aFeatures, $features);
        }

        return $this->_aFeatures;
    }

    /**
     * Gets the browser type identifier
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Check a feature for the current browser/device.
     *
     * @param  string $feature The feature to check.
     * @return bool
     */
    public function hasFeature($feature)
    {
        return (!empty($this->_aFeatures[$feature]));
    }

    /**
     * Gets the value of the current browser/device feature
     *
     * @param  string $feature Feature to search
     * @return string|null
     */
    public function getFeature($feature)
    {
        if ($this->hasFeature($feature)) {
            return $this->_aFeatures[$feature];
        }
    }

    /**
     * Set a feature for the current browser/device.
     *
     * @param  string $feature The feature to set.
     * @param  string $value (option) feature value.
     * @param  string $group (option) Group to associate with the feature
     * @return Zend_Http_UserAgent_AbstractDevice
     */
    public function setFeature($feature, $value = false, $group = '')
    {
        $this->_aFeatures[$feature] = $value;
        if (!empty($group)) {
            $this->setGroup($group, $feature);
        }
        return $this;
    }

    /**
     * Affects a feature to a group
     *
     * @param  string $group Group name
     * @param  string $feature Feature name
     * @return Zend_Http_UserAgent_AbstractDevice
     */
    public function setGroup($group, $feature)
    {
        if (!isset($this->_aGroup[$group])) {
            $this->_aGroup[$group] = array();
        }
        if (!in_array($feature, $this->_aGroup[$group])) {
            $this->_aGroup[$group][] = $feature;
        }
        return $this;
    }

    /**
     * Gets an array of features associated to a group
     *
     * @param  string $group Group param
     * @return array
     */
    public function getGroup($group)
    {
        return $this->_aGroup[$group];
    }

    /**
     * Gets all the browser/device features
     *
     * @return array
     */
    public function getAllFeatures()
    {
        return $this->_aFeatures;
    }

    /**
     * Gets all the browser/device features' groups
     *
     * @return array
     */
    public function getAllGroups()
    {
        return $this->_aGroup;
    }

    /**
     * Sets all the standard features extracted from the User Agent chain and $this->_server
     * vars
     *
     * @return void
     */
    protected function _getDefaultFeatures()
    {
        $server = array();

        // gets info from user agent chain
        $uaExtract = $this->extractFromUserAgent($this->getUserAgent());

        if (is_array($uaExtract)) {
            foreach ($uaExtract as $key => $info) {
                $this->setFeature($key, $info, 'product_info');
            }
        }

        if (isset($uaExtract['browser_name'])) {
            $this->_browser = $uaExtract['browser_name'];
        }
        if (isset($uaExtract['browser_version'])) {
            $this->_browserVersion = $uaExtract['browser_version'];
        }
        if (isset($uaExtract['device_os'])) {
            $this->device_os = $uaExtract['device_os_name'];
        }

        /* browser & device info */
        $this->setFeature('is_wireless_device', false, 'product_info');
        $this->setFeature('is_mobile', false, 'product_info');
        $this->setFeature('is_desktop', false, 'product_info');
        $this->setFeature('is_tablet', false, 'product_info');
        $this->setFeature('is_bot', false, 'product_info');
        $this->setFeature('is_email', false, 'product_info');
        $this->setFeature('is_text', false, 'product_info');
        $this->setFeature('device_claims_web_support', false, 'product_info');

        $this->setFeature('is_' . strtolower($this->getType()), true, 'product_info');

        /* sets the browser name */
        if (isset($this->list) && empty($this->_browser)) {
            $lowerUserAgent = strtolower($this->getUserAgent());
            foreach ($this->list as $browser_signature) {
                if (strpos($lowerUserAgent, $browser_signature) !== false) {
                    $this->_browser = strtolower($browser_signature);
                    $this->setFeature('browser_name', $this->_browser, 'product_info');
                }
            }
        }

        /* sets the client IP */
        if (isset($this->_server['remote_addr'])) {
            $this->setFeature('client_ip', $this->_server['remote_addr'], 'product_info');
        } elseif (isset($this->_server['http_x_forwarded_for'])) {
            $this->setFeature('client_ip', $this->_server['http_x_forwarded_for'], 'product_info');
        } elseif (isset($this->_server['http_client_ip'])) {
            $this->setFeature('client_ip', $this->_server['http_client_ip'], 'product_info');
        }

        /* sets the server infos */
        if (isset($this->_server['server_software'])) {
            if (strpos($this->_server['server_software'], 'Apache') !== false || strpos($this->_server['server_software'], 'LiteSpeed') !== false) {
                $server['version'] = 1;
                if (strpos($this->_server['server_software'], 'Apache/2') !== false) {
                    $server['version'] = 2;
                }
                $server['server'] = 'apache';
            }

            if (strpos($this->_server['server_software'], 'Microsoft-IIS') !== false) {
                $server['server'] = 'iis';
            }

            if (strpos($this->_server['server_software'], 'Unix') !== false) {
                $server['os'] = 'unix';
                if (isset($_ENV['MACHTYPE'])) {
                    if (strpos($_ENV['MACHTYPE'], 'linux') !== false) {
                        $server['os'] = 'linux';
                    }
                }
            } elseif (strpos($this->_server['server_software'], 'Win') !== false) {
                $server['os'] = 'windows';
            }

            if (preg_match('/Apache\/([0-9\.]*)/', $this->_server['server_software'], $arr)) {
                if ($arr[1]) {
                    $server['version'] = $arr[1];
                    $server['server']  = 'apache';
                }
            }
        }

        $this->setFeature('php_version', phpversion(), 'server_info');
        if (isset($server['server'])) {
            $this->setFeature('server_os', $server['server'], 'server_info');
        }
        if (isset($server['version'])) {
            $this->setFeature('server_os_version', $server['version'], 'server_info');
        }
        if (isset($this->_server['http_accept'])) {
            $this->setFeature('server_http_accept', $this->_server['http_accept'], 'server_info');
        }
        if (isset($this->_server['http_accept_language'])) {
            $this->setFeature('server_http_accept_language', $this->_server['http_accept_language'], 'server_info');
        }
        if (isset($this->_server['server_addr'])) {
            $this->setFeature('server_ip', $this->_server['server_addr'], 'server_info');
        }
        if (isset($this->_server['server_name'])) {
            $this->setFeature('server_name', $this->_server['server_name'], 'server_info');
        }
    }

    /**
     * Extract and sets informations from the User Agent chain
     *
     * @param  string $userAgent User Agent chain
     * @return array
     */
    public static function extractFromUserAgent($userAgent)
    {
        $userAgent = trim($userAgent);

        /**
         * @see http://www.texsoft.it/index.php?c=software&m=sw.php.useragent&l=it
         */
        $pattern =  "(([^/\s]*)(/(\S*))?)(\s*\[[a-zA-Z][a-zA-Z]\])?\s*(\\((([^()]|(\\([^()]*\\)))*)\\))?\s*";
        preg_match("#^$pattern#", $userAgent, $match);

        $comment = array();
        if (isset($match[7])) {
            $comment = explode(';', $match[7]);
        }

        // second part if exists
        $end = substr($userAgent, strlen($match[0]));
        if (!empty($end)) {
            $result['others']['full'] = $end;
        }

        $match2 = array();
        if (isset($result['others'])) {
            preg_match_all('/(([^\/\s]*)(\/)?([^\/\(\)\s]*)?)(\s\((([^\)]*)*)\))?/i', $result['others']['full'], $match2);
        }
        $result['user_agent']   = trim($match[1]);
        $result['product_name'] = isset($match[2]) ? trim($match[2]) : '';
        $result['browser_name'] = $result['product_name'];
        if (isset($match[4]) && trim($match[4])) {
            $result['product_version'] = trim($match[4]);
            $result['browser_version'] = trim($match[4]);
        }
        if (count($comment) && !empty($comment[0])) {
            $result['comment']['full']     = trim($match[7]);
            $result['comment']['detail']   = $comment;
            $result['compatibility_flag']  = trim($comment[0]);
            if (isset($comment[1])) {
                $result['browser_token']   = trim($comment[1]);
            }
            if (isset($comment[2])) {
                $result['device_os_token'] = trim($comment[2]);
            }
        }
        if (empty($result['device_os_token']) && !empty($result['compatibility_flag'])) {
            // some browsers do not have a platform token
            $result['device_os_token'] = $result['compatibility_flag'];
        }
        if ($match2) {
            $i = 0;
            $max = count($match2[0]);
            for ($i = 0; $i < $max; $i ++) {
                if (!empty($match2[0][$i])) {
                    $result['others']['detail'][] = array(
                        $match2[0][$i],
                        $match2[2][$i],
                        $match2[4][$i],
                    );
                }
            }
        }

        /** Security level */
        $security = array(
            'N' => 'no security',
            'U' => 'strong security',
            'I' => 'weak security',
        );
        if (!empty($result['browser_token'])) {
            if (isset($security[$result['browser_token']])) {
                $result['security_level'] = $security[$result['browser_token']];
                unset($result['browser_token']);
            }
        }

        $product = strtolower($result['browser_name']);

        // Mozilla : true && false
        $compatibleOrIe = false;
        if (isset($result['compatibility_flag']) && isset($result['comment'])) {
            $compatibleOrIe = ($result['compatibility_flag'] == 'compatible' || strpos($result['comment']['full'], "MSIE") !== false);
        }
        if ($product == 'mozilla' && $compatibleOrIe) {
            if (!empty($result['browser_token'])) {
                // Classic Mozilla chain
                preg_match_all('/([^\/\s].*)(\/|\s)(.*)/i', $result['browser_token'], $real);
            } else {
                // MSIE specific chain with 'Windows' compatibility flag
                foreach ($result['comment']['detail'] as $v) {
                    if (strpos($v, 'MSIE') !== false) {
                        $real[0][1]               = trim($v);
                        $result['browser_engine'] = "MSIE";
                        $real[1][0]               = "Internet Explorer";
                        $temp                     = explode(' ', trim($v));
                        $real[3][0]               = $temp[1];

                    }
                    if (strpos($v, 'Win') !== false) {
                        $result['device_os_token'] = trim($v);
                    }
                }
            }

            if (!empty($real[0])) {
                $result['browser_name']    = $real[1][0];
                $result['browser_version'] = $real[3][0];
            } else {
                $result['browser_name']    = $result['browser_token'];
                $result['browser_version'] = '??';
            }
        } elseif ($product == 'mozilla' && $result['browser_version'] < 5.0) {
            // handles the real Mozilla (or old Netscape if version < 5.0)
            $result['browser_name'] = 'Netscape';
        }

        /** windows */
        if ($result['browser_name'] == 'MSIE') {
            $result['browser_engine'] = 'MSIE';
            $result['browser_name']   = 'Internet Explorer';
        }
        if (isset($result['device_os_token'])) {
            if (strpos($result['device_os_token'], 'Win') !== false) {

                $windows = array(
                    'Windows NT 6.1'          => 'Windows 7',
                    'Windows NT 6.0'          => 'Windows Vista',
                    'Windows NT 5.2'          => 'Windows Server 2003',
                    'Windows NT 5.1'          => 'Windows XP',
                    'Windows NT 5.01'         => 'Windows 2000 SP1',
                    'Windows NT 5.0'          => 'Windows 2000',
                    'Windows NT 4.0'          => 'Microsoft Windows NT 4.0',
                    'WinNT'                   => 'Microsoft Windows NT 4.0',
                    'Windows 98; Win 9x 4.90' => 'Windows Me',
                    'Windows 98'              => 'Windows 98',
                    'Win98'                   => 'Windows 98',
                    'Windows 95'              => 'Windows 95',
                    'Win95'                   => 'Windows 95',
                    'Windows CE'              => 'Windows CE',
                );
                if (isset($windows[$result['device_os_token']])) {
                    $result['device_os_name'] = $windows[$result['device_os_token']];
                } else {
                    $result['device_os_name'] = $result['device_os_token'];
                }
            }
        }

        // iphone
        $apple_device = array(
            'iPhone',
            'iPod',
            'iPad',
        );
        if (isset($result['compatibility_flag'])) {
            if (in_array($result['compatibility_flag'], $apple_device)) {
                $result['device']           = strtolower($result['compatibility_flag']);
                $result['device_os_token']  = 'iPhone OS';
                $result['browser_language'] = trim($comment[3]);
                $result['browser_version']  = $result['others']['detail'][1][2];
                if (!empty($result['others']['detail'][2])) {
                    $result['firmware'] = $result['others']['detail'][2][2];
                }
                if (!empty($result['others']['detail'][3])) {
                    $result['browser_name']  = $result['others']['detail'][3][1];
                    $result['browser_build'] = $result['others']['detail'][3][2];
                }
            }
        }

        // Safari
        if (isset($result['others'])) {
            if ($result['others']['detail'][0][1] == 'AppleWebKit') {
                $result['browser_engine'] = 'AppleWebKit';
                if ($result['others']['detail'][1][1] == 'Version') {
                    $result['browser_version'] = $result['others']['detail'][1][2];
                } else {
                    $result['browser_version'] = $result['others']['detail'][count($result['others']['detail']) - 1][2];
                }
                if (isset($comment[3])) {
                     $result['browser_language'] = trim($comment[3]);
                }

                $last = $result['others']['detail'][count($result['others']['detail']) - 1][1];

                if (empty($result['others']['detail'][2][1]) || $result['others']['detail'][2][1] == 'Safari') {
                    $result['browser_name']    = ($result['others']['detail'][1][1] && $result['others']['detail'][1][1] != 'Version' ? $result['others']['detail'][1][1] : 'Safari');
                    $result['browser_version'] = ($result['others']['detail'][1][2] ? $result['others']['detail'][1][2] : $result['others']['detail'][0][2]);
                } else {
                    $result['browser_name']    = $result['others']['detail'][2][1];
                    $result['browser_version'] = $result['others']['detail'][2][2];

                    // mobile version
                    if ($result['browser_name'] == 'Mobile') {
                        $result['browser_name'] = 'Safari ' . $result['browser_name'];
                        if ($result['others']['detail'][1][1] == 'Version') {
                            $result['browser_version'] = $result['others']['detail'][1][2];
                        }
                    }
                }

                // For Safari < 2.2, AppleWebKit version gives the Safari version
                if (strpos($result['browser_version'], '.') > 2 || (int) $result['browser_version'] > 20) {
                    $temp = explode('.', $result['browser_version']);
                    $build = (int) $temp[0];
                    $awkVersion = array(
                        48  => '0.8',
                        73  => '0.9',
                        85  => '1.0',
                        103 => '1.1',
                        124 => '1.2',
                        300 => '1.3',
                        400 => '2.0',
                    );
                    foreach ($awkVersion as $k => $v) {
                        if ($build >= $k) {
                            $result['browser_version'] = $v;
                        }
                    }
                }
            }

            // Gecko (Firefox or compatible)
            if ($result['others']['detail'][0][1] == 'Gecko') {
                $searchRV = true;
                if (!empty($result['others']['detail'][1][1]) && !empty($result['others']['detail'][count($result['others']['detail']) - 1][2]) || strpos(strtolower($result['others']['full']), 'opera') !== false) {
                    $searchRV = false;
                    $result['browser_engine'] = $result['others']['detail'][0][1];

                    // the name of the application is at the end indepenently
                    // of quantity of information in $result['others']['detail']
                    $last = count($result['others']['detail']) - 1;

                    // exception : if the version of the last information is
                    // empty we take the previous one
                    if (empty($result['others']['detail'][$last][2])) {
                        $last --;
                    }

                    // exception : if the last one is 'Red Hat' or 'Debian' =>
                    // use rv: to find browser_version */
                    if (in_array($result['others']['detail'][$last][1], array(
                        'Debian',
                        'Hat',
                    ))) {
                        $searchRV = true;
                    }
                    $result['browser_name']    = $result['others']['detail'][$last][1];
                    $result['browser_version'] = $result['others']['detail'][$last][2];
                    if (isset($comment[4])) {
                        $result['browser_build'] = trim($comment[4]);
                    }
                    if (isset($comment[3])) {
                        $result['browser_language'] = trim($comment[3]);
                    }

                    // Netscape
                    if ($result['browser_name'] == 'Navigator' || $result['browser_name'] == 'Netscape6') {
                        $result['browser_name'] = 'Netscape';
                    }
                }
                if ($searchRV) {
                    // Mozilla alone : the version is identified by rv:
                    $result['browser_name'] = 'Mozilla';
                    if (isset($result['comment']['detail'])) {
                        foreach ($result['comment']['detail'] as $rv) {
                            if (strpos($rv, 'rv:') !== false) {
                                $result['browser_version'] = trim(str_replace('rv:', '', $rv));
                            }
                        }
                    }
                }
            }

            // Netscape
            if ($result['others']['detail'][0][1] == 'Netscape') {
                $result['browser_name']    = 'Netscape';
                $result['browser_version'] = $result['others']['detail'][0][2];
            }

            // Opera
            // Opera: engine Presto
            if ($result['others']['detail'][0][1] == 'Presto') {
                $result['browser_engine'] = 'Presto';
                if (!empty($result['others']['detail'][1][2])) {
                    $result['browser_version'] = $result['others']['detail'][1][2];
                }
            }

            // UA ends with 'Opera X.XX'
            if ($result['others']['detail'][0][1] == 'Opera') {
                $result['browser_name']    = $result['others']['detail'][0][1];
                $result['browser_version'] = $result['others']['detail'][1][1];
            }

            // Opera Mini
            if (isset($result["browser_token"])) {
                if (strpos($result["browser_token"], 'Opera Mini') !== false) {
                    $result['browser_name'] = 'Opera Mini';
                }
            }

            // Symbian
            if ($result['others']['detail'][0][1] == 'SymbianOS') {
                $result['device_os_token'] = 'SymbianOS';
            }
        }

        // UA ends with 'Opera X.XX'
        if (isset($result['browser_name']) && isset($result['browser_engine'])) {
            if ($result['browser_name'] == 'Opera' && $result['browser_engine'] == 'Gecko' && empty($result['browser_version'])) {
                $result['browser_version'] = $result['others']['detail'][count($result['others']['detail']) - 1][1];
            }
        }

        // cleanup
        if (isset($result['browser_version']) && isset($result['browser_build'])) {
            if ($result['browser_version'] == $result['browser_build']) {
                unset($result['browser_build']);
            }
        }

        // compatibility
        $compatibility['AppleWebKit'] = 'Safari';
        $compatibility['Gecko']       = 'Firefox';
        $compatibility['MSIE']        = 'Internet Explorer';
        $compatibility['Presto']      = 'Opera';
        if (!empty($result['browser_engine'])) {
            if (isset($compatibility[$result['browser_engine']])) {
                $result['browser_compatibility'] = $compatibility[$result['browser_engine']];
            }
        }

        ksort($result);
        return $result;
    }

    /**
     * Loads the Features Adapter if it's defined in the $config array
     * Otherwise, nothing is done
     *
     * @param  string $browserType Browser type
     * @return array
     */
    protected function _loadFeaturesAdapter()
    {
        $config      = $this->_config;
        $browserType = $this->getType();
        if (!isset($config[$browserType]) || !isset($config[$browserType]['features'])) {
            return array();
        }
        $config = $config[$browserType]['features'];

        if (empty($config['classname'])) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception('The ' . $this->getType() . ' features adapter must have a "classname" config parameter defined');
        }

        $className = $config['classname'];
        if (!class_exists($className)) {
            if (isset($config['path'])) {
                $path = $config['path'];
            } else {
                // require_once 'Zend/Http/UserAgent/Exception.php';
                throw new Zend_Http_UserAgent_Exception('The ' . $this->getType() . ' features adapter must have a "path" config parameter defined');
            }

            if (false === include_once ($path)) {
                // require_once 'Zend/Http/UserAgent/Exception.php';
                throw new Zend_Http_UserAgent_Exception('The ' . $this->getType() . ' features adapter path that does not exist');
            }
        }

        return call_user_func(array($className, 'getFromRequest'), $this->_server, $this->_config);
    }

    /**
     * Retrieve image format support
     *
     * @return array
     */
    public function getImageFormatSupport()
    {
        return $this->_images;
    }

    /**
     * Get maximum image height supported by this device
     *
     * @return int
     */
    public function getMaxImageHeight()
    {
        return null;
    }

    /**
     * Get maximum image width supported by this device
     *
     * @return int
     */
    public function getMaxImageWidth()
    {
        return null;
    }

    /**
     * Get physical screen height of this device
     *
     * @return int
     */
    public function getPhysicalScreenHeight()
    {
        return null;
    }

    /**
     * Get physical screen width of this device
     *
     * @return int
     */
    public function getPhysicalScreenWidth()
    {
        return null;
    }

    /**
     * Get preferred markup type
     *
     * @return string
     */
    public function getPreferredMarkup()
    {
        return 'xhtml';
    }

    /**
     * Get supported X/HTML version
     *
     * @return int
     */
    public function getXhtmlSupportLevel()
    {
        return 4;
    }

    /**
     * Does the device support Flash?
     *
     * @return bool
     */
    public function hasFlashSupport()
    {
        return true;
    }

    /**
     * Does the device support PDF?
     *
     * @return bool
     */
    public function hasPdfSupport()
    {
        return true;
    }

    /**
     * Does the device have a phone number associated with it?
     *
     * @return bool
     */
    public function hasPhoneNumber()
    {
        return false;
    }

    /**
     * Does the device support HTTPS?
     *
     * @return bool
     */
    public function httpsSupport()
    {
        return true;
    }

    /**
     * Get the browser type
     *
     * @return string
     */
    public function getBrowser()
    {
        return $this->_browser;
    }

    /**
     * Get the browser version
     *
     * @return string
     */
    public function getBrowserVersion()
    {
        return $this->_browserVersion;
    }

    /**
     * Get the user agent string
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    /**
     * @return the $_images
     */
    public function getImages()
    {
        return $this->_images;
    }

    /**
     * @param string $browser
     */
    public function setBrowser($browser)
    {
        $this->_browser = $browser;
    }

    /**
     * @param string $browserVersion
     */
    public function setBrowserVersion($browserVersion)
    {
        $this->_browserVersion = $browserVersion;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->_userAgent = $userAgent;
        return $this;
    }

    /**
     * @param array $_images
     */
    public function setImages($_images)
    {
        $this->_images = $_images;
    }

    /**
     * Match a user agent string against a list of signatures
     *
     * @param  string $userAgent
     * @param  array $signatures
     * @return bool
     */
    protected static function _matchAgentAgainstSignatures($userAgent, $signatures)
    {
        $userAgent = strtolower($userAgent);
        foreach ($signatures as $signature) {
            if (!empty($signature)) {
                if (strpos($userAgent, $signature) !== false) {
                    // Browser signature was found in user agent string
                    return true;
                }
            }
        }
        return false;
    }
}
