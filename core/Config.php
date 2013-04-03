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

/**
 * For general performance (and specifically, the Tracker), we use deferred (lazy) initialization
 * and cache sections.  We also avoid any dependency on Zend Framework's Zend_Config.
 *
 * We use a parse_ini_file() wrapper to parse the configuration files, in case php's built-in
 * function is disabled.
 *
 * Example reading a value from the configuration:
 *
 *     $minValue = Piwik_Config::getInstance()->General['minimum_memory_limit'];
 *
 * will read the value minimum_memory_limit under the [General] section of the config file.
 *
 * Example setting a section in the configuration:
 *
 *    $brandingConfig = array(
 *        'use_custom_logo' => 1,
 *    );
 *    Piwik_Config::getInstance()->branding = $brandingConfig;
 *
 * Example setting an option within a section in the configuration:
 *
 *    $brandingConfig = Piwik_Config::getInstance()->branding;
 *    $brandingConfig['use_custom_logo'] = 1;
 *    Piwik_Config::getInstance()->branding = $brandingConfig;
 *
 * @package Piwik
 * @subpackage Piwik_Config
 */
class Piwik_Config
{
    private static $instance = null;

    /**
     * Returns the singleton Piwik_Config
     *
     * @return Piwik_Config
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Contains configuration files values
     *
     * @var array
     */
    protected $initialized = false;
    protected $configGlobal = array();
    protected $configLocal = array();
    protected $configCache = array();
    protected $pathGlobal = null;
    protected $pathLocal = null;

    protected function __construct()
    {
        $this->pathGlobal = self::getGlobalConfigPath();
        $this->pathLocal = self::getLocalConfigPath();
    }

    /**
     * @var boolean
     */
    protected $isTest = false;

