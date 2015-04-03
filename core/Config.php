<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;
use Piwik\Config\IniFileChain;
use Piwik\Config\ConfigNotFoundException;
use Piwik\Config\IniFileChainFactory;
use Piwik\Ini\IniReadingException;

/**
 * Singleton that provides read & write access to Piwik's INI configuration.
 *
 * This class reads and writes to the `config/config.ini.php` file. If config
 * options are missing from that file, this class will look for their default
 * values in `config/global.ini.php`.
 *
 * ### Examples
 *
 * **Getting a value:**
 *
 *     // read the minimum_memory_limit option under the [General] section
 *     $minValue = Config::getInstance()->General['minimum_memory_limit'];
 *
 * **Setting a value:**
 *
 *     // set the minimum_memory_limit option
 *     Config::getInstance()->General['minimum_memory_limit'] = 256;
 *     Config::getInstance()->forceSave();
 *
 * **Setting an entire section:**
 *
 *     Config::getInstance()->MySection = array('myoption' => 1);
 *     Config::getInstance()->forceSave();
 *
 * @method static Config getInstance()
 */
class Config extends Singleton
{
    const DEFAULT_LOCAL_CONFIG_PATH = '/config/config.ini.php';
    const DEFAULT_COMMON_CONFIG_PATH = '/config/common.config.ini.php';
    const DEFAULT_GLOBAL_CONFIG_PATH = '/config/global.ini.php';

    /**
     * @var boolean
     */
    protected $pathGlobal = null;
    protected $pathCommon = null;
    protected $pathLocal = null;

    /**
     * @var boolean
     */
    protected $doNotWriteConfigInTests = false;

    /**
     * @var IniFileChain
     */
    protected $settings;

    private $initialized = false;

    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $this->pathGlobal = $pathGlobal ?: self::getGlobalConfigPath();
        $this->pathCommon = $pathCommon ?: self::getCommonConfigPath();
        $this->pathLocal = $pathLocal ?: self::getLocalConfigPath();

