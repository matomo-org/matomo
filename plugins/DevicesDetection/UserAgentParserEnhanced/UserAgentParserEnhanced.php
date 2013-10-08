<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package DevicesDetection
 */
//yml parser
require_once(PIWIK_INCLUDE_PATH . '/libs/spyc.php');

class UserAgentParserEnhanced
{
    public static $deviceTypes = array('desktop', 'smartphone', 'tablet', 'feature phone', 'console', 'tv', 'car browser');

    public static $deviceBrands = array(
        'AC' => 'Acer',
        'AI' => 'Airness',
        'AL' => 'Alcatel',
        'AO' => 'Amoi',
        'AP' => 'Apple',
        'AU' => 'Asus',
        'AV' => 'Avvio',
        'AX' => 'Audiovox',
        'BE' => 'Becker',
        'BI' => 'Bird',
        'BL' => 'Beetel',
        'BQ' => 'BenQ',
        'BS' => 'BenQ-Siemens',
        'CK' => 'Cricket',
        'CL' => 'Compal',
        'CT' => 'Capitel',
        'DB' => 'Dbtel',
        'DC' => 'DoCoMo',
        'DI' => 'Dicam',
        'DL' => 'Dell',
        'DP' => 'Dopod',
        'EC' => 'Ericsson',
        'EI' => 'Ezio',
        'ER' => 'Ericy',
        'ET' => 'eTouch',
        'EZ' => 'Ezze',
        'FL' => 'Fly',
        'GI' => 'Gionee',
        'GO' => 'Google',
        'GR' => 'Gradiente',
        'GU' => 'Grundig',
        'HA' => 'Haier',
        'HP' => 'HP',
        'HT' => 'HTC',
        'HU' => 'Huawei',
        'IK' => 'iKoMo',
        'IM' => 'i-mate',
        'IN' => 'Innostream',
        'IO' => 'i-mobile',
        'IQ' => 'INQ',
        'KA' => 'Karbonn',
        'KD' => 'KDDI',
        'KN' => 'Kindle',
        'KO' => 'Konka',
        'KY' => 'Kyocera',
        'LA' => 'Lanix',
        'LC' => 'LCT',
        'LE' => 'Lenovo',
        'LG' => 'LG',
        'LU' => 'LGUPlus',
        'MI' => 'MicroMax',
        'MO' => 'Mio',
        'MR' => 'Motorola',
        'MS' => 'Microsoft',
        'MT' => 'Mitsubishi',
        'MY' => 'MyPhone',
        'NE' => 'NEC',
        'NG' => 'NGM',
        'NI' => 'Nintendo',
        'NK' => 'Nokia',
        'NW' => 'Newgen',
        'NX' => 'Nexian',
        'OD' => 'Onda',
        'OP' => 'OPPO',
        'OR' => 'Orange',
        'OT' => 'O2',
        'PA' => 'Panasonic',
        'PH' => 'Philips',
        'PM' => 'Palm',
        'PO' => 'phoneOne',
        'PT' => 'Pantech',
        'QT' => 'Qtek',
        'RM' => 'RIM',
        'RO' => 'Rover',
        'SA' => 'Samsung',
        'SD' => 'Sega',
        'SE' => 'Sony Ericsson',
        'SF' => 'Softbank',
        'SG' => 'Sagem',
        'SH' => 'Sharp',
        'SI' => 'Siemens',
        'SN' => 'Sendo',
        'SO' => 'Sony',
        'SP' => 'Spice',
        'SY' => 'Sanyo',
        'TA' => 'Tesla',
        'TC' => 'TCL',
        'TE' => 'Telit',
        'TH' => 'TiPhone',
        'TI' => 'TIANYU',
        'TM' => 'T-Mobile',
        'TO' => 'Toplux',
        'TS' => 'Toshiba',
        'UT' => 'UTStarcom',
        'VD' => 'Videocon',
        'VE' => 'Vertu',
        'VI' => 'Vitelcom',
        'VK' => 'VK Mobile',
        'VO' => 'Voxtel',
        'WB' => 'Web TV',
        'WE' => 'WellcoM',
        'WO' => 'Wonu',
        'XX' => 'Unknown',
        'ZO' => 'Zonda',
        'ZT' => 'ZTE',
    );
    public static $osShorts = array(
        'AIX'                  => 'AIX',
        'Android'              => 'AND',
        'Apple TV'             => 'ATV',
        'Arch Linux'           => 'ARL',
        'BackTrack'            => 'BTR',
        'Bada'                 => 'SBA',
        'BlackBerry OS'        => 'BLB',
        'BlackBerry Tablet OS' => 'QNX',
        'Bot'                  => 'BOT',
        'Brew'                 => 'BMP',
        'CentOS'               => 'CES',
        'Chrome OS'            => 'COS',
        'Debian'               => 'DEB',
        'DragonFly'            => 'DFB',
        'Fedora'               => 'FED',
        'Firefox OS'           => 'FOS',
        'FreeBSD'              => 'BSD',
        'Gentoo'               => 'GNT',
        'Google TV'            => 'GTV',
        'HP-UX'                => 'HPX',
        'IRIX'                 => 'IRI',
        'Knoppix'              => 'KNO',
        'Kubuntu'              => 'KBT',
        'Linux'                => 'LIN',
        'Lubuntu'              => 'LBT',
        'Mac'                  => 'MAC',
        'Mandriva'             => 'MDR',
        'MeeGo'                => 'SMG',
        'Mint'                 => 'MIN',
        'NetBSD'               => 'NBS',
        'Nintendo'             => 'WII',
        'Nintendo Mobile'      => 'NDS',
        'OS/2'                 => 'OS2',
        'OSF1'                 => 'T64',
        'OpenBSD'              => 'OBS',
        'PlayStation'          => 'PSP',
        'PlayStation 3'        => 'PS3',
        'Presto'               => 'PRS',
        'Puppy'                => 'PPY',
        'Red Hat'              => 'RHT',
        'SUSE'                 => 'SSE',
        'Slackware'            => 'SLW',
        'Solaris'              => 'SOS',
        'Syllable'             => 'SYL',
        'Symbian'              => 'SYM',
        'Symbian OS'           => 'SYS',
        'Symbian OS Series 40' => 'S40',
        'Symbian OS Series 60' => 'S60',
        'Symbian^3'            => 'SY3',
        'Talkatone'            => 'TKT',
        'Tizen'                => 'TIZ',
        'Ubuntu'               => 'UBT',
        'WebTV'                => 'WTV',
        'WinWAP'               => 'WWP',
        'Windows'              => 'WIN',
        'Windows 2000'         => 'W2K',
        'Windows 3.1'          => 'W31',
        'Windows 7'            => 'WI7',
        'Windows 8'            => 'WI8',
        'Windows 95'           => 'W95',
        'Windows 98'           => 'W98',
        'Windows CE'           => 'WCE',
        'Windows ME'           => 'WME',
        'Windows Mobile'       => 'WMO',
        'Windows NT'           => 'WNT',
        'Windows Phone'        => 'WPH',
        'Windows RT'           => 'WRT',
        'Windows Server 2003'  => 'WS3',
        'Windows Vista'        => 'WVI',
        'Windows XP'           => 'WXP',
        'Xbox'                 => 'XBX',
        'Xubuntu'              => 'XBT',
        'YunOs'                => 'YNS',
        'iOS'                  => 'IOS',
        'palmOS'               => 'POS',
        'webOS'                => 'WOS'
    );
    protected static $desktopOsArray = array('IBM', 'Linux', 'Mac', 'Unix', 'Windows');
    public static $osFamilies = array(
        'Android'               => array('AND'),
        'Apple TV'              => array('ATV'),
        'BlackBerry'            => array('BLB'),
        'Bot'                   => array('BOT'),
        'Brew'                  => array('BMP'),
        'Chrome OS'             => array('COS'),
        'Firefox OS'            => array('FOS'),
        'Gaming Console'        => array('WII', 'PS3'),
        'Google TV'             => array('GTV'),
        'IBM'                   => array('OS2'),
        'iOS'                   => array('IOS'),
        'Linux'                 => array('LIN', 'ARL', 'DEB', 'KNO', 'MIN', 'UBT', 'KBT', 'XBT', 'LBT', 'FED', 'RHT', 'MDR', 'GNT', 'SLW', 'SSE', 'PPY', 'CES', 'BTR', 'YNS', 'PRS'),
        'Mac'                   => array('MAC'),
        'Mobile Gaming Console' => array('PSP', 'NDS', 'XBX'),
        'Other Mobile'          => array('WOS', 'POS', 'QNX', 'SBA', 'TIZ'),
        'Simulator'             => array('TKT', 'WWP'),
        'Symbian'               => array('SYM', 'SYS', 'SY3', 'S60', 'S40', 'SMG'),
        'Unix'                  => array('SOS', 'AIX', 'HPX', 'BSD', 'NBS', 'OBS', 'DFB', 'SYL', 'IRI', 'T64'),
        'WebTV'                 => array('WTV'),
        'Windows'               => array('WI8', 'WI7', 'WVI', 'WS3', 'WXP', 'W2K', 'WNT', 'WME', 'W98', 'W95', 'WRT', 'W31', 'WIN'),
        'Windows Mobile'        => array('WPH', 'WMO', 'WCE')
    );
    public static $browserFamilies = array(
        'Android Browser'    => array('AN'),
        'BlackBerry Browser' => array('BB'),
        'Chrome'             => array('CH', 'CM', 'CI', 'CF', 'CR', 'RM'),
        'Firefox'            => array('FF', 'FE', 'SX', 'FB', 'PX', 'MB'),
        'Internet Explorer'  => array('IE', 'IM'),
        'Konqueror'          => array('KO'),
        'NetFront'           => array('NF'),
        'Nokia Browser'      => array('NB'),
        'Opera'              => array('OP', 'OM', 'OI'),
        'Safari'             => array('SF', 'MF')
    );
    public static $browsers = array(
        'AB' => 'ABrowse',
        'AM' => 'Amaya',
        'AN' => 'Android Browser',
        'AR' => 'Arora',
        'AV' => 'Amiga Voyager',
        'AW' => 'Amiga Aweb',
        'BB' => 'BlackBerry Browser',
        'BD' => 'Baidu Browser',
        'BE' => 'Beonex',
        'BX' => 'BrowseX',
        'CA' => 'Camino',
        'CF' => 'Chrome Frame',
        'CH' => 'Chrome',
        'CI' => 'Chrome Mobile iOS',
        'CK' => 'Conkeror',
        'CM' => 'Chrome Mobile',
        'CO' => 'CometBird',
        'CR' => 'Chromium',
        'CS' => 'Cheshire',
        'DF' => 'Dolphin',
        'DI' => 'Dillo',
        'EL' => 'Elinks',
        'EP' => 'Epiphany',
        'FB' => 'Firebird',
        'FD' => 'Fluid',
        'FE' => 'Fennec',
        'FF' => 'Firefox',
        'FL' => 'Flock',
        'FN' => 'Fireweb Navigator',
        'GA' => 'Galeon',
        'GE' => 'Google Earth',
        'HJ' => 'HotJava',
        'IB' => 'IBrowse',
        'IC' => 'iCab',
        'IE' => 'Internet Explorer',
        'IM' => 'IE Mobile',
        'IR' => 'Iron',
        'JS' => 'Jasmine',
        'KI' => 'Kindle Browser',
        'KM' => 'K-meleon',
        'KO' => 'Konqueror',
        'KP' => 'Kapiko',
        'KZ' => 'Kazehakase',
        'LG' => 'Lightning',
        'LI' => 'Links',
        'LX' => 'Lynx',
        'MB' => 'MicroB',
        'MC' => 'NCSA Mosaic',
        'MF' => 'Mobile Safari',
        'MI' => 'Midori',
        'MS' => 'Mobile Silk',
        'MX' => 'Maxthon',
        'NB' => 'Nokia Browser',
        'NF' => 'NetFront',
        'NL' => 'NetFront Life',
        'NS' => 'Netscape',
        'OB' => 'Obigo',
        'OI' => 'Opera Mini',
        'OM' => 'Opera Mobile',
        'OP' => 'Opera',
        'OV' => 'Openwave Mobile Browser',
        'OW' => 'OmniWeb',
        'PL' => 'Palm Blazer',
        'PR' => 'Palm Pre',
        'PX' => 'Phoenix',
        'RK' => 'Rekonq',
        'RM' => 'RockMelt',
        'SF' => 'Safari',
        'SM' => 'SeaMonkey',
        'SN' => 'Snowshoe',
        'SX' => 'Swiftfox',
        'TZ' => 'Tizen Browser',
        'UC' => 'UC Browser',
        'WO' => 'wOSBrowser',
        'YA' => 'Yandex Browser'
    );

