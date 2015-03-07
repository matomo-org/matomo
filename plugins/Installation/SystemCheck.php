<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Piwik\CliMulti;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Db\Adapter;
use Piwik\DbHelper;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\SettingsServer;
use Piwik\Url;

class SystemCheck
{
    /**
     * Get system information
     */
    public static function getSystemInformation()
    {
        global $piwik_minimumPHPVersion;

        $infos = array();

        $infos['directories'] = self::getDirectoriesWritableStatus();
        $infos['can_auto_update'] = Filechecks::canAutoUpdate();

        self::initServerFilesForSecurity();

        $infos['phpVersion_minimum'] = $piwik_minimumPHPVersion;
        $infos['phpVersion'] = PHP_VERSION;
        $infos['phpVersion_ok'] = self::isPhpVersionValid($infos['phpVersion']);

        // critical errors
        $infos['needed_extensions'] = self::getRequiredExtensions();
        $infos['missing_extensions'] = self::getRequiredExtensionsMissing();

        $infos['pdo_ok'] = self::isPhpExtensionLoaded('PDO');
        $infos['adapters'] = Adapter::getAdapters();

        $infos['needed_functions'] = self::getRequiredFunctions();
        $infos['missing_functions'] = self::getRequiredFunctionsMissing();

        // warnings
        $infos['desired_extensions'] = self::getRecommendedExtensions();
        $infos['missing_desired_extensions'] = self::getRecommendedExtensionsMissing();

        $infos['desired_functions'] = self::getRecommendedFunctions();
        $infos['missing_desired_functions'] = self::getRecommendedFunctionsMissing();

        $infos['needed_settings'] = self::getRequiredPhpSettings();
        $infos['missing_settings'] = self::getMissingPhpSettings();

        $infos['pagespeed_module_disabled_ok'] = self::isPageSpeedDisabled();

        $infos['openurl'] = Http::getTransportMethod();
        $infos['gd_ok'] = SettingsServer::isGdExtensionEnabled();
        $infos['serverVersion'] = addslashes(isset($_SERVER['SERVER_SOFTWARE']) ?: '');
        $infos['serverOs'] = @php_uname();
        $infos['serverTime'] = date('H:i:s');

        $infos['memoryMinimum'] = self::getMinimumRecommendedMemoryLimit();
        $infos['memory_ok'] = true;
        $infos['memoryCurrent'] = '';

        SettingsServer::raiseMemoryLimitIfNecessary();
        if (($memoryValue = SettingsServer::getMemoryLimitValue()) > 0) {
            $infos['memoryCurrent'] = $memoryValue . 'M';
            $infos['memory_ok'] = $memoryValue >= self::getMinimumRecommendedMemoryLimit();
        }

        $infos['isWindows'] = SettingsServer::isWindows();

        $integrityInfo = Filechecks::getFileIntegrityInformation();
        $infos['integrity'] = $integrityInfo[0];

        $infos['integrityErrorMessages'] = array();
        if (isset($integrityInfo[1])) {
            if ($infos['integrity'] == false) {
                $infos['integrityErrorMessages'][] = Piwik::translate('General_FileIntegrityWarningExplanation');
            }
            $infos['integrityErrorMessages'] = array_merge($infos['integrityErrorMessages'], array_slice($integrityInfo, 1));
        }

        $infos['timezone'] = SettingsServer::isTimezoneSupportEnabled();

        $process = new CliMulti();
        $infos['cli_process_ok'] = $process->supportsAsync();

        $infos['tracker_status'] = Common::getRequestVar('trackerStatus', 0, 'int');

        $infos['is_nfs'] = Filesystem::checkIfFileSystemIsNFS();
        $infos = self::enrichSystemChecks($infos);

        return $infos;
    }

    /**
     * This can be overriden to provide a Customised System Check.
     *
     * @api
     * @param $infos
     * @return mixed
     */
    public static function enrichSystemChecks($infos)
    {
        // determine whether there are any errors/warnings from the checks done above
        $infos['has_errors'] = false;
        $infos['has_warnings'] = false;
        if (in_array(0, $infos['directories']) // if a directory is not writable
            || !$infos['phpVersion_ok']
            || !empty($infos['missing_extensions'])
            || empty($infos['adapters'])
            || !empty($infos['missing_functions'])
        ) {
            $infos['has_errors'] = true;
        }

        if (   !empty($infos['missing_desired_extensions'])
            || !empty($infos['missing_desired_functions'])
            || !empty($infos['missing_settings'])
            || !$infos['pagespeed_module_disabled_ok']
            || !$infos['gd_ok']
            || !$infos['memory_ok']
            || !empty($infos['integrityErrorMessages'])
            || !$infos['timezone'] // if timezone support isn't available
            || $infos['tracker_status'] != 0
            || $infos['is_nfs']
        ) {
            $infos['has_warnings'] = true;
        }
        return $infos;
    }

