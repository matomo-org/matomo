<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
//yml parser
require_once(PIWIK_INCLUDE_PATH . '/libs/spyc.php');

class UserAgentParserEnhanced
{
    public static $deviceTypes = array(
        'desktop',          // 0
        'smartphone',       // 1
        'tablet',           // 2
        'feature phone',    // 3
        'console',          // 4
        'tv',               // 5
        'car browser',      // 6
        'smart display',    // 7
        'camera'            // 8
    );

    public static $deviceBrands = array(
        'AC' => 'Acer',
        'AI' => 'Airness',
        'AL' => 'Alcatel',
        'AN' => 'Arnova',
        'AO' => 'Amoi',
        'AP' => 'Apple',
        'AR' => 'Archos',
        'AU' => 'Asus',
        'AV' => 'Avvio',
        'AX' => 'Audiovox',
        'BB' => 'BBK',
        'BE' => 'Becker',
        'BI' => 'Bird',
        'BL' => 'Beetel',
        'BO' => 'BangOlufsen',
        'BQ' => 'BenQ',
        'BS' => 'BenQ-Siemens',
        'CA' => 'Cat',
        'CK' => 'Cricket',
        'CL' => 'Compal',
        'CN' => 'CnM',
        'CR' => 'CreNova',
        'CT' => 'Capitel',
        'CO' => 'Coolpad',
        'CU' => 'Cube',
        'DE' => 'Denver',
        'DB' => 'Dbtel',
        'DC' => 'DoCoMo',
        'DI' => 'Dicam',
        'DL' => 'Dell',
        'DM' => 'DMM',
        'DP' => 'Dopod',
        'EC' => 'Ericsson',
        'EI' => 'Ezio',
        'ER' => 'Ericy',
        'ET' => 'eTouch',
        'EZ' => 'Ezze',
        'FL' => 'Fly',
        'GD' => 'Gemini',
        'GI' => 'Gionee',
        'GG' => 'Gigabyte',
        'GO' => 'Google',
        'GR' => 'Gradiente',
        'GU' => 'Grundig',
        'HA' => 'Haier',
        'HP' => 'HP',
        'HT' => 'HTC',
        'HU' => 'Huawei',
        'HX' => 'Humax',
        'IA' => 'Ikea',
        'IK' => 'iKoMo',
        'IM' => 'i-mate',
        'IN' => 'Innostream',
        'IX' => 'Intex',
        'IO' => 'i-mobile',
        'IQ' => 'INQ',
        'IT' => 'Intek',
        'IV' => 'Inverto',
        'JI' => 'Jiayu',
        'JO' => 'Jolla',
        'KA' => 'Karbonn',
        'KD' => 'KDDI',
        'KN' => 'Kindle',
        'KO' => 'Konka',
        'KT' => 'K-Touch',
        'KH' => 'KT-Tech',
        'KY' => 'Kyocera',
        'LA' => 'Lanix',
        'LC' => 'LCT',
        'LE' => 'Lenovo',
        'LG' => 'LG',
        'LO' => 'Loewe',
        'LU' => 'LGUPlus',
        'MA' => 'Manta Multimedia',
        'MD' => 'Medion',
        'ME' => 'Metz',
        'MI' => 'MicroMax',
        'MK' => 'MediaTek',
        'MO' => 'Mio',
        'MR' => 'Motorola',
        'MS' => 'Microsoft',
        'MT' => 'Mitsubishi',
        'MY' => 'MyPhone',
        'NE' => 'NEC',
        'NG' => 'NGM',
        'NI' => 'Nintendo',
        'NK' => 'Nokia',
        'NN' => 'Nikon',
        'NW' => 'Newgen',
        'NX' => 'Nexian',
        'OD' => 'Onda',
        'OP' => 'OPPO',
        'OR' => 'Orange',
        'OT' => 'O2',
        'OU' => 'OUYA',
        'PA' => 'Panasonic',
        'PE' => 'PEAQ',
        'PH' => 'Philips',
        'PL' => 'Polaroid',
        'PM' => 'Palm',
        'PO' => 'phoneOne',
        'PT' => 'Pantech',
        'PP' => 'PolyPad',
        'PR' => 'Prestigio',
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
        'SU' => 'SuperSonic',
        'SV' => 'Selevision',
        'SY' => 'Sanyo',
        'SM' => 'Symphony',
        'SR' => 'Smart',
        'TA' => 'Tesla',
        'TC' => 'TCL',
        'TE' => 'Telit',
        'TH' => 'TiPhone',
        'TI' => 'TIANYU',
        'TL' => 'Telefunken',
        'TM' => 'T-Mobile',
        'TN' => 'Thomson',
        'TO' => 'Toplux',
        'TS' => 'Toshiba',
        'TT' => 'TechnoTrend',
        'TV' => 'TVC',
        'TX' => 'TechniSat',
        'TZ' => 'teXet',
        'UT' => 'UTStarcom',
        'VD' => 'Videocon',
        'VE' => 'Vertu',
        'VI' => 'Vitelcom',
        'VK' => 'VK Mobile',
        'VS' => 'ViewSonic',
        'VT' => 'Vestel',
        'VO' => 'Voxtel',
        'VW' => 'Videoweb',
        'WB' => 'Web TV',
        'WE' => 'WellcoM',
        'WO' => 'Wonu',
        'WX' => 'Woxter',
        'XI' => 'Xiaomi',
        'XX' => 'Unknown',
        'YU' => 'Yuandao',
        'ZO' => 'Zonda',
        'ZT' => 'ZTE',
    );
    public static $osShorts = array(
        'AIX'                  => 'AIX',
        'Android'              => 'AND',
        'AmigaOS'              => 'AMG',
        'Apple TV'             => 'ATV',
        'Arch Linux'           => 'ARL',
        'BackTrack'            => 'BTR',
        'Bada'                 => 'SBA',
        'BeOS'                 => 'BEO',
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
        'Haiku OS'             => 'HAI',
        'IRIX'                 => 'IRI',
        'Inferno'              => 'INF',
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
        'PlayStation Portable' => 'PSP',
        'PlayStation'          => 'PS3',
        'Presto'               => 'PRS',
        'Puppy'                => 'PPY',
        'Red Hat'              => 'RHT',
        'RISC OS'              => 'ROS',
        'Sabayon'              => 'SAB',
        'SUSE'                 => 'SSE',
        'Sailfish OS'          => 'SAF',
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
    protected static $desktopOsArray = array('AmigaOS', 'IBM', 'Linux', 'Mac', 'Unix', 'Windows', 'BeOS');
    public static $osFamilies = array(
        'Android'               => array('AND'),
        'AmigaOS'               => array('AMG'),
        'Apple TV'              => array('ATV'),
        'BlackBerry'            => array('BLB'),
        'Bot'                   => array('BOT'),
        'Brew'                  => array('BMP'),
        'BeOS'                  => array('BEO', 'HAI'),
        'Chrome OS'             => array('COS'),
        'Firefox OS'            => array('FOS'),
        'Gaming Console'        => array('WII', 'PS3'),
        'Google TV'             => array('GTV'),
        'IBM'                   => array('OS2'),
        'iOS'                   => array('IOS'),
        'RISC OS'               => array('ROS'),
        'Linux'                 => array('LIN', 'ARL', 'DEB', 'KNO', 'MIN', 'UBT', 'KBT', 'XBT', 'LBT', 'FED', 'RHT', 'MDR', 'GNT', 'SAB', 'SLW', 'SSE', 'PPY', 'CES', 'BTR', 'YNS', 'PRS', 'SAF'),
        'Mac'                   => array('MAC'),
        'Mobile Gaming Console' => array('PSP', 'NDS', 'XBX'),
        'Other Mobile'          => array('WOS', 'POS', 'QNX', 'SBA', 'TIZ', 'SMG'),
        'Simulator'             => array('TKT', 'WWP'),
        'Symbian'               => array('SYM', 'SYS', 'SY3', 'S60', 'S40'),
        'Unix'                  => array('SOS', 'AIX', 'HPX', 'BSD', 'NBS', 'OBS', 'DFB', 'SYL', 'IRI', 'T64', 'INF'),
        'WebTV'                 => array('WTV'),
        'Windows'               => array('WI7', 'WI8', 'WVI', 'WS3', 'WXP', 'W2K', 'WNT', 'WME', 'W98', 'W95', 'WRT', 'W31', 'WIN'),
        'Windows Mobile'        => array('WPH', 'WMO', 'WCE')
    );
    public static $browserFamilies = array(
        'Android Browser'    => array('AN'),
        'BlackBerry Browser' => array('BB'),
        'Chrome'             => array('CH', 'CD', 'CM', 'CI', 'CF', 'CN', 'CR', 'CP', 'RM'),
        'Firefox'            => array('FF', 'FE', 'SX', 'FB', 'PX', 'MB'),
        'Internet Explorer'  => array('IE', 'IM'),
        'Konqueror'          => array('KO'),
        'NetFront'           => array('NF'),
        'Nokia Browser'      => array('NB', 'NO', 'NV'),
        'Opera'              => array('OP', 'OM', 'OI', 'ON'),
        'Safari'             => array('SF', 'MF'),
        'Sailfish Browser'   => array('SA')
    );
    public static $browsers = array(
        'AA' => 'Avant Browser',
        'AB' => 'ABrowse',
        'AG' => 'ANTGalio',
        'AM' => 'Amaya',
        'AN' => 'Android Browser',
        'AR' => 'Arora',
        'AV' => 'Amiga Voyager',
        'AW' => 'Amiga Aweb',
        'BB' => 'BlackBerry Browser',
        'BD' => 'Baidu Browser',
        'BE' => 'Beonex',
        'BJ' => 'Bunjalloo',
        'BX' => 'BrowseX',
        'CA' => 'Camino',
        'CD' => 'Comodo Dragon',
        'CX' => 'Charon',
        'CF' => 'Chrome Frame',
        'CH' => 'Chrome',
        'CI' => 'Chrome Mobile iOS',
        'CK' => 'Conkeror',
        'CM' => 'Chrome Mobile',
        'CN' => 'CoolNovo',
        'CO' => 'CometBird',
        'CP' => 'ChromePlus',
        'CR' => 'Chromium',
        'CS' => 'Cheshire',
        'DF' => 'Dolphin',
        'DI' => 'Dillo',
        'EL' => 'Elinks',
        'EP' => 'Epiphany',
        'ES' => 'Espial TV Browser',
        'FB' => 'Firebird',
        'FD' => 'Fluid',
        'FE' => 'Fennec',
        'FF' => 'Firefox',
        'FL' => 'Flock',
        'FN' => 'Fireweb Navigator',
        'GA' => 'Galeon',
        'GE' => 'Google Earth',
        'HJ' => 'HotJava',
        'IA' => 'Iceape',
        'IB' => 'IBrowse',
        'IC' => 'iCab',
        'ID' => 'IceDragon',
        'IW' => 'Iceweasel',
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
        'LS' => 'Lunascape',
        'LX' => 'Lynx',
        'MB' => 'MicroB',
        'MC' => 'NCSA Mosaic',
        'ME' => 'Mercury',
        'MF' => 'Mobile Safari',
        'MI' => 'Midori',
        'MS' => 'Mobile Silk',
        'MX' => 'Maxthon',
        'NB' => 'Nokia Browser',
        'NO' => 'Nokia OSS Browser',
        'NV' => 'Nokia Ovi Browser',
        'NF' => 'NetFront',
        'NL' => 'NetFront Life',
        'NP' => 'NetPositive',
        'NS' => 'Netscape',
        'OB' => 'Obigo',
        'OI' => 'Opera Mini',
        'OM' => 'Opera Mobile',
        'OP' => 'Opera',
        'ON' => 'Opera Next',
        'OR' => 'Oregano',
        'OV' => 'Openwave Mobile Browser',
        'OW' => 'OmniWeb',
        'PL' => 'Palm Blazer',
        'PM' => 'Pale Moon',
        'PR' => 'Palm Pre',
        'PU' => 'Puffin',
        'PW' => 'Palm WebPro',
        'PX' => 'Phoenix',
        'PO' => 'Polaris',
        'RK' => 'Rekonq',
        'RM' => 'RockMelt',
        'SA' => 'Sailfish Browser',
        'SF' => 'Safari',
        'SL' => 'Sleipnir',
        'SM' => 'SeaMonkey',
        'SN' => 'Snowshoe',
        'SX' => 'Swiftfox',
        'TB' => 'Thunderbird',
        'TZ' => 'Tizen Browser',
        'UC' => 'UC Browser',
        'WE' => 'WebPositive',
        'WO' => 'wOSBrowser',
        'YA' => 'Yandex Browser',
        'XI' => 'Xiino'
    );

    const UNKNOWN = "UNK";
    protected static $regexesDir = '/regexes/';
    protected static $osRegexesFile = 'oss.yml';
    protected static $browserRegexesFile = 'browsers.yml';
    protected static $mobileRegexesFile = 'mobiles.yml';
    protected static $televisionRegexesFile = 'televisions.yml';
    protected $userAgent;
    protected $os = '';
    protected $browser = '';
    protected $device = '';
    protected $brand = '';
    protected $model = '';
    protected $debug = false;

    /**
     * @var \Piwik\CacheFile
     */
    protected $cache = null;

    public function __construct($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    protected function getOsRegexes()
    {
        static $regexOs;
        if(empty($regexOs)) {
            $regexOs = $this->getRegexList('os', self::$osRegexesFile);
        }
        return $regexOs;
    }

    protected function getBrowserRegexes()
    {
        static $regexBrowser;
        if (empty($regexBrowser)) {
            $regexBrowser = $this->getRegexList('browser', self::$browserRegexesFile);
        }
        return $regexBrowser;
    }

    protected function getMobileRegexes()
    {
        static $regexMobile;
        if (empty($regexMobile)) {
            $regexMobile = $this->getRegexList('mobile', self::$mobileRegexesFile);
        }
        return $regexMobile;
    }

    protected function getTelevisionRegexes()
    {
        static $regexTvs;
        if (empty($regexTvs)) {
            $regexTvs = $this->getRegexList('tv', self::$televisionRegexesFile);
        }
        return $regexTvs;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    protected function saveParsedYmlInCache($type, $data)
    {
        if (!empty($this->cache) && method_exists($this->cache, 'set')) {
            $this->cache->set($type, serialize($data));
        }
    }

    protected function getParsedYmlFromCache($type)
    {
        $data = null;
        if (!empty($this->cache) && method_exists($this->cache, 'get')) {
            $data = $this->cache->get($type);
            if (!empty($data)) {
                $data = unserialize($data);
            }
        }
        return $data;
    }


    public function parse()
    {
        $this->parseOs();
        if ($this->isBot() || $this->isSimulator())
            return;

        $this->parseBrowser();

        if($this->isHbbTv()) {
            $this->parseTelevision();
        } else {
            $this->parseMobile();
        }

        if (empty($this->device) && $this->isHbbTv()) {
            $this->device = array_search('tv', self::$deviceTypes);
        } else if (empty($this->device) && $this->isDesktop()) {
            $this->device = array_search('desktop', self::$deviceTypes);
        }

        /**
         * Android up to 3.0 was designed for smartphones only. But as 3.0, which was tablet only, was published
         * too late, there were a bunch of tablets running with 2.x
         * With 4.0 the two trees were merged and it is for smartphones and tablets
         *
         * So were are expecting that all devices running Android < 2 are smartphones
         * Devices running Android 3.X are tablets. Device type of Android 2.X and 4.X+ are unknown
         */
        if (empty($this->device) && $this->getOs('short_name') == 'AND' && $this->getOs('version') != '') {
            if (version_compare($this->getOs('version'), '2.0') == -1) {
                $this->device = array_search('smartphone', self::$deviceTypes);
            } else if (version_compare($this->getOs('version'), '3.0') >= 0 AND version_compare($this->getOs('version'), '4.0') == -1) {
                $this->device = array_search('tablet', self::$deviceTypes);
            }
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

        $name  = $this->buildOsName($osRegex['name'], $matches);
        $short = 'UNK';

        foreach (self::$osShorts AS $osName => $osShort) {
            if (strtolower($name) == strtolower($osName)) {
                $name  = $osName;
                $short = $osShort;
            }
        }

        $this->os = array(
            'name'       => $name,
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

        $name  = $this->buildBrowserName($browserRegex['name'], $matches);
        $short = 'XX';

        foreach (self::$browsers AS $browserShort => $browserName) {
            if (strtolower($name) == strtolower($browserName)) {
                $name  = $browserName;
                $short = $browserShort;
            }
        }

        $this->browser = array(
            'name'       => $name,
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

    protected function parseTelevision()
    {
        $televisionRegexes = $this->getTelevisionRegexes();
        $this->parseBrand($televisionRegexes);
        $this->parseModel($televisionRegexes);
    }

    protected function parseBrand($deviceRegexes)
    {
        foreach ($deviceRegexes as $brand => $mobileRegex) {
            $matches = $this->matchUserAgent($mobileRegex['regex']);
            if ($matches)
                break;
        }

        if (!$matches)
            return;

        $brandId = array_search($brand, self::$deviceBrands);
        if($brandId === false) {
            throw new Exception("The brand with name '$brand' should be listed in the deviceBrands array.");
        }
        $this->brand = $brandId;
        $this->fullName = $brand;

        if (isset($mobileRegex['device'])) {
            $this->device = array_search($mobileRegex['device'], self::$deviceTypes);
        }

        if (isset($mobileRegex['model'])) {
            $this->model = $this->buildModel($mobileRegex['model'], $matches);
        }
    }

    protected function parseModel($deviceRegexes)
    {
        if (empty($this->brand) || !empty($this->model) || empty($deviceRegexes[$this->fullName]['models']))
            return;

        foreach ($deviceRegexes[$this->fullName]['models'] as $modelRegex) {
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
        $regex = '/(?:^|[^A-Z_-])(?:' . str_replace('/', '\/', $regex) . ')/i';

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
        return $this->getOsFamily($this->getOs('short_name')) == 'Bot';
    }

    public function isSimulator()
    {
        return $this->getOsFamily($this->getOs('short_name')) == 'Simulator';
    }

    public function isHbbTv()
    {
        $regex = 'HbbTV/([1-9]{1}(\.[0-9]{1}){1,2})';
        return $this->matchUserAgent($regex);
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

    /**
     * @param $osLabel
     * @return bool|string If false, "Unknown"
     */
    public static function getOsFamily($osLabel)
    {
        foreach (self::$osFamilies as $family => $labels) {
            if (in_array($osLabel, $labels)) {
                return $family;
            }
        }
        return false;
    }

    /**
     * @param $browserLabel
     * @return bool|string If false, "Unknown"
     */
    public static function getBrowserFamily($browserLabel)
    {
        foreach (self::$browserFamilies as $browserFamily => $browserLabels) {
            if (in_array($browserLabel, $browserLabels)) {
                return $browserFamily;
            }
        }
        return false;
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

    static public function getInfoFromUserAgent($ua)
    {
        $userAgentParserEnhanced = new UserAgentParserEnhanced($ua);
        $userAgentParserEnhanced->parse();

        $osFamily = $userAgentParserEnhanced->getOsFamily($userAgentParserEnhanced->getOs('short_name'));
        $browserFamily = $userAgentParserEnhanced->getBrowserFamily($userAgentParserEnhanced->getBrowser('short_name'));
        $device = $userAgentParserEnhanced->getDevice();

        $deviceName = $device === '' ? '' : UserAgentParserEnhanced::$deviceTypes[$device];
        $processed = array(
            'user_agent'     => $userAgentParserEnhanced->getUserAgent(),
            'os'             => array(
                'name'       => $userAgentParserEnhanced->getOs('name'),
                'short_name' => $userAgentParserEnhanced->getOs('short_name'),
                'version'    => $userAgentParserEnhanced->getOs('version'),
            ),
            'browser'        => array(
                'name'       => $userAgentParserEnhanced->getBrowser('name'),
                'short_name' => $userAgentParserEnhanced->getBrowser('short_name'),
                'version'    => $userAgentParserEnhanced->getBrowser('version'),
            ),
            'device'         => array(
                'type'       => $deviceName,
                'brand'      => $userAgentParserEnhanced->getBrand(),
                'model'      => $userAgentParserEnhanced->getModel(),
            ),
            'os_family'      => $osFamily !== false ? $osFamily : 'Unknown',
            'browser_family' => $browserFamily !== false ? $browserFamily : 'Unknown',
        );
        return $processed;
    }

    protected function getRegexList($type, $regexesFile)
    {
        $regexList = $this->getParsedYmlFromCache($type);
        if (empty($regexList)) {
            $regexList = Spyc::YAMLLoad(dirname(__FILE__) . self::$regexesDir . $regexesFile);
            $this->saveParsedYmlInCache($type, $regexList);
        }
        return $regexList;
    }

}
