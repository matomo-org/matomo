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
namespace Piwik;

use Exception;
use Piwik\Db\Adapter;
use Piwik\Db\Schema;
use Piwik\Db;
use Piwik\Log\ScreenFormatter;
use Piwik\Plugin;
use Piwik\Plugins\UsersManager\API;
use Piwik\Session;
use Piwik\Tracker\Cache;
use Piwik\Tracker;
use Piwik\Tracker\GoalManager;
use Piwik\View;
use Zend_Registry;

/**
 * @see core/Translate.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Translate.php';

/**
 * Main piwik helper class.
 * Contains static functions you can call from the plugins.
 *
 * @package Piwik
 */
class Piwik
{
    const COMPRESSED_FILE_LOCATION = '/tmp/assets/';

    /**
     * Piwik periods
     * @var array
     */
    public static $idPeriods = array(
        'day' => 1,
        'week' => 2,
        'month' => 3,
        'year' => 4,
        'range' => 5,
    );

    /**
     * @see getKnownSegmentsToArchive
     *
     * @var array
     */
    public static $cachedKnownSegmentsToArchive = null;

    const LABEL_ID_GOAL_IS_ECOMMERCE_CART = 'ecommerceAbandonedCart';
    const LABEL_ID_GOAL_IS_ECOMMERCE_ORDER = 'ecommerceOrder';

    /**
     * Should we process and display Unique Visitors?
     * -> Always process for day/week/month periods
     * For Year and Range, only process if it was enabled in the config file,
     *
     * @param string $periodLabel  Period label (e.g., 'day')
     * @return bool
     */
    static public function isUniqueVisitorsEnabled($periodLabel)
    {
        $generalSettings = Config::getInstance()->General;

        $settingName = "enable_processing_unique_visitors_$periodLabel";
        $result = !empty($generalSettings[$settingName]) && $generalSettings[$settingName] == 1;

        // check enable_processing_unique_visitors_year_and_range for backwards compatibility
        if (($periodLabel == 'year' || $periodLabel == 'range')
            && isset($generalSettings['enable_processing_unique_visitors_year_and_range'])
        ) {
            $result |= $generalSettings['enable_processing_unique_visitors_year_and_range'] == 1;
        }

        return $result;
    }

    /**
     * Returns true if Segmentation is allowed for this user
     *
     * @return bool
     */
    public static function isSegmentationEnabled()
    {
        return !Piwik::isUserIsAnonymous()
        || Config::getInstance()->General['anonymous_user_enable_use_segments_API'];
    }

    /**
     * Uninstallation helper
     */
    static public function uninstall()
    {
        Schema::getInstance()->dropTables();
    }

