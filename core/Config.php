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
 * @method static \Piwik\Config getInstance()
 */
class Config extends Singleton
{
    const DEFAULT_LOCAL_CONFIG_PATH = '/config/config.ini.php';
    const DEFAULT_COMMON_CONFIG_PATH = '/config/common.config.ini.php';
    const DEFAULT_GLOBAL_CONFIG_PATH = '/config/global.ini.php';

    /**
     * Contains configuration files values
     *
     * @var array
     */
    protected $initialized = false;
    protected $configGlobal = array();
    protected $configLocal = array();
    protected $configCommon = array();
    protected $configCache = array();
    protected $pathGlobal = null;
    protected $pathCommon = null;
    protected $pathLocal = null;

    /**
     * @var boolean
     */
    protected $isTest = false;

    /**
     * Constructor
     */
    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $this->pathGlobal = $pathGlobal ?: self::getGlobalConfigPath();
        $this->pathCommon = $pathCommon ?: self::getCommonConfigPath();
        $this->pathLocal = $pathLocal ?: self::getLocalConfigPath();
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
     */
    public function setTestEnvironment($pathLocal = null, $pathGlobal = null, $pathCommon = null, $allowSaving = false)
    {
        if (!$allowSaving) {
            $this->isTest = true;
        }

        $this->clear();

        $this->pathLocal = $pathLocal ?: Config::getLocalConfigPath();
        $this->pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
        $this->pathCommon = $pathCommon ?: Config::getCommonConfigPath();

        $this->init();

        // this proxy will not record any data in the production database.
        // this provides security for Piwik installs and tests were setup.
        if (isset($this->configGlobal['database_tests'])
            || isset($this->configLocal['database_tests'])
        ) {
            $this->__get('database_tests');
            $this->configCache['database'] = $this->configCache['database_tests'];
        }

        // Ensure local mods do not affect tests
        if (empty($pathGlobal)) {
            $this->configCache['Debug'] = $this->configGlobal['Debug'];
            $this->configCache['mail'] = $this->configGlobal['mail'];
            $this->configCache['General'] = $this->configGlobal['General'];
            $this->configCache['Segments'] = $this->configGlobal['Segments'];
            $this->configCache['Tracker'] = $this->configGlobal['Tracker'];
            $this->configCache['Deletelogs'] = $this->configGlobal['Deletelogs'];
            $this->configCache['Deletereports'] = $this->configGlobal['Deletereports'];
            $this->configCache['Development'] = $this->configGlobal['Development'];
        }

        // for unit tests, we set that no plugin is installed. This will force
        // the test initialization to create the plugins tables, execute ALTER queries, etc.
        $this->configCache['PluginsInstalled'] = array('PluginsInstalled' => array());
    }

    /**
     * Returns absolute path to the global configuration file
     *
     * @return string
     */
    protected static function getGlobalConfigPath()
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
        $hostConfig = static::getLocalConfigInfoForHostname($hostname);

        if (!Filesystem::isValidFilename($hostConfig['file'])) {
            throw new Exception('Hostname is not valid');
        }

        $this->pathLocal   = $hostConfig['path'];
        $this->configLocal = array();
        $this->initialized = false;
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
     */
    public function clear()
    {
        $this->configGlobal = array();
        $this->configLocal = array();
        $this->configCache = array();
        $this->initialized = false;
    }

    /**
     * Read configuration from files into memory
     *
     * @throws Exception if local config file is not readable; exits for other errors
     */
    public function init()
    {
        $this->initialized = true;
        $reportError = SettingsServer::isTrackerApiRequest();

        // read defaults from global.ini.php
        if (!is_readable($this->pathGlobal) && $reportError) {
            Piwik_ExitWithMessage(Piwik::translate('General_ExceptionConfigurationFileNotFound', array($this->pathGlobal)));
        }

        $this->configGlobal = _parse_ini_file($this->pathGlobal, true);

        if (empty($this->configGlobal) && $reportError) {
            Piwik_ExitWithMessage(Piwik::translate('General_ExceptionUnreadableFileDisabledMethod', array($this->pathGlobal, "parse_ini_file()")));
        }

        $this->configCommon = _parse_ini_file($this->pathCommon, true);

        // Check config.ini.php last
        $this->checkLocalConfigFound();

        $this->configLocal = _parse_ini_file($this->pathLocal, true);
        if (empty($this->configLocal) && $reportError) {
            Piwik_ExitWithMessage(Piwik::translate('General_ExceptionUnreadableFileDisabledMethod', array($this->pathLocal, "parse_ini_file()")));
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
            throw new Exception(Piwik::translate('General_ExceptionConfigurationFileNotFound', array($this->pathLocal)));
        }
    }

