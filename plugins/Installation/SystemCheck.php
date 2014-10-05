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
use Piwik\Db;
use Piwik\Db\Adapter;
use Piwik\DbHelper;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\SettingsServer;

class SystemCheck
{

    /**
     * Get system information
     */
    public static function getSystemInformation()
    {
        global $piwik_minimumPHPVersion;
        $minimumMemoryLimit = Config::getInstance()->General['minimum_memory_limit'];

        $infos = array();

        $directoriesToCheck = array(
            '/tmp/',
            '/tmp/assets/',
            '/tmp/cache/',
            '/tmp/climulti/',
            '/tmp/latest/',
            '/tmp/logs/',
            '/tmp/sessions/',
            '/tmp/tcpdf/',
            '/tmp/templates_c/',
        );

        if (!DbHelper::isInstalled()) {
            // at install, need /config to be writable (so we can create config.ini.php)
            $directoriesToCheck[] = '/config/';
        }

        $infos['directories'] = Filechecks::checkDirectoriesWritable($directoriesToCheck);

        $infos['can_auto_update'] = Filechecks::canAutoUpdate();

        self::initServerFilesForSecurity();

        $infos['phpVersion_minimum'] = $piwik_minimumPHPVersion;
        $infos['phpVersion'] = PHP_VERSION;
        $infos['phpVersion_ok'] = self::isPhpVersionValid($infos['phpVersion']);

        // critical errors
        $extensions = @get_loaded_extensions();
        $needed_extensions = array(
            'zlib',
            'SPL',
            'iconv',
            'json',
            'mbstring',
        );
        // HHVM provides the required subset of Reflection but lists Reflections as missing
        if (!defined('HHVM_VERSION')) {
            $needed_extensions[] = 'Reflection';
        }
        $infos['needed_extensions'] = $needed_extensions;
        $infos['missing_extensions'] = array();
        foreach ($needed_extensions as $needed_extension) {
            if (!in_array($needed_extension, $extensions)) {
                $infos['missing_extensions'][] = $needed_extension;
            }
        }

        // Special case for mbstring
        if (!function_exists('mb_get_info')
            || ((int)ini_get('mbstring.func_overload')) != 0) {
            $infos['missing_extensions'][] = 'mbstring';
        }

        $infos['pdo_ok'] = false;
        if (in_array('PDO', $extensions)) {
            $infos['pdo_ok'] = true;
        }

        $infos['adapters'] = Adapter::getAdapters();

        $needed_functions = array(
            'debug_backtrace',
            'create_function',
            'eval',
            'gzcompress',
            'gzuncompress',
            'pack',
        );
        $infos['needed_functions'] = $needed_functions;
        $infos['missing_functions'] = array();
        foreach ($needed_functions as $needed_function) {
            if (!self::functionExists($needed_function)) {
                $infos['missing_functions'][] = $needed_function;
            }
        }

        // warnings
        $desired_extensions = array(
            'json',
            'libxml',
            'dom',
            'SimpleXML',
        );
        $infos['desired_extensions'] = $desired_extensions;
        $infos['missing_desired_extensions'] = array();
        foreach ($desired_extensions as $desired_extension) {
            if (!in_array($desired_extension, $extensions)) {
                $infos['missing_desired_extensions'][] = $desired_extension;
            }
        }
        $desired_functions = array(
            'set_time_limit',
            'mail',
            'parse_ini_file',
            'glob',
            'gzopen',
        );
        $infos['missing_desired_functions'] = array();
        foreach ($desired_functions as $desired_function) {
            if (!self::functionExists($desired_function)) {
                $infos['missing_desired_functions'][] = $desired_function;
            }
        }

        $sessionAutoStarted = (int)ini_get('session.auto_start');
        if ($sessionAutoStarted) {
            $infos['missing_desired_functions'][] = 'session.auto_start';
        }

        $desired_settings = array(
            'session.auto_start',
        );
        $infos['desired_functions'] = array_merge($desired_functions, $desired_settings);

        $infos['openurl'] = Http::getTransportMethod();

        $infos['gd_ok'] = SettingsServer::isGdExtensionEnabled();

        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        $infos['serverVersion'] = addslashes($serverSoftware);
        $infos['serverOs'] = @php_uname();
        $infos['serverTime'] = date('H:i:s');

        $infos['memoryMinimum'] = $minimumMemoryLimit;

        $infos['memory_ok'] = true;
        $infos['memoryCurrent'] = '';

        $raised = SettingsServer::raiseMemoryLimitIfNecessary();
        if (($memoryValue = SettingsServer::getMemoryLimitValue()) > 0) {
            $infos['memoryCurrent'] = $memoryValue . 'M';
            $infos['memory_ok'] = $memoryValue >= $minimumMemoryLimit;
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

        // check if filesystem is NFS, if it is file based sessions won't work properly
        $infos['is_nfs'] = Filesystem::checkIfFileSystemIsNFS();
        $infos = self::enrichSystemChecks($infos);

        return $infos;
    }

    /**
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
     * Test if function exists.  Also handles case where function is disabled via Suhosin.
     *
     * @param string $functionName Function name
     * @return bool True if function exists (not disabled); False otherwise.
     */
    public static function functionExists($functionName)
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
     * @param $piwik_minimumPHPVersion
     * @param $infos
     * @return bool
     */
    public static function isPhpVersionValid($phpVersion)
    {
        global $piwik_minimumPHPVersion;
        return version_compare($piwik_minimumPHPVersion, $phpVersion) === -1;
    }

}