    /**
     * @return array
     */
    protected static function getDirectoriesShouldBeWritable()
    {
        $tmpPath = StaticContainer::get('path.tmp');

        $directoriesToCheck = array(
            $tmpPath,
            $tmpPath . '/assets/',
            $tmpPath . '/cache/',
            $tmpPath . '/climulti/',
            $tmpPath . '/latest/',
            $tmpPath . '/logs/',
            $tmpPath . '/sessions/',
            $tmpPath . '/tcpdf/',
            $tmpPath . '/templates_c/',
        );

        if (!DbHelper::isInstalled()) {
            // at install, need /config to be writable (so we can create config.ini.php)
            $directoriesToCheck[] = '/config/';
        }
        return $directoriesToCheck;
    }

    /**
     * @return array
     */
    protected static function getRequiredFunctions()
    {
        return array(
            'debug_backtrace',
            'create_function',
            'eval',
            'gzcompress',
            'gzuncompress',
            'pack',
        );
    }

    /**
     * @return array
     */
    protected static function getRecommendedExtensions()
    {
        return array(
            'json',
            'libxml',
            'dom',
            'SimpleXML',
        );
    }

    /**
     * @return array
     */
    protected static function getRecommendedFunctions()
    {
        return array(
            'set_time_limit',
            'mail',
            'parse_ini_file',
            'glob',
            'gzopen',
        );
    }

    /**
     * @return array
     */
    protected static function getRequiredExtensions()
    {
        $requiredExtensions = array(
            'zlib',
            'SPL',
            'iconv',
            'json',
            'mbstring',
        );

        if (!defined('HHVM_VERSION')) {
            // HHVM provides the required subset of Reflection but lists Reflections as missing
            $requiredExtensions[] = 'Reflection';
        }

        return $requiredExtensions;
    }

    /**
     * Performs extra system checks for the 'System Check' admin page. These
     * checks are not performed during Installation.
     *
     * The following checks are performed:
     *  - Check for whether LOAD DATA INFILE can be used. The result of the check
     *    is stored in $result['load_data_infile_available']. The error message is
     *    stored in $result['load_data_infile_error'].
     *
     * - Check whether geo location is setup correctly
     *
     * @return array
     */
    public static function performAdminPageOnlySystemCheck()
    {
        $result = array();
        self::checkLoadDataInfile($result);
        self::checkGeolocation($result);
        return $result;
    }

    /**
     * Test if function exists.  Also handles case where function is disabled via Suhosin.
     *
     * @param string $functionName Function name
     * @return bool True if function exists (not disabled); False otherwise.
     */
    protected static function functionExists($functionName)
    {
        // eval() is a language construct
        if ($functionName == 'eval') {
            // does not check suhosin.executor.eval.whitelist (or blacklist)
            if (extension_loaded('suhosin')) {
                return @ini_get("suhosin.executor.disable_eval") != "1";
            }
            return true;
        }

        $exists = function_exists($functionName);
        if (extension_loaded('suhosin')) {
            $blacklist = @ini_get("suhosin.executor.func.blacklist");
            if (!empty($blacklist)) {
                $blacklistFunctions = array_map('strtolower', array_map('trim', explode(',', $blacklist)));
                return $exists && !in_array($functionName, $blacklistFunctions);
            }
        }
        return $exists;
    }

    private static function checkGeolocation(&$result)
    {
        $currentProviderId = LocationProvider::getCurrentProviderId();
        $allProviders = LocationProvider::getAllProviderInfo();
        $isRecommendedProvider = in_array($currentProviderId, array( LocationProvider\GeoIp\Php::ID, $currentProviderId == LocationProvider\GeoIp\Pecl::ID));
        $isProviderInstalled = ($allProviders[$currentProviderId]['status'] == LocationProvider::INSTALLED);

        $result['geolocation_using_non_recommended'] = $result['geolocation_ok'] = false;
        if ($isRecommendedProvider && $isProviderInstalled) {
            $result['geolocation_ok'] = true;
        } elseif ($isProviderInstalled) {
            $result['geolocation_using_non_recommended'] = true;
        }
    }

    private static function checkLoadDataInfile(&$result)
    {
        // check if LOAD DATA INFILE works
        $optionTable = Common::prefixTable('option');
        $testOptionNames = array('test_system_check1', 'test_system_check2');

        $result['load_data_infile_available'] = false;
        try {
            $result['load_data_infile_available'] = \Piwik\Db\BatchInsert::tableInsertBatch(
                $optionTable,
                array('option_name', 'option_value'),
                array(
                    array($testOptionNames[0], '1'),
                    array($testOptionNames[1], '2'),
                ),
                $throwException = true
            );
        } catch (\Exception $ex) {
            $result['load_data_infile_error'] = str_replace("\n", "<br/>", $ex->getMessage());
        }

        // delete the temporary rows that were created
        Db::exec("DELETE FROM `$optionTable` WHERE option_name IN ('" . implode("','", $testOptionNames) . "')");
    }