    /**
     * Decode HTML entities
     *
     * @param mixed $values
     * @return mixed
     */
    protected function decodeValues($values)
    {
        if (is_array($values)) {
            foreach ($values as &$value) {
                $value = $this->decodeValues($value);
            }
            return $values;
        }
        return html_entity_decode($values, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Encode HTML entities
     *
     * @param mixed $values
     * @return mixed
     */
    protected function encodeValues($values)
    {
        if (is_array($values)) {
            foreach ($values as &$value) {
                $value = $this->encodeValues($value);
            }
        } else {
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
            $this->init();

            // must be called here, not in init(), since setTestEnvironment() calls init(). (this avoids
            // infinite recursion)
            Piwik::postTestEvent('Config.createConfigSingleton',
                array($this, &$this->configCache, &$this->configLocal));
        }

        // check cache for merged section
        if (isset($this->configCache[$name])) {
            $tmp =& $this->configCache[$name];
            return $tmp;
        }

        $section = $this->getFromGlobalConfig($name);
        $sectionCommon = $this->getFromCommonConfig($name);
        if(empty($section) && !empty($sectionCommon)) {
            $section = $sectionCommon;
        } elseif(!empty($section) && !empty($sectionCommon)) {
            $section = $this->array_merge_recursive_distinct($section, $sectionCommon);
        }

        if (isset($this->configLocal[$name])) {
            // local settings override the global defaults
            $section = $section
                ? array_merge($section, $this->configLocal[$name])
                : $this->configLocal[$name];
        }

        if ($section === null && $name = 'superuser') {
            $user = $this->getConfigSuperUserForBackwardCompatibility();
            return $user;
        } else if ($section === null) {
            throw new Exception("Error while trying to read a specific config file entry <strong>'$name'</strong> from your configuration files.</b>If you just completed a Piwik upgrade, please check that the file config/global.ini.php was overwritten by the latest Piwik version.");
        }

        // cache merged section for later
        $this->configCache[$name] = $this->decodeValues($section);
        $tmp =& $this->configCache[$name];

        return $tmp;
    }

    /**
     * @deprecated since version 2.0.4
     */
    public function getConfigSuperUserForBackwardCompatibility()
    {
        try {
            $db   = Db::get();
            $user = $db->fetchRow("SELECT login, email, password
                                FROM " . Common::prefixTable("user") . "
                                WHERE superuser_access = 1
                                ORDER BY date_registered ASC LIMIT 1");

            if (!empty($user)) {
                $user['bridge'] = 1;
                return $user;
            }
        } catch (Exception $e) {}

        return array();
    }

    public function getFromGlobalConfig($name)
    {
        if (isset($this->configGlobal[$name])) {
            return $this->configGlobal[$name];
        }
        return null;
    }

    public function getFromCommonConfig($name)
    {
        if (isset($this->configCommon[$name])) {
            return $this->configCommon[$name];
        }
        return null;
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
        $this->configCache[$name] = $value;
    }

    /**
     * Comparison function
     *
     * @param mixed $elem1
     * @param mixed $elem2
     * @return int;
     */
    public static function compareElements($elem1, $elem2)
    {
        if (is_array($elem1)) {
            if (is_array($elem2)) {
                return strcmp(serialize($elem1), serialize($elem2));
            }

            return 1;
        }

        if (is_array($elem2)) {
            return -1;
        }

        if ((string)$elem1 === (string)$elem2) {
            return 0;
        }

        return ((string)$elem1 > (string)$elem2) ? 1 : -1;
    }

    /**
     * Compare arrays and return difference, such that:
     *
     *     $modified = array_merge($original, $difference);
     *
     * @param array $original original array
     * @param array $modified modified array
     * @return array differences between original and modified
     */
    public function array_unmerge($original, $modified)
    {
        // return key/value pairs for keys in $modified but not in $original
        // return key/value pairs for keys in both $modified and $original, but values differ
        // ignore keys that are in $original but not in $modified

        return array_udiff_assoc($modified, $original, array(__CLASS__, 'compareElements'));
    }

    /**
     * Dump config
     *
     * @param array $configLocal
     * @param array $configGlobal
     * @param array $configCommon
     * @param array $configCache
     * @return string
     */
    public function dumpConfig($configLocal, $configGlobal, $configCommon, $configCache)
    {
        $dirty = false;

        $output = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
        $output .= "; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.\n";

        if (!$configCache) {
            return false;
        }

        // If there is a common.config.ini.php, this will ensure config.ini.php does not duplicate its values
        if(!empty($configCommon)) {
            $configGlobal = $this->array_merge_recursive_distinct($configGlobal, $configCommon);
        }

        if ($configLocal) {
            foreach ($configLocal as $name => $section) {
                if (!isset($configCache[$name])) {
                    $configCache[$name] = $this->decodeValues($section);
                }
            }
        }

        $sectionNames = array_unique(array_merge(array_keys($configGlobal), array_keys($configCache)));

        foreach ($sectionNames as $section) {
            if (!isset($configCache[$section])) {
                continue;
            }

            // Only merge if the section exists in global.ini.php (in case a section only lives in config.ini.php)

            // get local and cached config
            $local = isset($configLocal[$section]) ? $configLocal[$section] : array();
            $config = $configCache[$section];

            // remove default values from both (they should not get written to local)
            if (isset($configGlobal[$section])) {
                $config = $this->array_unmerge($configGlobal[$section], $configCache[$section]);
                $local = $this->array_unmerge($configGlobal[$section], $local);
            }

            // if either local/config have non-default values and the other doesn't,
            // OR both have values, but different values, we must write to config.ini.php
            if (empty($local) xor empty($config)
                || (!empty($local)
                    && !empty($config)
                    && self::compareElements($config, $configLocal[$section]))
            ) {
                $dirty = true;
            }

            // no point in writing empty sections, so skip if the cached section is empty
            if (empty($config)) {
                continue;
            }

            $output .= "[$section]\n";

            foreach ($config as $name => $value) {
                $value = $this->encodeValues($value);

                if (is_numeric($name)) {
                    $name = $section;
                    $value = array($value);
                }

                if (is_array($value)) {
                    foreach ($value as $currentValue) {
                        $output .= $name . "[] = \"$currentValue\"\n";
                    }
                } else {
                    if (!is_numeric($value)) {
                        $value = "\"$value\"";
                    }
                    $output .= $name . ' = ' . $value . "\n";
                }
            }

            $output .= "\n";
        }

        if ($dirty) {
            return $output;
        }
        return false;
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
    protected function writeConfig($configLocal, $configGlobal, $configCommon, $configCache, $pathLocal, $clear = true)
    {
        if ($this->isTest) {
            return;
        }

        $output = $this->dumpConfig($configLocal, $configGlobal, $configCommon, $configCache);
        if ($output !== false) {
            $success = @file_put_contents($pathLocal, $output);
            if (!$success) {
                throw $this->getConfigNotWritableException();
            }
        }

        if ($clear) {
            $this->clear();
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
        $this->writeConfig($this->configLocal, $this->configGlobal, $this->configCommon, $this->configCache, $this->pathLocal);
    }

    /**
     * @throws \Exception
     */
    public function getConfigNotWritableException()
    {
        $path = "config/" . basename($this->pathLocal);
        return new Exception(Piwik::translate('General_ConfigFileIsNotWritable', array("(" . $path . ")", "")));
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    function array_merge_recursive_distinct ( array &$array1, array &$array2 )
    {
        $merged = $array1;
        foreach ( $array2 as $key => &$value ) {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
                $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }

}