    /**
     * Returns true if Piwik is installed
     *
     * @since 0.6.3
     *
     * @return bool  True if installed; false otherwise
     */
    static public function isInstalled()
    {
        try {
            return Schema::getInstance()->hasTables();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Called on Core install, update, plugin enable/disable
     * Will clear all cache that could be affected by the change in configuration being made
     */
    static public function deleteAllCacheOnUpdate()
    {
        AssetManager::removeMergedAssets();
        View::clearCompiledTemplates();
        Cache::deleteTrackerCache();
    }

    /**
     * Cache for result of getPiwikUrl.
     * Can be overwritten for testing purposes only.
     *
     * @var string
     */
    static public $piwikUrlCache = null;

    /**
     * Returns the cached the Piwik URL, eg. http://demo.piwik.org/ or http://example.org/piwik/
     * If not found, then tries to cache it and returns the value.
     *
     * If the Piwik URL changes (eg. Piwik moved to new server), the value will automatically be refreshed in the cache.
     *
     * @return string
     */
    static public function getPiwikUrl()
    {
        // Only set in tests
        if (self::$piwikUrlCache !== null) {
            return self::$piwikUrlCache;
        }

        $key = 'piwikUrl';
        $url = Piwik_GetOption($key);
        if (SettingsServer::isPhpCliMode()
            // in case archive.php is triggered with domain localhost
            || SettingsServer::isArchivePhpTriggered()
            || defined('PIWIK_MODE_ARCHIVE')
        ) {
            return $url;
        }

        $currentUrl = Common::sanitizeInputValue(Url::getCurrentUrlWithoutFileName());

        if (empty($url)
            // if URL changes, always update the cache
            || $currentUrl != $url
        ) {
            if (strlen($currentUrl) >= strlen('http://a/')) {
                Piwik_SetOption($key, $currentUrl, $autoLoad = true);
            }
            $url = $currentUrl;
        }
        return $url;
    }


    /*
     * PHP environment settings
     */

    /**
     * Logging and error handling
     *
     * @var bool|null
     */
    public static $shouldLog = null;

    /**
     * Log a message
     *
     * @param string $message
     */
    static public function log($message = '')
    {
        if (is_null(self::$shouldLog)) {
            self::$shouldLog = self::shouldLoggerLog();
            // It is possible that the logger is not setup:
            // - Tracker request, and debug disabled,
            // - and some scheduled tasks call code that tries and log something
            try {
                \Zend_Registry::get('logger_message');
            } catch (Exception $e) {
                self::$shouldLog = false;
            }
        }
        if (self::$shouldLog) {
            \Zend_Registry::get('logger_message')->logEvent($message);
        }
    }

    /**
     * Returns if logging should work
     * @return bool
     */
    static public function shouldLoggerLog()
    {
        try {
            $shouldLog = (SettingsServer::isPhpCliMode()
                    || Config::getInstance()->log['log_only_when_cli'] == 0)
                &&
                (Config::getInstance()->log['log_only_when_debug_parameter'] == 0
                    || isset($_REQUEST['debug']));
        } catch (Exception $e) {
            $shouldLog = false;
        }
        return $shouldLog;
    }

    /**
     * Trigger E_USER_ERROR with optional message
     *
     * @param string $message
     */
    static public function error($message = '')
    {
        trigger_error($message, E_USER_ERROR);
    }

    /**
     * Display the message in a nice red font with a nice icon
     * ... and dies
     *
     * @param string $message
     */
    static public function exitWithErrorMessage($message)
    {
        $output = "<style>a{color:red;}</style>\n" .
            "<div style='color:red;font-family:Georgia;font-size:120%'>" .
            "<p><img src='plugins/Zeitgeist/images/error_medium.png' style='vertical-align:middle; float:left;padding:20 20 20 20' />" .
            $message .
            "</p></div>";
        print(ScreenFormatter::getFormattedString($output));
        exit;
    }

    /*
     * Amounts, Percentages, Currency, Time, Math Operations, and Pretty Printing
     */

    /**
     * Returns a list of currency symbols
     *
     * @return array  array( currencyCode => symbol, ... )
     */
    static public function getCurrencyList()
    {
        static $currenciesList = null;
        if (is_null($currenciesList)) {
            require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Currencies.php';
            $currenciesList = $GLOBALS['Piwik_CurrencyList'];
        }
        return $currenciesList;
    }

    /**
     * Computes the division of i1 by i2. If either i1 or i2 are not number, or if i2 has a value of zero
     * we return 0 to avoid the division by zero.
     *
     * @param number $i1
     * @param number $i2
     * @return number The result of the division or zero
     */
    static public function secureDiv($i1, $i2)
    {
        if (is_numeric($i1) && is_numeric($i2) && floatval($i2) != 0) {
            return $i1 / $i2;
        }
        return 0;
    }

    /**
     * Safely compute a percentage.  Return 0 to avoid division by zero.
     *
     * @param number $dividend
     * @param number $divisor
     * @param int $precision
     * @return number
     */
    static public function getPercentageSafe($dividend, $divisor, $precision = 0)
    {
        if ($divisor == 0) {
            return 0;
        }
        return round(100 * $dividend / $divisor, $precision);
    }

    /**
     * Get currency symbol for a site
     *
     * @param int $idSite
     * @return string
     */
    static public function getCurrency($idSite)
    {
        $symbols = self::getCurrencyList();
        $site = new Site($idSite);
        $currency = $site->getCurrency();
        if (isset($symbols[$currency])) {
            return $symbols[$currency][0];
        }

        return '';
    }

    /**
     * For the given value, based on the column name, will apply: pretty time, pretty money
     * @param int $idSite
     * @param string $columnName
     * @param mixed $value
     * @param bool $htmlAllowed
     * @return string
     */
    static public function getPrettyValue($idSite, $columnName, $value, $htmlAllowed)
    {
        // Display time in human readable
        if (strpos($columnName, 'time') !== false) {
            // Little hack: Display 15s rather than 00:00:15, only for "(avg|min|max)_generation_time"
            $timeAsSentence = (substr($columnName, -16) == '_time_generation');
            return Piwik::getPrettyTimeFromSeconds($value, $timeAsSentence);
        }
        // Add revenue symbol to revenues
        if (strpos($columnName, 'revenue') !== false && strpos($columnName, 'evolution') === false) {
            return Piwik::getPrettyMoney($value, $idSite, $htmlAllowed);
        }
        // Add % symbol to rates
        if (strpos($columnName, '_rate') !== false) {
            if (strpos($value, "%") === false) {
                return $value . "%";
            }
        }
        return $value;
    }

    /**
     * Pretty format monetary value for a site
     *
     * @param int|string $value
     * @param int $idSite
     * @param bool $htmlAllowed
     * @return string
     */
    static public function getPrettyMoney($value, $idSite, $htmlAllowed = true)
    {
        $currencyBefore = self::getCurrency($idSite);

        $space = ' ';
        if ($htmlAllowed) {
            $space = '&nbsp;';
        }

        $currencyAfter = '';
        // manually put the currency symbol after the amount for euro
        // (maybe more currencies prefer this notation?)
        if (in_array($currencyBefore, array('€', 'kr'))) {
            $currencyAfter = $space . $currencyBefore;
            $currencyBefore = '';
        }

        // if the input is a number (it could be a string or INPUT form),
        // and if this number is not an int, we round to precision 2
        if (is_numeric($value)) {
            if ($value == round($value)) {
                // 0.0 => 0
                $value = round($value);
            } else {
                $precision = GoalManager::REVENUE_PRECISION;
                $value = sprintf("%01." . $precision . "f", $value);
            }
        }
        $prettyMoney = $currencyBefore . $space . $value . $currencyAfter;
        return $prettyMoney;
    }

    /**
     * Pretty format a memory size value
     *
     * @param number $size       size in bytes
     * @param string $unit       The specific unit to use, if any. If null, the unit is determined by $size.
     * @param int $precision  The precision to use when rounding.
     * @return string
     */
    static public function getPrettySizeFromBytes($size, $unit = null, $precision = 1)
    {
        if ($size == 0) {
            return '0 M';
        }

        $units = array('B', 'K', 'M', 'G', 'T');
        foreach ($units as $currentUnit) {
            if ($size >= 1024 && $unit != $currentUnit) {
                $size = $size / 1024;
            } else {
                break;
            }
        }
        return round($size, $precision) . " " . $currentUnit;
    }

    /**
     * Pretty format a time
     *
     * @param int $numberOfSeconds
     * @param bool $displayTimeAsSentence  If set to true, will output "5min 17s", if false "00:05:17"
     * @param bool $isHtml
     * @param bool $round to the full seconds
     * @return string
     */
    static public function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = true, $isHtml = true, $round = false)
    {
        $numberOfSeconds = $round ? (int)$numberOfSeconds : (float)$numberOfSeconds;

        // Display 01:45:17 time format
        if ($displayTimeAsSentence === false) {
            $hours = floor($numberOfSeconds / 3600);
            $minutes = floor(($reminder = ($numberOfSeconds - $hours * 3600)) / 60);
            $seconds = floor($reminder - $minutes * 60);
            $time = sprintf("%02s", $hours) . ':' . sprintf("%02s", $minutes) . ':' . sprintf("%02s", $seconds);
            $centiSeconds = ($numberOfSeconds * 100) % 100;
            if ($centiSeconds) {
                $time .= '.' . sprintf("%02s", $centiSeconds);
            }
            return $time;
        }
        $secondsInYear = 86400 * 365.25;
        $years = floor($numberOfSeconds / $secondsInYear);
        $minusYears = $numberOfSeconds - $years * $secondsInYear;
        $days = floor($minusYears / 86400);

        $minusDays = $numberOfSeconds - $days * 86400;
        $hours = floor($minusDays / 3600);

        $minusDaysAndHours = $minusDays - $hours * 3600;
        $minutes = floor($minusDaysAndHours / 60);

        $seconds = $minusDaysAndHours - $minutes * 60;
        $precision = ($seconds > 0 && $seconds < 0.01 ? 3 : 2);
        $seconds = round($seconds, $precision);

        if ($years > 0) {
            $return = sprintf(Piwik_Translate('General_YearsDays'), $years, $days);
        } elseif ($days > 0) {
            $return = sprintf(Piwik_Translate('General_DaysHours'), $days, $hours);
        } elseif ($hours > 0) {
            $return = sprintf(Piwik_Translate('General_HoursMinutes'), $hours, $minutes);
        } elseif ($minutes > 0) {
            $return = sprintf(Piwik_Translate('General_MinutesSeconds'), $minutes, $seconds);
        } else {
            $return = sprintf(Piwik_Translate('General_Seconds'), $seconds);
        }
        if ($isHtml) {
            return str_replace(' ', '&nbsp;', $return);
        }
        return $return;
    }

    /**
     * Gets a prettified string representation of a number. The result will have
     * thousands separators and a decimal point specific to the current locale.
     *
     * @param number $value
     * @return string
     */
    static public function getPrettyNumber($value)
    {
        $locale = localeconv();

        $decimalPoint = $locale['decimal_point'];
        $thousandsSeparator = $locale['thousands_sep'];

        return number_format($value, 0, $decimalPoint, $thousandsSeparator);
    }

    /**
     * Returns the Javascript code to be inserted on every page to track
     *
     * @param int $idSite
     * @param string $piwikUrl  http://path/to/piwik/directory/
     * @return string
     */
    static public function getJavascriptCode($idSite, $piwikUrl)
    {
        $jsCode = file_get_contents(PIWIK_INCLUDE_PATH . "/plugins/Zeitgeist/templates/javascriptCode.tpl");
        $jsCode = htmlentities($jsCode);
        preg_match('~^(http|https)://(.*)$~D', $piwikUrl, $matches);
        $piwikUrl = @$matches[2];
        $jsCode = str_replace('{$idSite}', $idSite, $jsCode);
        $jsCode = str_replace('{$piwikUrl}', Common::sanitizeInputValue($piwikUrl), $jsCode);
        return $jsCode;
    }

    /**
     * Generate a title for image tags
     *
     * @return string
     */
    static public function getRandomTitle()
    {
        static $titles = array(
            'Web analytics',
            'Real Time Web Analytics',
            'Analytics',
            'Real Time Analytics',
            'Analytics in Real time',
            'Open Source Analytics',
            'Open Source Web Analytics',
            'Free Website Analytics',
            'Free Web Analytics',
            'Analytics Platform',
        );
        $id = abs(intval(md5(Url::getCurrentHost())));
        $title = $titles[$id % count($titles)];
        return $title;
    }

    /**
     * Number of websites to show in the Website selector
     *
     * @return int
     */
    static public function getWebsitesCountToDisplay()
    {
        $count = max(Config::getInstance()->General['site_selector_max_sites'],
            Config::getInstance()->General['autocomplete_min_sites']);
        return (int)$count;
    }

    /**
     * Segments to pre-process
     *
     * @return string
     */
    static public function getKnownSegmentsToArchive()
    {
        if (self::$cachedKnownSegmentsToArchive === null) {
            $segments = Config::getInstance()->Segments;
            $cachedResult = isset($segments['Segments']) ? $segments['Segments'] : array();

            Piwik_PostEvent('Piwik.getKnownSegmentsToArchiveAllSites', array(&$cachedResult));

            self::$cachedKnownSegmentsToArchive = array_unique($cachedResult);
        }

        return self::$cachedKnownSegmentsToArchive;
    }

    static public function getKnownSegmentsToArchiveForSite($idSite)
    {
        $segments = array();
        Piwik_PostEvent('Piwik.getKnownSegmentsToArchiveForSite', array(&$segments, $idSite));
        return $segments;
    }

    /*
     * Access
     */

    /**
     * Get current user email address
     *
     * @return string
     */
    static public function getCurrentUserEmail()
    {
        if (!Piwik::isUserIsSuperUser()) {
            $user = API::getInstance()->getUser(Piwik::getCurrentUserLogin());
            return $user['email'];
        }
        return self::getSuperUserEmail();
    }

    /**
     * Returns Super User login
     *
     * @return string
     */
    static public function getSuperUserLogin()
    {
        return Access::getInstance()->getSuperUserLogin();
    }

    /**
     * Returns Super User email
     *
     * @return string
     */
    static public function getSuperUserEmail()
    {
        $superuser = Config::getInstance()->superuser;
        return $superuser['email'];
    }

    /**
     * Get current user login
     *
     * @return string  login ID
     */
    static public function getCurrentUserLogin()
    {
        return Access::getInstance()->getLogin();
    }

    /**
     * Get current user's token auth
     *
     * @return string  Token auth
     */
    static public function getCurrentUserTokenAuth()
    {
        return Access::getInstance()->getTokenAuth();
    }

    /**
     * Returns true if the current user is either the super user, or the user $theUser
     * Used when modifying user preference: this usually requires super user or being the user itself.
     *
     * @param string $theUser
     * @return bool
     */
    static public function isUserIsSuperUserOrTheUser($theUser)
    {
        try {
            self::checkUserIsSuperUserOrTheUser($theUser);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check that current user is either the specified user or the superuser
     *
     * @param string $theUser
     * @throws NoAccessException  if the user is neither the super user nor the user $theUser
     */
    static public function checkUserIsSuperUserOrTheUser($theUser)
    {
        try {
            if (Piwik::getCurrentUserLogin() !== $theUser) {
                // or to the super user
                Piwik::checkUserIsSuperUser();
            }
        } catch (NoAccessException $e) {
            throw new NoAccessException(Piwik_Translate('General_ExceptionCheckUserIsSuperUserOrTheUser', array($theUser)));
        }
    }

    /**
     * Returns true if the current user is the Super User
     *
     * @return bool
     */
    static public function isUserIsSuperUser()
    {
        try {
            self::checkUserIsSuperUser();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Is user the anonymous user?
     *
     * @return bool  True if anonymouse; false otherwise
     */
    static public function isUserIsAnonymous()
    {
        return Piwik::getCurrentUserLogin() == 'anonymous';
    }

    /**
     * Checks if user is not the anonymous user.
     *
     * @throws NoAccessException  if user is anonymous.
     */
    static public function checkUserIsNotAnonymous()
    {
        if (self::isUserIsAnonymous()) {
            throw new NoAccessException(Piwik_Translate('General_YouMustBeLoggedIn'));
        }
    }

    /**
     * Helper method user to set the current as Super User.
     * This should be used with great care as this gives the user all permissions.
     *
     * @param bool $bool  true to set current user as super user
     */
    static public function setUserIsSuperUser($bool = true)
    {
        Access::getInstance()->setSuperUser($bool);
    }

    /**
     * Check that user is the superuser
     *
     * @throws Exception if not the superuser
     */
    static public function checkUserIsSuperUser()
    {
        Access::getInstance()->checkUserIsSuperUser();
    }

    /**
     * Returns true if the user has admin access to the sites
     *
     * @param mixed $idSites
     * @return bool
     */
    static public function isUserHasAdminAccess($idSites)
    {
        try {
            self::checkUserHasAdminAccess($idSites);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has admin access to the sites
     *
     * @param mixed $idSites
     * @throws Exception if user doesn't have admin access to the sites
     */
    static public function checkUserHasAdminAccess($idSites)
    {
        Access::getInstance()->checkUserHasAdminAccess($idSites);
    }

    /**
     * Returns true if the user has admin access to any sites
     *
     * @return bool
     */
    static public function isUserHasSomeAdminAccess()
    {
        try {
            self::checkUserHasSomeAdminAccess();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has admin access to any sites
     *
     * @throws Exception if user doesn't have admin access to any sites
     */
    static public function checkUserHasSomeAdminAccess()
    {
        Access::getInstance()->checkUserHasSomeAdminAccess();
    }

    /**
     * Returns true if the user has view access to the sites
     *
     * @param mixed $idSites
     * @return bool
     */
    static public function isUserHasViewAccess($idSites)
    {
        try {
            self::checkUserHasViewAccess($idSites);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has view access to the sites
     *
     * @param mixed $idSites
     * @throws Exception if user doesn't have view access to sites
     */
    static public function checkUserHasViewAccess($idSites)
    {
        Access::getInstance()->checkUserHasViewAccess($idSites);
    }

    /**
     * Returns true if the user has view access to any sites
     *
     * @return bool
     */
    static public function isUserHasSomeViewAccess()
    {
        try {
            self::checkUserHasSomeViewAccess();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user has view access to any sites
     *
     * @throws Exception if user doesn't have view access to any sites
     */
    static public function checkUserHasSomeViewAccess()
    {
        Access::getInstance()->checkUserHasSomeViewAccess();
    }

    /*
     * Current module, action, plugin
     */

    /**
     * Returns the name of the Login plugin currently being used.
     * Must be used since it is not allowed to hardcode 'Login' in URLs
     * in case another Login plugin is being used.
     *
     * @return string
     */
    static public function getLoginPluginName()
    {
        return \Zend_Registry::get('auth')->getName();
    }

    /**
     * Returns the plugin currently being used to display the page
     *
     * @return Plugin
     */
    static public function getCurrentPlugin()
    {
        return \Piwik\PluginsManager::getInstance()->getLoadedPlugin(Piwik::getModule());
    }

    /**
     * Returns the current module read from the URL (eg. 'API', 'UserSettings', etc.)
     *
     * @return string
     */
    static public function getModule()
    {
        return Common::getRequestVar('module', '', 'string');
    }

    /**
     * Returns the current action read from the URL
     *
     * @return string
     */
    static public function getAction()
    {
        return Common::getRequestVar('action', '', 'string');
    }

    /**
     * Helper method used in API function to introduce array elements in API parameters.
     * Array elements can be passed by comma separated values, or using the notation
     * array[]=value1&array[]=value2 in the URL.
     * This function will handle both cases and return the array.
     *
     * @param array|string $columns
     * @return array
     */
    static public function getArrayFromApiParameter($columns)
    {
        if (empty($columns)) {
            return array();
        }
        if (is_array($columns)) {
            return $columns;
        }
        $array = explode(',', $columns);
        $array = array_unique($array);
        return $array;
    }

    /**
     * Redirect to module (and action)
     *
     * @param string $newModule   Target module
     * @param string $newAction   Target action
     * @param array $parameters  Parameters to modify in the URL
     * @return bool  false if the URL to redirect to is already this URL
     */
    static public function redirectToModule($newModule, $newAction = '', $parameters = array())
    {
        $newUrl = 'index.php' . Url::getCurrentQueryStringWithParametersModified(
                array('module' => $newModule, 'action' => $newAction)
                + $parameters
            );
        Url::redirectToUrl($newUrl);
    }

    /*
     * Global database object
     */

    /**
     * Create database object and connect to database
     * @param array|null $dbInfos
     */
    static public function createDatabaseObject($dbInfos = null)
    {
        $config = Config::getInstance();

        if (is_null($dbInfos)) {
            $dbInfos = $config->database;
        }

        Piwik_PostEvent('Reporting.getDatabaseConfig', array(&$dbInfos));

        $dbInfos['profiler'] = $config->Debug['enable_sql_profiler'];

        $db = null;
        Piwik_PostEvent('Reporting.createDatabase', array(&$db));
        if (is_null($db)) {
            $adapter = $dbInfos['adapter'];
            $db = @Adapter::factory($adapter, $dbInfos);
        }
        \Zend_Registry::set('db', $db);
    }

    /**
     * Disconnect from database
     */
    static public function disconnectDatabase()
    {
        \Zend_Registry::get('db')->closeConnection();
    }

    /**
     * Checks the database server version against the required minimum
     * version.
     *
     * @see config/global.ini.php
     * @since 0.4.4
     * @throws Exception if server version is less than the required version
     */
    static public function checkDatabaseVersion()
    {
        \Zend_Registry::get('db')->checkServerVersion();
    }

    /**
     * Check database connection character set is utf8.
     *
     * @return bool  True if it is (or doesn't matter); false otherwise
     */
    static public function isDatabaseConnectionUTF8()
    {
        return \Zend_Registry::get('db')->isConnectionUTF8();
    }

    /*
     * Global log object
     */

    /*
     * User input validation
     */

    /**
     * Returns true if the email is a valid email
     *
     * @param string $email
     * @return bool
     */
    static public function isValidEmailString($email)
    {
        return (preg_match('/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]{2,7}$/D', $email) > 0);
    }

    /**
     * Returns true if the login is valid.
     * Warning: does not check if the login already exists! You must use UsersManager_API->userExists as well.
     *
     * @param string $userLogin
     * @throws Exception
     * @return bool
     */
    static public function checkValidLoginString($userLogin)
    {
        if (!self::isChecksEnabled()
            && !empty($userLogin)
        ) {
            return;
        }
        $loginMinimumLength = 3;
        $loginMaximumLength = 100;
        $l = strlen($userLogin);
        if (!($l >= $loginMinimumLength
            && $l <= $loginMaximumLength
            && (preg_match('/^[A-Za-z0-9_.@+-]*$/D', $userLogin) > 0))
        ) {
            throw new Exception(Piwik_TranslateException('UsersManager_ExceptionInvalidLoginFormat', array($loginMinimumLength, $loginMaximumLength)));
        }
    }

    /**
     * Should Piwik check that the login & password have minimum length and valid characters?
     *
     * @return bool  True if checks enabled; false otherwise
     */
    static public function isChecksEnabled()
    {
        return Config::getInstance()->General['disable_checks_usernames_attributes'] == 0;
    }

    /*
     * Database and table definition methods
     */

    /**
     * Is the schema available?
     *
     * @return bool  True if schema is available; false otherwise
     */
    static public function isAvailable()
    {
        return Schema::getInstance()->isAvailable();
    }

    /**
     * Get the SQL to create a specific Piwik table
     *
     * @param string $tableName
     * @return string  SQL
     */
    static public function getTableCreateSql($tableName)
    {
        return Schema::getInstance()->getTableCreateSql($tableName);
    }

    /**
     * Get the SQL to create Piwik tables
     *
     * @return array  array of strings containing SQL
     */
    static public function getTablesCreateSql()
    {
        return Schema::getInstance()->getTablesCreateSql();
    }

    /**
     * Create database
     *
     * @param string|null $dbName
     */
    static public function createDatabase($dbName = null)
    {
        Schema::getInstance()->createDatabase($dbName);
    }

    /**
     * Drop database
     */
    static public function dropDatabase()
    {
        Schema::getInstance()->dropDatabase();
    }

    /**
     * Create all tables
     */
    static public function createTables()
    {
        Schema::getInstance()->createTables();
    }

    /**
     * Creates an entry in the User table for the "anonymous" user.
     */
    static public function createAnonymousUser()
    {
        Schema::getInstance()->createAnonymousUser();
    }

    /**
     * Truncate all tables
     */
    static public function truncateAllTables()
    {
        Schema::getInstance()->truncateAllTables();
    }

    /**
     * Drop specific tables
     *
     * @param array $doNotDelete  Names of tables to not delete
     */
    static public function dropTables($doNotDelete = array())
    {
        Schema::getInstance()->dropTables($doNotDelete);
    }

    /**
     * Names of all the prefixed tables in piwik
     * Doesn't use the DB
     *
     * @return array  Table names
     */
    static public function getTablesNames()
    {
        return Schema::getInstance()->getTablesNames();
    }

    /**
     * Get list of tables installed
     *
     * @param bool $forceReload  Invalidate cache
     * @return array  Tables installed
     */
    static public function getTablesInstalled($forceReload = true)
    {
        return Schema::getInstance()->getTablesInstalled($forceReload);
    }

    /**
     * Utility function that checks if an object type is in a set of types.
     *
     * @param mixed $o
     * @param array $types List of class names that $o is expected to be one of.
     * @throws Exception if $o is not an instance of the types contained in $types.
     */
    static public function checkObjectTypeIs($o, $types)
    {
        foreach ($types as $type) {
            if ($o instanceof $type) {
                return;
            }
        }

        $oType = is_object($o) ? get_class($o) : gettype($o);
        throw new Exception("Invalid variable type '$oType', expected one of following: " . implode(', ', $types));
    }

    /**
     * Returns true if an array is an associative array, false if otherwise.
     *
     * This method determines if an array is associative by checking that the
     * first element's key is 0, and that each successive element's key is
     * one greater than the last.
     *
     * @param array $array
     * @return bool
     */
    static public function isAssociativeArray($array)
    {
        reset($array);
        if (!is_numeric(key($array))
            || key($array) != 0
        ) // first key must be 0
        {
            return true;
        }

        // check that each key is == next key - 1 w/o actually indexing the array
        while (true) {
            $current = key($array);

            next($array);
            $next = key($array);

            if ($next === null) {
                break;
            } else if ($current + 1 != $next) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the option name of the option that stores the time the archive.php
     * script was last run.
     *
     * @param string $period
     * @param string $idSite
     * @return string
     */
    public static function getArchiveCronLastRunOptionName($period, $idSite)
    {
        return "lastRunArchive" . $period . "_" . $idSite;
    }

    /**
     * Returns the class name of an object without its namespace.
     * 
     * @param mixed|string $object
     * @return string
     */
    public static function getUnnamespacedClassName($object)
    {
        $className = is_string($object) ? $object : get_class($object);
        $parts = explode('\\', $className);
        return end($parts);
    }
}