    const UNKNOWN = "UNK";
    protected static $regexesDir = '/regexes/';
    protected static $osRegexesFile = 'oss.yml';
    protected static $browserRegexesFile = 'browsers.yml';
    protected static $mobileRegexesFile = 'mobiles.yml';
    protected $userAgent;
    protected $os;
    protected $browser;
    protected $device;
    protected $brand;
    protected $model;
    protected $debug = false;

    public function __construct($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    protected function getOsRegexes()
    {
        return Spyc::YAMLLoad(dirname(__FILE__) . self::$regexesDir . self::$osRegexesFile);
    }

    protected function getBrowserRegexes()
    {
        return Spyc::YAMLLoad(dirname(__FILE__) . self::$regexesDir . self::$browserRegexesFile);
    }

    protected function getMobileRegexes()
    {
        return Spyc::YAMLLoad(dirname(__FILE__) . self::$regexesDir . self::$mobileRegexesFile);
    }

    public function parse()
    {
        $this->parseOs();
        if ($this->isBot() || $this->isSimulator())
            return;

        $this->parseBrowser();

        if ($this->isMobile()) {
            $this->parseMobile();
        } else {
            $this->device = array_search('desktop', self::$deviceTypes);
        }
        if ($this->debug) {
            var_export($this->brand, $this->model, $this->device);
        }
    }

    protected function parseOs()
    {
        foreach ($this->getOsRegexes() as $osRegex) {
            $matches = $this->matchUserAgent($osRegex['regex']);
            if ($matches)
                break;
        }

        if (!$matches)
            return;

        if (in_array($osRegex['name'], self::$osShorts)) {
            $short = self::$osShorts[$osRegex['name']];
        } else {
            $short = 'UNK';
        }

        $this->os = array(
            'name'       => $this->buildOsName($osRegex['name'], $matches),
            'short_name' => $short,
            'version'    => $this->buildOsVersion($osRegex['version'], $matches)
        );

        if (array_key_exists($this->os['name'], self::$osShorts)) {
            $this->os['short_name'] = self::$osShorts[$this->os['name']];
        }
    }

    protected function parseBrowser()
    {
        foreach ($this->getBrowserRegexes() as $browserRegex) {
            $matches = $this->matchUserAgent($browserRegex['regex']);
            if ($matches)
                break;
        }

        if (!$matches)
            return;

        if (in_array($browserRegex['name'], self::$browsers)) {
            $short = array_search($browserRegex['name'], self::$browsers);
        } else {
            $short = 'XX';
        }

        $this->browser = array(
            'name'       => $this->buildBrowserName($browserRegex['name'], $matches),
            'short_name' => $short,
            'version'    => $this->buildBrowserVersion($browserRegex['version'], $matches)
        );
    }

    protected function parseMobile()
    {
        $mobileRegexes = $this->getMobileRegexes();
        $this->parseBrand($mobileRegexes);
        $this->parseModel($mobileRegexes);
    }

    protected function parseBrand($mobileRegexes)
    {
        foreach ($mobileRegexes as $brand => $mobileRegex) {
            $matches = $this->matchUserAgent($mobileRegex['regex']);
            if ($matches)
                break;
        }

        if (!$matches)
            return;
        $this->brand = array_search($brand, self::$deviceBrands);
        $this->fullName = $brand;

        if (isset($mobileRegex['device'])) {
            $this->device = array_search($mobileRegex['device'], self::$deviceTypes);
        }

        if (isset($mobileRegex['model'])) {
            $this->model = $this->buildModel($mobileRegex['model'], $matches);
        }
    }

    protected function parseModel($mobileRegexes)
    {
        if (empty($this->brand) || !empty($this->model))
            return;

        foreach ($mobileRegexes[$this->fullName]['models'] as $modelRegex) {
            $matches = $this->matchUserAgent($modelRegex['regex']);
            if ($matches)
                break;
        }

        if (!$matches) {
            return;
        }

        $this->model = $this->buildModel($modelRegex['model'], $matches);

        if (isset($modelRegex['device'])) {
            $this->device = array_search($modelRegex['device'], self::$deviceTypes);
        }
    }

    protected function matchUserAgent($regex)
    {
        $regex = '/' . str_replace('/', '\/', $regex) . '/i';

        if (preg_match($regex, $this->userAgent, $matches)) {
            return $matches;
        }

        return false;
    }

    protected function buildOsName($osName, $matches)
    {
        return $this->buildByMatch($osName, $matches);
    }

    protected function buildOsVersion($osVersion, $matches)
    {
        $osVersion = $this->buildByMatch($osVersion, $matches);

        $osVersion = $this->buildByMatch($osVersion, $matches, '2');

        $osVersion = str_replace('_', '.', $osVersion);

        return $osVersion;
    }

    protected function buildBrowserName($browserName, $matches)
    {
        return $this->buildByMatch($browserName, $matches);
    }

    protected function buildBrowserVersion($browserVersion, $matches)
    {
        $browserVersion = $this->buildByMatch($browserVersion, $matches);

        $browserVersion = $this->buildByMatch($browserVersion, $matches, '2');

        $browserVersion = str_replace('_', '.', $browserVersion);

        return $browserVersion;
    }

    protected function buildModel($model, $matches)
    {
        $model = $this->buildByMatch($model, $matches);

        $model = $this->buildByMatch($model, $matches, '2');

        $model = $this->buildModelExceptions($model);

        $model = str_replace('_', ' ', $model);

        return $model;
    }

    protected function buildModelExceptions($model)
    {
        if ($this->brand == 'O2') {
            $model = preg_replace('/([a-z])([A-Z])/', '$1 $2', $model);
            $model = ucwords(str_replace('_', ' ', $model));
        }

        return $model;
    }

    /**
     * This method is used in this class for processing results of pregmatch
     * results into string containing recognized information.
     *
     * General algorithm:
     * Parsing UserAgent string consists of trying to match it against list of
     * regular expressions for three different information:
     * browser + version,
     * OS + version,
     * device manufacturer + model.
     *
     * After match has been found iteration stops, and results are processed
     * by buildByMatch.
     * As $item we get decoded name (name of browser, name of OS, name of manufacturer).
     * In array $match we recieve preg_match results containing whole string matched at index 0
     * and following matches in further indexes. Desired action now is to concatenate
     * decoded name ($item) with matches found. First step is to append first found match,
     * which is located in index=1 (that's why $nb is 1 by default).
     * In other cases, where whe know that preg_match may return more than 1 result,
     * we call buildByMatch with $nb = 2 or more, depending on what will be returned from
     * regular expression.
     *
     * Example:
     * We are parsing UserAgent of Firefox 20.0 browser.
     * UserAgentParserEnhanced calls buildBrowserName() and buildBrowserVersion() in order
     * to retrieve those information.
     * In buildBrowserName() we only have one call of buildByMatch, where passed argument
     * is regular expression testing given string for browser name. In this case, we are only
     * interrested in first hit, so no $nb parameter will be set to 1. After finding match, and calling
     * buildByMatch - we will receive just the name of browser.
     *
     * Also after decoding browser we will get list of regular expressions for this browser name
     * testing UserAgent string for version number. Again we iterate over this list, and after finding first
     * occurence - we break loop and proceed to build by match. Since browser regular expressions can
     * contain two hits (major version and minor version) in function buildBrowserVersion() we have
     * two calls to buildByMatch, one without 3rd parameter, and second with $nb set to 2.
     * This way we can retrieve version number, and assign it to object property.
     *
     * In case of mobiles.yml this schema slightly varies, but general idea is the same.
     *
     * @param string $item
     * @param array $matches
     * @param int|string $nb
     * @return string type
     */
    protected function buildByMatch($item, $matches, $nb = '1')
    {
        if (strpos($item, '$' . $nb) === false)
            return $item;

        $replace = isset($matches[$nb]) ? $matches[$nb] : '';
        return trim(str_replace('$' . $nb, $replace, $item));
    }

    public function isBot()
    {
        $decodedFamily = '';
        if (in_array($this->getOs('name'), self::$osShorts)) {
            $osShort = self::$osShorts[$this->getOs('name')];
        } else {
            $osShort = '';
        }
        foreach (self::$osFamilies as $family => $familyOs) {
            if (in_array($osShort, $familyOs)) {
                $decodedFamily = $family;
                break;
            }
        }

        return $decodedFamily == 'Bot';
    }

    public function isSimulator()
    {
        $decodedFamily = '';
        if (in_array($this->getOs('name'), self::$osShorts)) {
            $osShort = self::$osShorts[$this->getOs('name')];
        } else {
            $osShort = '';
        }
        foreach (self::$osFamilies as $family => $familyOs) {
            if (in_array($osShort, $familyOs)) {
                $decodedFamily = $family;
                break;
            }
        }
        return $decodedFamily == 'Simulator';
    }

    public function isMobile()
    {
        return !$this->isDesktop();
    }

    public function isDesktop()
    {
        $osName = $this->getOs('name');
        if (empty($osName) || empty(self::$osShorts[$osName])) {
            return false;
        }

        $osShort = self::$osShorts[$osName];
        foreach (self::$osFamilies as $family => $familyOs) {
            if (in_array($osShort, $familyOs)) {
                $decodedFamily = $family;
                break;
            }
        }
        return in_array($decodedFamily, self::$desktopOsArray);
    }

    public function getOs($attr = '')
    {
        if ($attr == '') {
            return $this->os;
        }

        if (!isset($this->os[$attr])) {
            return self::UNKNOWN;
        }

        return $this->os[$attr];
    }

    public function getBrowser($attr = '')
    {
        if ($attr == '') {
            return $this->browser;
        }

        if (!isset($this->browser[$attr])) {
            return self::UNKNOWN;
        }

        return $this->browser[$attr];
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public static function getOsFamily($osLabel)
    {
        $osShortName = substr($osLabel, 0, 3);

        foreach (self::$osFamilies as $osFamily => $osShortNames) {
            if (in_array($osShortName, $osShortNames)) {
                return $osFamily;
            }
        }

        return 'Other';
    }

    public static function getBrowserFamily($browserLabel)
    {
        foreach (self::$browserFamilies as $browserFamily => $browserShortNames) {
            if (in_array($browserLabel, $browserShortNames)) {
                return $browserFamily;
            }
        }

        return 'Other';
    }

    public static function getOsNameFromId($os, $ver = false)
    {
        $osFullName = array_search($os, self::$osShorts);
        if ($osFullName) {
            if (in_array($os, self::$osFamilies['Windows'])) {
                return $osFullName;
            } else {
                return trim($osFullName . " " . $ver);
            }
        }
        return false;
    }

}