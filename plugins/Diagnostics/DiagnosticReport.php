<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CliMulti\CliPhp;
use Piwik\Common;
use Piwik\Config;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Development;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\SettingsPiwik;
use Piwik\Version;

/**
 * A diagnostic report contains all the results of all the diagnostics.
 */
class DiagnosticReport
{
    /**
     * @var DiagnosticResult[]
     */
    private $mandatoryDiagnosticResults;

    /**
     * @var DiagnosticResult[]
     */
    private $optionalDiagnosticResults;

    /**
     * @var int
     */
    private $errorCount = 0;

    /**
     * @var int
     */
    private $warningCount = 0;

    /**
     * @param DiagnosticResult[] $mandatoryDiagnosticResults
     * @param DiagnosticResult[] $optionalDiagnosticResults
     */
    public function __construct(array $mandatoryDiagnosticResults, array $optionalDiagnosticResults)
    {
        $this->mandatoryDiagnosticResults = $mandatoryDiagnosticResults;
        $this->optionalDiagnosticResults = $optionalDiagnosticResults;

        $this->computeErrorAndWarningCount();
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->getErrorCount() > 0;
    }

    /**
     * @return bool
     */
    public function hasWarnings()
    {
        return $this->getWarningCount() > 0;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * @return int
     */
    public function getWarningCount()
    {
        return $this->warningCount;
    }

    /**
     * @return DiagnosticResult[]
     */
    public function getAllResults()
    {
        return array_merge($this->mandatoryDiagnosticResults, $this->optionalDiagnosticResults);
    }

    /**
     * @return DiagnosticResult[]
     */
    public function getMandatoryDiagnosticResults()
    {
        return $this->mandatoryDiagnosticResults;
    }

    /**
     * @return DiagnosticResult[]
     */
    public function getOptionalDiagnosticResults()
    {
        return $this->optionalDiagnosticResults;
    }

    public function getInformationalResults()
    {
        $results = [];

        $results[] = DiagnosticResult::informationalResult('Matomo Version', Version::VERSION);

        if ( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
            $results[] = DiagnosticResult::informationalResult('Server Info', $_SERVER['SERVER_SOFTWARE']);
        }
        if ( defined('PHP_OS') && PHP_OS ) {
            $results[] = DiagnosticResult::informationalResult('PHP_OS',  PHP_OS);
        }
        if ( defined('PHP_BINARY') && PHP_BINARY ) {
            $results[] = DiagnosticResult::informationalResult('PHP_BINARY', PHP_BINARY);
        }
        $results[] = DiagnosticResult::informationalResult('PHP SAPI', php_sapi_name());

        $cliPhp = new CliPhp();
        $binary = $cliPhp->findPhpBinary();
        if (!empty($binary)) {
            $binary = basename($binary);
            $rows[] = array(
                'name'  => 'PHP Found Binary',
                'value' => $binary,
            );
        }
        $results[] = DiagnosticResult::informationalResult('PHP Timezone', date_default_timezone_get());
        $results[] = DiagnosticResult::informationalResult('PHP Time', time());
        $results[] = DiagnosticResult::informationalResult('PHP Datetime', Date::now()->getDatetime());

        $disabled_functions = ini_get('disable_functions');
        if (!empty($disabled_functions)) {
            $results[] = DiagnosticResult::informationalResult('PHP Disabled functions', $disabled_functions);
        }

        foreach (['max_execution_time', 'post_max_size', 'max_input_vars', 'zlib.output_compression'] as $iniSetting) {
            $results[] = DiagnosticResult::informationalResult('PHP INI ' . $iniSetting, @ini_get($iniSetting));
        }

        if ( function_exists( 'curl_version' ) ) {
            $curl_version = curl_version();
            $curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
            $results[] = DiagnosticResult::informationalResult('Curl Version', $curl_version);
        }
        $suhosin_installed = ( extension_loaded( 'suhosin' ) || ( defined( 'SUHOSIN_PATCH' ) && constant( 'SUHOSIN_PATCH' ) ) );

        $results[] = DiagnosticResult::informationalResult('Suhosin Installed', (int) $suhosin_installed);

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $results[] = DiagnosticResult::informationalResult('User Agent', $_SERVER['HTTP_USER_AGENT']);
        }
        $results[] = DiagnosticResult::informationalResult('Browser Language', Common::getBrowserLanguage());

        if (SettingsPiwik::isMatomoInstalled()){
            $dbConfig = Config::getInstance()->database;
            $results[] = DiagnosticResult::informationalResult('DB Prefix', $dbConfig['tables_prefix']);
            $results[] = DiagnosticResult::informationalResult('DB Charset', $dbConfig['charset']);
            $results[] = DiagnosticResult::informationalResult('DB Adapter', $dbConfig['adapter']);
            try {
                $results[] = DiagnosticResult::informationalResult('MySQL Version', Db::get()->getServerVersion());
            } catch (\Exception $e) {
                $results[] = DiagnosticResult::informationalResult('MySQL Version', $e->getMessage());
            }
            $results[] = DiagnosticResult::informationalResult('Num Tables', $this->getNumMatomoTables());

            $pluginsActivated = array();
            $pluginsDeactivated = array();
            $pluginsInvalid = array();
            $plugins = Manager::getInstance()->loadAllPluginsAndGetTheirInfo();
            foreach ($plugins as $pluginName => $plugin) {
                $string = $pluginName;
                if (!empty($plugin['info']['version'])
                    && !empty($plugin['uninstallable'])
                    && (!defined('PIWIK_TEST_MODE') || !PIWIK_TEST_MODE)) {
                    // we only want to show versions for plugins not shipped with core
                    // in tests we don't show version numbers to not always needing to update the screenshot
                    $string .= ' ' . $plugin['info']['version'];
                }
                if (!empty($plugin['activated'])) {
                    $pluginsActivated[] = $string;
                } else {
                    $pluginsDeactivated[] = $string;
                }
                if (!empty($plugin['invalid'])) {
                    $pluginsInvalid[] = $string;
                }
            }

            $results[] = DiagnosticResult::informationalResult('Plugins Activated', implode(', ', $pluginsActivated));
            $results[] = DiagnosticResult::informationalResult('Plugins Deactivated', implode(', ', $pluginsDeactivated));
            $results[] = DiagnosticResult::informationalResult('Plugins Invalid', implode(', ', $pluginsInvalid));

            if (!empty($GLOBALS['MATOMO_PLUGIN_DIRS'])) {
                $results[] = DiagnosticResult::informationalResult('Custom Plugins Directories', json_encode($GLOBALS['MATOMO_PLUGIN_DIRS']));
            }

            $results[] = DiagnosticResult::informationalResult('Matomo Install Version', DbHelper::getInstallVersion());
            $results[] = DiagnosticResult::informationalResult('Had visits in last 1 day', $this->hadVisitsInLastDays(1));
            $results[] = DiagnosticResult::informationalResult('Had visits in last 3 days', $this->hadVisitsInLastDays(3));
            $results[] = DiagnosticResult::informationalResult('Had visits in last 5 days', $this->hadVisitsInLastDays(5));

            $results[] = DiagnosticResult::informationalResult('Browser Archiving Enabled', (int) Rules::isBrowserTriggerEnabled());
            $results[] = DiagnosticResult::informationalResult('Browser Segment Archiving Enabled', (int) Rules::isBrowserArchivingAvailableForSegments());
            $results[] = DiagnosticResult::informationalResult('Development Mode Enabled', (int) Development::isEnabled());
            $results[] = DiagnosticResult::informationalResult('Internet Enabled',(int) SettingsPiwik::isInternetEnabled());
            $results[] = DiagnosticResult::informationalResult('Multi Server Environment',(int) SettingsPiwik::isMultiServerEnvironment());
            $results[] = DiagnosticResult::informationalResult('Archive Time Last Started', Option::get(CronArchive::OPTION_ARCHIVING_STARTED_TS));
            $results[] = DiagnosticResult::informationalResult('Archive Time Last Finished', Option::get(CronArchive::OPTION_ARCHIVING_FINISHED_TS));
        }
        return $results;
    }

    private function getNumMatomoTables() {
        $prefix = Common::prefixTable('');

        $results = null;
        try {
            $results = Db::get()->fetchAll('show tables like "'.$prefix.'%"');
        } catch (\Exception $e) {
            return 'show tables not working';
        }

        return count($results);
    }

    private function hadVisitsInLastDays($numDays)
    {
        $table = Common::prefixTable('log_visit');
        $time = Date::now()->subDay($numDays)->getDatetime();

        try {
            $row = Db::fetchOne('SELECT count(idsite) from ' . $table . ' where visit_last_action_time > ? LIMIT 1', $time );
        } catch ( \Exception $e ) {
            $row = null;
        }

        if (!empty($row)) {
            return Piwik::translate('General_Yes');
        }
        return Piwik::translate('General_No');
    }

    private function computeErrorAndWarningCount()
    {
        foreach ($this->getAllResults() as $result) {
            switch ($result->getStatus()) {
                case DiagnosticResult::STATUS_ERROR:
                    $this->errorCount++;
                    break;
                case DiagnosticResult::STATUS_WARNING:
                    $this->warningCount++;
                    break;
            }
        }
    }
}
