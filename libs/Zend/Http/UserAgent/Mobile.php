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

// require_once 'Zend/Http/UserAgent/AbstractDevice.php';

/**
 * Mobile browser type matcher
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_UserAgent_Mobile extends Zend_Http_UserAgent_AbstractDevice
{

    const DEFAULT_FEATURES_ADAPTER_CLASSNAME = 'Zend_Http_UserAgent_Features_Adapter_WurflApi';

    const DEFAULT_FEATURES_ADAPTER_PATH = 'Zend/Http/UserAgent/Features/Adapter/WurflApi.php';

    /**
     * User Agent Signatures
     *
     * @var array
     */
    protected static $_uaSignatures = array(
        'iphone',
        'ipod',
        'ipad',
        'android',
        'blackberry',
        'opera mini',
        'opera mobi',
        'palm',
        'palmos',
        'elaine',
        'windows ce',
        'icab',
        '_mms',
        'ahong',
        'archos',
        'armv',
        'astel',
        'avantgo',
        'benq',
        'blazer',
        'brew',
        'com2',
        'compal',
        'danger',
        'pocket',
        'docomo',
        'epoc',
        'ericsson',
        'eudoraweb',
        'hiptop',
        'htc-',
        'htc_',
        'iemobile',
        'iris',
        'j-phone',
        'kddi',
        'kindle',
        'lg ',
        'lg-',
        'lg/',
        'lg;lx',
        'lge vx',
        'lge',
        'lge-',
        'lge-cx',
        'lge-lx',
        'lge-mx',
        'linux armv',
        'maemo',
        'midp',
        'mini 9.5',
        'minimo',
        'mob-x',
        'mobi',
        'mobile',
        'mobilephone',
        'mot 24',
        'mot-',
        'motorola',
        'n410',
        'netfront',
        'nintendo wii',
        'nintendo',
        'nitro',
        'nokia',
        'novarra-vision',
        'nuvifone',
        'openweb',
        'oper',
        'opwv',
        'palmsource',
        'pdxgw',
        'phone',
        'playstation',
        'polaris',
        'portalmmm',
        'qt embedded',
        'reqwirelessweb',
        'sagem',
        'sam-r',
        'samsu',
        'samsung',
        'sec-',
        'sec-sgh',
        'semc-browser',
        'series60',
        'series70',
        'series80',
        'series90',
        'sharp',
        'sie-m',
        'sie-s',
        'smartphone',
        'sony cmd',
        'sonyericsson',
        'sprint',
        'spv',
        'symbian os',
        'symbian',
        'symbianos',
        'telco',
        'teleca',
        'treo',
        'up.browser',
        'up.link',
        'vodafone',
        'vodaphone',
        'webos',
        'wml',
        'windows phone os 7',
        'wireless',
        'wm5 pie',
        'wms pie',
        'xiino',
        'wap',
        'up/',
        'psion',
        'j2me',
        'klondike',
        'kbrowser'
    );

    /**
     * @var array
     */
    protected static $_haTerms = array(
        'midp',
        'wml',
        'vnd.rim',
        'vnd.wap',
        'j2me',
    );

    /**
     * first 4 letters of mobile User Agent chains
     *
     * @var array
     */
    protected static $_uaBegin = array(
        'w3c ',
        'acs-',
        'alav',
        'alca',
        'amoi',
        'audi',
        'avan',
        'benq',
        'bird',
        'blac',
        'blaz',
        'brew',
        'cell',
        'cldc',
        'cmd-',
        'dang',
        'doco',
        'eric',
        'hipt',
        'inno',
        'ipaq',
        'java',
        'jigs',
        'kddi',
        'keji',
        'leno',
        'lg-c',
        'lg-d',
        'lg-g',
        'lge-',
        'maui',
        'maxo',
        'midp',
        'mits',
        'mmef',
        'mobi',
        'mot-',
        'moto',
        'mwbp',
        'nec-',
        'newt',
        'noki',
        'palm',
        'pana',
        'pant',
        'phil',
        'play',
        'port',
        'prox',
        'qwap',
        'sage',
        'sams',
        'sany',
        'sch-',
        'sec-',
        'send',
        'seri',
        'sgh-',
        'shar',
        'sie-',
        'siem',
        'smal',
        'smar',
        'sony',
        'sph-',
        'symb',
        't-mo',
        'teli',
        'tim-',
        'tosh',
        'tsm-',
        'upg1',
        'upsi',
        'vk-v',
        'voda',
        'wap-',
        'wapa',
        'wapi',
        'wapp',
        'wapr',
        'webc',
        'winw',
        'winw',
        'xda',
        'xda-',
    );

    /**
     * Comparison of the UserAgent chain and User Agent signatures
     *
     * @param  string $userAgent User Agent chain
     * @param  array $server $_SERVER like param
     * @return bool
     */
    public static function match($userAgent, $server)
    {
        //  To have a quick identification, try light-weight tests first
        if (isset($server['all_http'])) {
            if (strpos(strtolower(str_replace(' ', '', $server['all_http'])), 'operam') !== false) {
                // Opera Mini or Opera Mobi
                return true;
            }
        }

        if (isset($server['http_x_wap_profile']) || isset($server['http_profile'])) {
            return true;
        }

        if (isset($server['http_accept'])) {
            if (self::_matchAgentAgainstSignatures($server['http_accept'], self::$_haTerms)) {
                return true;
            }
        }

        if (self::userAgentStart($userAgent)) {
            return true;
        }

        if (self::_matchAgentAgainstSignatures($userAgent, self::$_uaSignatures)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve beginning clause of user agent
     *
     * @param  string $userAgent
     * @return string
     */
    public static function userAgentStart($userAgent)
    {

        $mobile_ua = strtolower(substr($userAgent, 0, 4));

        return (in_array($mobile_ua, self::$_uaBegin));
    }

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($userAgent = null, array $server = array(), array $config = array())
    {
        // For mobile detection, an adapter must be defined
        if (empty($config['mobile']['features'])) {
            $config['mobile']['features']['path']      = self::DEFAULT_FEATURES_ADAPTER_PATH;
            $config['mobile']['features']['classname'] = self::DEFAULT_FEATURES_ADAPTER_CLASSNAME;
        }
        parent::__construct($userAgent, $server, $config);
    }

    /**
     * Gives the current browser type
     *
     * @return string
     */
    public function getType()
    {
        return 'mobile';
    }

    /**
     * Look for features
     *
     * @return string
     */
    protected function _defineFeatures()
    {
        $this->setFeature('is_wireless_device', false, 'product_info');

        parent::_defineFeatures();

        if (isset($this->_aFeatures["mobile_browser"])) {
            $this->setFeature("browser_name", $this->_aFeatures["mobile_browser"]);
            $this->_browser = $this->_aFeatures["mobile_browser"];
        }
        if (isset($this->_aFeatures["mobile_browser_version"])) {
            $this->setFeature("browser_version", $this->_aFeatures["mobile_browser_version"]);
            $this->_browserVersion = $this->_aFeatures["mobile_browser_version"];
        }

        // markup
        if ($this->getFeature('device_os') == 'iPhone OS'
            || $this->getFeature('device_os_token') == 'iPhone OS'
        ) {
            $this->setFeature('markup', 'iphone');
        } else {
            $this->setFeature('markup', $this->getMarkupLanguage($this->getFeature('preferred_markup')));
        }

        // image format
        $this->_images = array();

        if ($this->getFeature('png')) {
            $this->_images[] = 'png';
        }
        if ($this->getFeature('jpg')) {
            $this->_images[] = 'jpg';
        }
        if ($this->getFeature('gif')) {
            $this->_images[] = 'gif';
        }
        if ($this->getFeature('wbmp')) {
            $this->_images[] = 'wbmp';
        }

        return $this->_aFeatures;
    }

    /**
     * Determine markup language expected
     *
     * @access public
     * @return __TYPE__
     */
    public function getMarkupLanguage($preferredMarkup = null)
    {
        $return = '';
        switch ($preferredMarkup) {
            case 'wml_1_1':
            case 'wml_1_2':
            case 'wml_1_3':
                $return = 'wml'; //text/vnd.wap.wml encoding="ISO-8859-15"
            case 'html_wi_imode_compact_generic':
            case 'html_wi_imode_html_1':
            case 'html_wi_imode_html_2':
            case 'html_wi_imode_html_3':
            case 'html_wi_imode_html_4':
            case 'html_wi_imode_html_5':
                $return = 'chtml'; //text/html
            case 'html_wi_oma_xhtmlmp_1_0': //application/vnd.wap.xhtml+xml
            case 'html_wi_w3_xhtmlbasic': //application/xhtml+xml DTD XHTML Basic 1.0
                $return = 'xhtml';
            case 'html_web_3_2': //text/html DTD Html 3.2 Final
            case 'html_web_4_0': //text/html DTD Html 4.01 Transitional
                $return = '';
        }
        return $return;
    }

    /**
     * Determine image format support
     *
     * @return array
     */
    public function getImageFormatSupport()
    {
        return $this->_images;
    }

    /**
     * Determine maximum image height supported
     *
     * @return int
     */
    public function getMaxImageHeight()
    {
        return $this->getFeature('max_image_height');
    }

    /**
     * Determine maximum image width supported
     *
     * @return int
     */
    public function getMaxImageWidth()
    {
        return $this->getFeature('max_image_width');
    }

    /**
     * Determine physical screen height
     *
     * @return int
     */
    public function getPhysicalScreenHeight()
    {
        return $this->getFeature('physical_screen_height');
    }

    /**
     * Determine physical screen width
     *
     * @return int
     */
    public function getPhysicalScreenWidth()
    {
        return $this->getFeature('physical_screen_width');
    }

    /**
     * Determine preferred markup
     *
     * @return string
     */
    public function getPreferredMarkup()
    {
        return $this->getFeature("markup");
    }

    /**
     * Determine X/HTML support level
     *
     * @return int
     */
    public function getXhtmlSupportLevel()
    {
        return $this->getFeature('xhtml_support_level');
    }

    /**
     * Does the device support Flash?
     *
     * @return bool
     */
    public function hasFlashSupport()
    {
        return $this->getFeature('fl_browser');
    }

    /**
     * Does the device support PDF?
     *
     * @return bool
     */
    public function hasPdfSupport()
    {
        return $this->getFeature('pdf_support');
    }

    /**
     * Does the device have an associated phone number?
     *
     * @return bool
     */
    public function hasPhoneNumber()
    {
        return $this->getFeature('can_assign_phone_number');
    }

    /**
     * Does the device support HTTPS?
     *
     * @return bool
     */
    public function httpsSupport()
    {
        return ($this->getFeature('https_support') == 'supported');
    }
}