        $this->settings = IniFileChainFactory::get($pathGlobal, $pathLocal, $pathCommon);
    }

    /**
     * Returns the path to the local config file used by this instance.
     *
     * @return string
     */
    public function getLocalPath()
    {
        return $this->pathLocal;
    }

    /**
     * Returns the path to the global config file used by this instance.
     *
     * @return string
     */
    public function getGlobalPath()
    {
        return $this->pathGlobal;
    }

    /**
     * Returns the path to the common config file used by this instance.
     *
     * @return string
     */
    public function getCommonPath()
    {
        return $this->pathCommon;
    }

    /**
     * Enable test environment
     *
     * @param string $pathLocal
     * @param string $pathGlobal
     * @param string $pathCommon
     * @deprecated
     */
    public function setTestEnvironment($pathLocal = null, $pathGlobal = null, $pathCommon = null, $allowSaving = false)
    {
        if (!$allowSaving) {
            $this->doNotWriteConfigInTests = true;
        }

        $this->pathLocal = $pathLocal ?: Config::getLocalConfigPath();
        $this->pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
        $this->pathCommon = $pathCommon ?: Config::getCommonConfigPath();

        $this->reload();

        $databaseTestsSettings = $this->settings->get('database_tests'); // has to be __get otherwise when called from TestConfig, PHP will issue a NOTICE
        if (!empty($databaseTestsSettings)) {
            $this->settings->set('database', $databaseTestsSettings);
        }

        // Ensure local mods do not affect tests
        if (empty($pathGlobal)) {
            $this->settings->set('Debug', $this->settings->getFrom($this->pathGlobal, 'Debug'));
            $this->settings->set('mail', $this->settings->getFrom($this->pathGlobal, 'mail'));
            $this->settings->set('General', $this->settings->getFrom($this->pathGlobal, 'General'));
            $this->settings->set('Segments', $this->settings->getFrom($this->pathGlobal, 'Segments'));
            $this->settings->set('Tracker', $this->settings->getFrom($this->pathGlobal, 'Tracker'));
            $this->settings->set('Deletelogs', $this->settings->getFrom($this->pathGlobal, 'Deletelogs'));
            $this->settings->set('Deletereports', $this->settings->getFrom($this->pathGlobal, 'Deletereports'));
            $this->settings->set('Development', $this->settings->getFrom($this->pathGlobal, 'Development'));
        }

        // for unit tests, we set that no plugin is installed. This will force
        // the test initialization to create the plugins tables, execute ALTER queries, etc.
        $this->settings->set('PluginsInstalled', array('PluginsInstalled' => array()));
    }

    protected function postConfigTestEvent()
    {
        $allSettings =& $this->settings->getAll();
        Piwik::postTestEvent('Config.createConfigSingleton', array($this, &$allSettings));
    }

    /**
     * Returns absolute path to the global configuration file
     *
     * @return string
     */
    public static function getGlobalConfigPath()
    {
        return PIWIK_USER_PATH . self::DEFAULT_GLOBAL_CONFIG_PATH;
    }

    /**
     * Returns absolute path to the common configuration file.
     *
     * @return string
     */
    public static function getCommonConfigPath()
    {
        return PIWIK_USER_PATH . self::DEFAULT_COMMON_CONFIG_PATH;
    }

    /**
     * Returns absolute path to the local configuration file
     *
     * @return string
     */
    public static function getLocalConfigPath()
    {
        $path = self::getByDomainConfigPath();
        if ($path) {
            return $path;
        }
        return PIWIK_USER_PATH . self::DEFAULT_LOCAL_CONFIG_PATH;
    }

    private static function getLocalConfigInfoForHostname($hostname)
    {
        // Remove any port number to get actual hostname
        $hostname = Url::getHostSanitized($hostname);
        $perHostFilename  = $hostname . '.config.ini.php';
        $pathDomainConfig = PIWIK_USER_PATH . '/config/' . $perHostFilename;

        return array('file' => $perHostFilename, 'path' => $pathDomainConfig);
    }

    public function getConfigHostnameIfSet()
    {
        if ($this->getByDomainConfigPath() === false) {
            return false;
        }
        return $this->getHostname();
    }

    public function getClientSideOptions()
    {
        $general = $this->General;

        return array(
            'action_url_category_delimiter' => $general['action_url_category_delimiter'],
            'autocomplete_min_sites' => $general['autocomplete_min_sites'],
            'datatable_export_range_as_day' => $general['datatable_export_range_as_day'],
            'datatable_row_limits' => $this->getDatatableRowLimits()
        );
    }

    /**
     * @param $general
     * @return mixed
     */
    private function getDatatableRowLimits()
    {
        $limits = $this->General['datatable_row_limits'];
        $limits = explode(",", $limits);
        $limits = array_map('trim', $limits);
        return $limits;
    }

    protected static function getByDomainConfigPath()
    {
        $host       = self::getHostname();
        $hostConfig = self::getLocalConfigInfoForHostname($host);

        if (Filesystem::isValidFilename($hostConfig['file'])
            && file_exists($hostConfig['path'])
        ) {
            return $hostConfig['path'];
        }
        return false;
    }

    /**
     * Returns the hostname of the current request (without port number)
     *
     * @return string
     */
    public static function getHostname()
    {
        // Check trusted requires config file which is not ready yet
        $host = Url::getHost($checkIfTrusted = false);

        // Remove any port number to get actual hostname
        $host = Url::getHostSanitized($host);

        return $host;
    }

    /**
     * If set, Piwik will use the hostname config no matter if it exists or not. Useful for instance if you want to
     * create a new hostname config:
     *
     *     $config = Config::getInstance();
     *     $config->forceUsageOfHostnameConfig('piwik.example.com');
     *     $config->save();
     *
     * @param string $hostname eg piwik.example.com
     * @return string
     * @throws \Exception In case the domain contains not allowed characters
     */
    public function forceUsageOfLocalHostnameConfig($hostname)
    {
        $hostConfig = self::getLocalConfigInfoForHostname($hostname);

        $filename = $hostConfig['file'];
        if (!Filesystem::isValidFilename($filename)) {
            throw new Exception('Piwik domain is not a valid looking hostname (' . $filename . ').');
        }

        $this->pathLocal   = $hostConfig['path'];
        $this->configLocal = array();
        $this->initialized = false;
        $this->reload();
        return $this->pathLocal;
    }

    /**
     * Returns `true` if the local configuration file is writable.
     *
     * @return bool
     */
    public function isFileWritable()
    {
        return is_writable($this->pathLocal);
    }

    /**
     * Clear in-memory configuration so it can be reloaded
     * @deprecated since v2.12.0
     * TODO: remove uses of.
     */
    public function clear()
    {
        $this->initialized = false;
        $this->reload();
    }

    /**
     * Read configuration from files into memory
     *
     * @throws Exception if local config file is not readable; exits for other errors
     * @deprecated since v2.12.0
     * TODO: remove uses of
     */
    public function init()
    {
        $this->reload();
    }

    /**
     * Reloads config data from disk.
     *
     * @throws \Exception if the global config file is not found and this is a tracker request, or
     *                    if the local config file is not found and this is NOT a tracker request.
     * TODO: make private
     */
    public function reload()
    {
        $this->initialized = true;

        $inTrackerRequest = SettingsServer::isTrackerApiRequest();

        // read defaults from global.ini.php
        if (!is_readable($this->pathGlobal) && $inTrackerRequest) {
            throw new Exception(Piwik::translate('General_ExceptionConfigurationFileNotFound', array($this->pathGlobal)));
        }

        try {
            // Force a reload because maybe the files to use were overridden in the Config's constructor
            $this->settings->reload(array($this->pathGlobal, $this->pathCommon), $this->pathLocal);
        } catch (IniReadingException $e) {
            // TODO why a different behavior here? This needs a comment
            if ($inTrackerRequest) {
                throw $e;
            }
        }

        // Check config.ini.php last
        if (!$inTrackerRequest) {
            $this->checkLocalConfigFound();
        }
    }

    public function existsLocalConfig()
    {
        return is_readable($this->pathLocal);
    }

    public function deleteLocalConfig()
    {
        $configLocal = $this->getLocalPath();
        unlink($configLocal);
    }

    public function checkLocalConfigFound()
    {
        if (!$this->existsLocalConfig()) {
            throw new ConfigNotFoundException(Piwik::translate('General_ExceptionConfigurationFileNotFound', array($this->pathLocal)));
        }
    }

    /**
     * Decode HTML entities
     *
     * @param mixed $values
     * @return mixed
     */
    public static function decodeValues(&$values)
    {
        if (is_array($values)) {
            foreach ($values as &$value) {
                $value = self::decodeValues($value);
            }
            return $values;
        } elseif (is_string($values)) {
            return html_entity_decode($values, ENT_COMPAT, 'UTF-8');
        }
        return $values;
    }

    /**
     * Encode HTML entities
     *
     * @param mixed $values
     * @return mixed
     */
    protected function encodeValues(&$values)
    {
        if (is_array($values)) {
            foreach ($values as &$value) {
                $value = $this->encodeValues($value);
            }
        } elseif (is_float($values)) {
            $values = Common::forceDotAsSeparatorForDecimalPoint($values);
        } elseif (is_string($values)) {
            $values = htmlentities($values, ENT_COMPAT, 'UTF-8');
            $values = str_replace('$', '&#36;', $values);
        }
        return $values;
    }

    /**
     * Returns a configuration value or section by name.
     *
     * @param string $name The value or section name.
     * @return string|array The requested value requested. Returned by reference.
     * @throws Exception If the value requested not found in either `config.ini.php` or
     *                   `global.ini.php`.
     * @api
     */
    public function &__get($name)
    {
        if (!$this->initialized) {
            $this->postConfigTestEvent();
        }

        $section =& $this->settings->get($name);
        return $section;
    }

    public function getFromGlobalConfig($name)
    {
        return $this->settings->getFrom($this->pathGlobal, $name);
    }

    public function getFromCommonConfig($name)
    {
        return $this->settings->getFrom($this->pathCommon, $name);
    }

    /**
     * Sets a configuration value or section.
     *
     * @param string $name This section name or value name to set.
     * @param mixed $value
     * @api
     */
    public function __set($name, $value)
    {
        $this->settings->set($name, $value);
    }

    /**
     * Dump config
     *
     * @return string|null
     * @throws \Exception
     */
    public function dumpConfig()
    {
        $this->encodeValues($this->settings->getAll());

        try {
            $header = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
            $header .= "; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.\n";
            $dumpedString = $this->settings->dumpChanges($header);

            $this->decodeValues($this->settings->getAll());
        } catch (Exception $ex) {
            $this->decodeValues($this->settings->getAll());

            throw $ex;
        }

        return $dumpedString;
    }

    /**
     * Write user configuration file
     *
     * @param array $configLocal
     * @param array $configGlobal
     * @param array $configCommon
     * @param array $configCache
     * @param string $pathLocal
     * @param bool $clear
     *
     * @throws \Exception if config file not writable
     */
    protected function writeConfig($clear = true)
    {
        if ($this->doNotWriteConfigInTests) {
            return;
        }

        $output = $this->dumpConfig();
        if ($output !== null
            && $output !== false
        ) {
            $success = @file_put_contents($this->pathLocal, $output);
            if ($success === false) {
                throw $this->getConfigNotWritableException();
            }
        }

        if ($clear) {
            $this->reload();
        }
    }

    /**
     * Writes the current configuration to the **config.ini.php** file. Only writes options whose
     * values are different from the default.
     *
     * @api
     */
    public function forceSave()
    {
        $this->writeConfig();
    }

    /**
     * @throws \Exception
     */
    public function getConfigNotWritableException()
    {
        $path = "config/" . basename($this->pathLocal);
        return new Exception(Piwik::translate('General_ConfigFileIsNotWritable', array("(" . $path . ")", "")));
    }
}