    /**
     * Enable test environment
     *
     * @param string $pathLocal
     * @param string $pathGlobal
     */
    public function setTestEnvironment($pathLocal = null, $pathGlobal = null)
    {
        $this->isTest = true;

        $this->clear();

        if ($pathLocal) {
            $this->pathLocal = $pathLocal;
        }

        if ($pathGlobal) {
            $this->pathGlobal = $pathGlobal;
        }

        $this->init();
        if (isset($this->configGlobal['database_tests'])
            || isset($this->configLocal['database_tests'])
        ) {
            $this->__get('database_tests');
            $this->configCache['database'] = $this->configCache['database_tests'];
        }

        // Ensure local mods do not affect tests
        if (is_null($pathGlobal)) {
            $this->configCache['Debug'] = $this->configGlobal['Debug'];
            $this->configCache['branding'] = $this->configGlobal['branding'];
            $this->configCache['mail'] = $this->configGlobal['mail'];
            $this->configCache['General'] = $this->configGlobal['General'];
            $this->configCache['Segments'] = $this->configGlobal['Segments'];
            $this->configCache['Tracker'] = $this->configGlobal['Tracker'];
            $this->configCache['Deletelogs'] = $this->configGlobal['Deletelogs'];
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
    public static function getGlobalConfigPath()
    {
        return PIWIK_USER_PATH . '/config/global.ini.php';
    }

    /**
     * Backward compatibility stub
     *
     * @todo remove in 2.0
     * @since 1.7
     * @deprecated 1.7
     * @return string
     */
    public static function getDefaultDefaultConfigPath()
    {
        return self::getGlobalConfigPath();
    }

    /**
     * Returns absolute path to the local configuration file
     *
     * @return string
     */
    public static function getLocalConfigPath()
    {
        return PIWIK_USER_PATH . '/config/config.ini.php';
    }

    /**
     * Is local configuration file writable?
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

        $this->pathGlobal = self::getGlobalConfigPath();
        $this->pathLocal = self::getLocalConfigPath();
    }

    /**
     * Read configuration from files into memory
     *
     * @throws Exception if local config file is not readable; exits for other errors
     */
    public function init()
    {
        $this->initialized = true;
        $reportError = empty($GLOBALS['PIWIK_TRACKER_MODE']);

        // read defaults from global.ini.php
        if (!is_readable($this->pathGlobal) && $reportError) {
            Piwik_ExitWithMessage(Piwik_TranslateException('General_ExceptionConfigurationFileNotFound', array($this->pathGlobal)));
        }

        $this->configGlobal = _parse_ini_file($this->pathGlobal, true);
        if (empty($this->configGlobal) && $reportError) {
            Piwik_ExitWithMessage(Piwik_TranslateException('General_ExceptionUnreadableFileDisabledMethod', array($this->pathGlobal, "parse_ini_file()")));
        }

        // read the local settings from config.ini.php
        if (!is_readable($this->pathLocal) && $reportError) {
            throw new Exception(Piwik_TranslateException('General_ExceptionConfigurationFileNotFound', array($this->pathLocal)));
        }

        $this->configLocal = _parse_ini_file($this->pathLocal, true);
        if (empty($this->configLocal) && $reportError) {
            Piwik_ExitWithMessage(Piwik_TranslateException('General_ExceptionUnreadableFileDisabledMethod', array($this->pathLocal, "parse_ini_file()")));
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
        } else {
            $values = html_entity_decode($values, ENT_COMPAT, 'UTF-8');
        }
        return $values;
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
        }
        return $values;
    }

    /**
     * Magic get methods catching calls to $config->var_name
     * Returns the value if found in the configuration
     *
     * @param string $name
     * @return string|array The value requested, returned by reference
     * @throws Exception if the value requested not found in both files
     */
    public function &__get($name)
    {
        if (!$this->initialized) {
            $this->init();
        }

        // check cache for merged section
        if (isset($this->configCache[$name])) {
            $tmp =& $this->configCache[$name];
            return $tmp;
        }

        $section = null;

        // merge corresponding sections from global and local settings
        if (isset($this->configGlobal[$name])) {
            $section = $this->configGlobal[$name];
        }

        if (isset($this->configLocal[$name])) {
            // local settings override the global defaults
            $section = $section
                ? array_merge($section, $this->configLocal[$name])
                : $this->configLocal[$name];
        }

        if ($section === null) {
            throw new Exception("Error while trying to read a specific config file entry <b>'$name'</b> from your configuration files.</b>If you just completed a Piwik upgrade, please check that the file config/global.ini.php was overwritten by the latest Piwik version.");
        }

        // cache merged section for later
        $this->configCache[$name] = $this->decodeValues($section);
        $tmp =& $this->configCache[$name];

        return $tmp;
    }

    /**
     * Set value
     *
     * @param string $name This corresponds to the section name
     * @param mixed $value
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
     * @param array $configCache
     * @return string
     */
    public function dumpConfig($configLocal, $configGlobal, $configCache)
    {
        $dirty = false;

        $output = "; <?php exit; ?> DO NOT REMOVE THIS LINE\n";
        $output .= "; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.\n";

        if ($configCache) {
            foreach ($configLocal as $name => $section) {
                if (!isset($configCache[$name])) {
                    $configCache[$name] = $this->decodeValues($section);
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
        }

        return false;
    }


    /**
     * Write user configuration file
     *
     * @param array $configLocal
     * @param array $configGlobal
     * @param array $configCache
     * @param string $pathLocal
     */
    public function writeConfig($configLocal, $configGlobal, $configCache, $pathLocal)
    {
        if ($this->isTest) {
            return;
        }

        $output = $this->dumpConfig($configLocal, $configGlobal, $configCache);
        if ($output !== false) {
            @file_put_contents($pathLocal, $output);
        }

        $this->clear();
    }

    /**
     * Force save
     */
    public function forceSave()
    {
        $this->writeConfig($this->configLocal, $this->configGlobal, $this->configCache, $this->pathLocal);
    }
}