    protected static function initServerFilesForSecurity()
    {
        ServerFilesGenerator::createWebConfigFiles();
        ServerFilesGenerator::createHtAccessFiles();
        ServerFilesGenerator::createWebRootFiles();
    }

    /**
     * @param string $phpVersion
     * @return bool
     */
    public static function isPhpVersionValid($phpVersion)
    {
        global $piwik_minimumPHPVersion;
        return version_compare($piwik_minimumPHPVersion, $phpVersion) <= 0;
    }

    /**
     * @return array
     */
    protected static function getDirectoriesWritableStatus()
    {
        $directoriesToCheck = self::getDirectoriesShouldBeWritable();
        $directoriesWritableStatus = Filechecks::checkDirectoriesWritable($directoriesToCheck);
        return $directoriesWritableStatus;
    }

    /**
     * @return array
     */
    protected static function getLoadedExtensions()
    {
        static $extensions = null;

        if(is_null($extensions)) {
            $extensions = @get_loaded_extensions();
        }
        return $extensions;
    }


    /**
     * @param $needed_extension
     * @return bool
     */
    protected static function isPhpExtensionLoaded($needed_extension)
    {
        return in_array($needed_extension, self::getLoadedExtensions());
    }

    /**
     * @return array
     */
    protected static function getRequiredExtensionsMissing()
    {
        $missingExtensions = array();
        foreach (self::getRequiredExtensions() as $requiredExtension) {
            if (!self::isPhpExtensionLoaded($requiredExtension)) {
                $missingExtensions[] = $requiredExtension;
            }
        }

        // Special case for mbstring
        if (!function_exists('mb_get_info')
            || ((int)ini_get('mbstring.func_overload')) != 0) {
            $missingExtensions[] = 'mbstring';
        }

        return $missingExtensions;
    }

    /**
     * @return array
     */
    protected static function getRecommendedExtensionsMissing()
    {
        return array_diff(self::getRecommendedExtensions(), self::getLoadedExtensions());
    }

    /**
     * @return array
     */
    protected static function getRecommendedFunctionsMissing()
    {
        return self::getFunctionsMissing(self::getRecommendedFunctions());
    }

    /**
     * @return array
     */
    protected static function getRequiredFunctionsMissing()
    {
        return self::getFunctionsMissing(self::getRequiredFunctions());
    }

    protected static function getFunctionsMissing($functionsToTestFor)
    {
        $missingFunctions = array();
        foreach ($functionsToTestFor as $function) {
            if (!self::functionExists($function)) {
                $missingFunctions[] = $function;
            }
        }
        return $missingFunctions;
    }

    /**
     * @return mixed
     */
    protected static function getMinimumRecommendedMemoryLimit()
    {
        return Config::getInstance()->General['minimum_memory_limit'];
    }

    private static function isPhpVersionAtLeast56()
    {
       return version_compare( PHP_VERSION, '5.6', '>=');
    }

    /**
     * @return array
     */
    public static function getRequiredPhpSettings()
    {
        $requiredPhpSettings = array(
            // setting = required value
            // Note: value must be an integer only
            'session.auto_start=0',
        );

        if (self::isPhpVersionAtLeast56()) {
            // always_populate_raw_post_data must be -1
            $requiredPhpSettings[] = 'always_populate_raw_post_data=-1';
        }
        return $requiredPhpSettings;
    }

    /**
     * @return array
     */
    protected static function getMissingPhpSettings()
    {
        $missingPhpSettings = array();
        foreach(self::getRequiredPhpSettings() as $requiredSetting) {
            list($requiredSettingName, $requiredSettingValue) = explode('=', $requiredSetting);

            $currentValue = ini_get($requiredSettingName);
            $currentValue = (int)$currentValue;

            if($currentValue != $requiredSettingValue) {
                $missingPhpSettings[] = $requiredSetting;
            }
        }
        return $missingPhpSettings;
    }

    protected static function isPageSpeedDisabled()
    {
        $url = Url::getCurrentUrlWithoutQueryString() . '?module=Installation&action=getEmptyPageForSystemCheck';

        try {
            $page = Http::sendHttpRequest($url,
                $timeout = 1,
                $userAgent = null,
                $destinationPath = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $byteRange = false,

                // Return headers
                $getExtendedInfo = true
            );
        } catch(\Exception $e) {

            // If the test failed, we assume Page speed is not enabled
            return true;
        }

        $headers = $page['headers'];

        return !self::isPageSpeedHeaderFound($headers);
    }

    /**
     * @param $headers
     * @return bool
     */
    protected static function isPageSpeedHeaderFound($headers)
    {
        return isset($headers['X-Mod-Pagespeed']) || isset($headers['X-Page-Speed']);
    }
}