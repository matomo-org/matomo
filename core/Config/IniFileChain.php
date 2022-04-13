<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Config;

use Piwik\Common;
use Matomo\Ini\IniReader;
use Matomo\Ini\IniReadingException;
use Matomo\Ini\IniWriter;
use Piwik\Piwik;

/**
 * Manages a list of INI files where the settings in each INI file merge with or override the
 * settings in the previous INI file.
 *
 * The IniFileChain class manages two types of INI files: multiple default setting files and one
 * user settings file.
 *
 * The default setting files (for example, global.ini.php & common.ini.php) hold the default setting values.
 * The settings in these files are merged recursively, however, array settings in one file will still
 * overwrite settings in the previous file.
 *
 * Default settings files cannot be modified through the IniFileChain class.
 *
 * The user settings file (for example, config.ini.php) holds the actual setting values. Settings in the
 * user settings files overwrite other settings. So array settings will not merge w/ previous values.
 *
 * HTML characters and dollar signs are stored as encoded HTML entities in INI files. This prevents
 * several `parse_ini_file` issues, including one where parse_ini_file tries to insert a variable
 * into a setting value if a string like `"$varname" is present.
 */
class IniFileChain
{
    const CONFIG_CACHE_KEY = 'config.ini';
    /**
     * Maps INI file names with their parsed contents. The order of the files signifies the order
     * in the chain. Files with lower index are overwritten/merged with files w/ a higher index.
     *
     * @var array
     */
    protected $settingsChain = [];

    /**
     * The merged INI settings.
     *
     * @var array
     */
    protected $mergedSettings = [];

    /**
     * Constructor.
     *
     * @param string[] $defaultSettingsFiles The list of paths to INI files w/ the default setting values.
     * @param string|null $userSettingsFile The path to the user settings file.
     */
    public function __construct(array $defaultSettingsFiles = [], $userSettingsFile = null)
    {
        $this->reload($defaultSettingsFiles, $userSettingsFile);
    }

    /**
     * Return setting section by reference.
     *
     * @param string $name
     * @return mixed
     */
    public function &get($name)
    {
        if (!isset($this->mergedSettings[$name])) {
            $this->mergedSettings[$name] = [];
        }

        $result =& $this->mergedSettings[$name];
        return $result;
    }

    /**
     * Return setting section from a specific file, rather than the current merged settings.
     *
     * @param string $file The path of the file. Should be the path used in construction or reload().
     * @param string $name The name of the section to access.
     */
    public function getFrom($file, $name)
    {
        return @$this->settingsChain[$file][$name];
    }

    /**
     * Sets a setting value.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $name = $this->replaceSectionInvalidChars($name);
        if ($value !== null) {
            $value = $this->replaceInvalidChars($value);
        }

        $this->mergedSettings[$name] = $value;
    }

    /**
     * Returns all settings. Changes made to the array result will be reflected in the
     * IniFileChain instance.
     *
     * @return array
     */
    public function &getAll()
    {
        return $this->mergedSettings;
    }

    /**
     * Dumps the current in-memory setting values to a string in INI format and returns it.
     *
     * @param string $header The header of the output INI file.
     * @return string The dumped INI contents.
     */
    public function dump($header = '')
    {
        return $this->dumpSettings($this->mergedSettings, $header);
    }

    /**
     * Writes the difference of the in-memory setting values and the on-disk user settings file setting
     * values to a string in INI format, and returns it.
     *
     * If a config section is identical to the default settings section (as computed by merging
     * all default setting files), it is not written to the user settings file.
     *
     * @param string $header The header of the INI output.
     * @return string The dumped INI contents.
     */
    public function dumpChanges($header = '')
    {
        $userSettingsFile = $this->getUserSettingsFile();

        $defaultSettings = $this->getMergedDefaultSettings();
        $existingMutableSettings = $this->settingsChain[$userSettingsFile];

        $dirty = false;

        $configToWrite = [];
        foreach ($this->mergedSettings as $sectionName => $changedSection) {
            if (isset($existingMutableSettings[$sectionName])) {
                $existingMutableSection = $existingMutableSettings[$sectionName];
            } else {
                $existingMutableSection = [];
            }

            // remove default values from both (they should not get written to local)
            if (isset($defaultSettings[$sectionName])) {
                $changedSection = $this->arrayUnmerge($defaultSettings[$sectionName], $changedSection);
                $existingMutableSection = $this->arrayUnmerge($defaultSettings[$sectionName], $existingMutableSection);
            }

            // if either local/config have non-default values and the other doesn't,
            // OR both have values, but different values, we must write to config.ini.php
            if (
                empty($changedSection) xor empty($existingMutableSection)
                || (!empty($changedSection)
                    && !empty($existingMutableSection)
                    && self::compareElements($changedSection, $existingMutableSection))
            ) {
                $dirty = true;
            }

            $configToWrite[$sectionName] = $changedSection;
        }

        if ($dirty) {
            // sort config sections by how early they appear in the file chain
            $self = $this;
            uksort($configToWrite, function ($sectionNameLhs, $sectionNameRhs) use ($self) {
                $lhsIndex = $self->findIndexOfFirstFileWithSection($sectionNameLhs);
                $rhsIndex = $self->findIndexOfFirstFileWithSection($sectionNameRhs);

                if ($lhsIndex == $rhsIndex) {
                    $lhsIndexInFile = $self->getIndexOfSectionInFile($lhsIndex, $sectionNameLhs);
                    $rhsIndexInFile = $self->getIndexOfSectionInFile($rhsIndex, $sectionNameRhs);

                    if ($lhsIndexInFile == $rhsIndexInFile) {
                        return 0;
                    } elseif ($lhsIndexInFile < $rhsIndexInFile) {
                        return -1;
                    } else {
                        return 1;
                    }
                } elseif ($lhsIndex < $rhsIndex) {
                    return -1;
                } else {
                    return 1;
                }
            });

            return $this->dumpSettings($configToWrite, $header);
        } else {
            return null;
        }
    }

    /**
     * Reloads settings from disk.
     */
    public function reload($defaultSettingsFiles = [], $userSettingsFile = null)
    {
        if (
            !empty($defaultSettingsFiles)
            || !empty($userSettingsFile)
        ) {
            $this->resetSettingsChain($defaultSettingsFiles, $userSettingsFile);
        }

        $hasAbsoluteConfigFile = !empty($userSettingsFile) && strpos($userSettingsFile, DIRECTORY_SEPARATOR) === 0;
        $useConfigCache = !empty($GLOBALS['ENABLE_CONFIG_PHP_CACHE']) && $hasAbsoluteConfigFile;

        if ($useConfigCache && is_file($userSettingsFile)) {
            $cache = new Cache();
            $values = $cache->doFetch(self::CONFIG_CACHE_KEY);

            if (
                !empty($values)
                && isset($values['mergedSettings'])
                && isset($values['settingsChain'][$userSettingsFile])
            ) {
                $this->mergedSettings = $values['mergedSettings'];
                $this->settingsChain = $values['settingsChain'];
                return;
            }
        }

        $reader = new IniReader();
        foreach ($this->settingsChain as $file => $ignore) {
            if (is_readable($file)) {
                try {
                    $contents = $reader->readFile($file);
                    $this->settingsChain[$file] = $this->decodeValues($contents);
                } catch (IniReadingException $ex) {
                    throw new IniReadingException('Unable to read INI file {' . $file . '}: ' . $ex->getMessage() . "\n Your host may have disabled parse_ini_file().");
                }

                $this->decodeValues($this->settingsChain[$file]);
            }
        }

        $merged = $this->mergeFileSettings();
        // remove reference to $this->settingsChain... otherwise dump() or compareElements() will never notice a difference
        // on PHP 7+ as they would be always equal
        $this->mergedSettings = $this->copy($merged);

        if (!empty($GLOBALS['MATOMO_MODIFY_CONFIG_SETTINGS']) && !empty($this->mergedSettings)) {
            $this->mergedSettings = call_user_func($GLOBALS['MATOMO_MODIFY_CONFIG_SETTINGS'], $this->mergedSettings);
        }

        if (
            $useConfigCache
            && !empty($this->mergedSettings)
            && !empty($this->settingsChain)
            && Cache::hasHostConfig($this->mergedSettings)
        ) {
            $ttlOneHour = 3600;
            $cache = new Cache();
            if ($cache->isValidHost($this->mergedSettings)) {
                // we make sure to save the config only if the host is valid...
                $data = ['mergedSettings' => $this->mergedSettings, 'settingsChain' => $this->settingsChain];
                $cache->doSave(self::CONFIG_CACHE_KEY, $data, $ttlOneHour);
            }
        }
    }

    public function deleteConfigCache()
    {
        if (!empty($GLOBALS['ENABLE_CONFIG_PHP_CACHE'])) {
            $cache = new Cache();
            $cache->doDelete(IniFileChain::CONFIG_CACHE_KEY);
        }
    }

    private function copy($merged)
    {
        $copy = [];
        foreach ($merged as $index => $value) {
            if (is_array($value)) {
                $copy[$index] = $this->copy($value);
            } else {
                $copy[$index] = $value;
            }
        }
        return $copy;
    }

    private function resetSettingsChain($defaultSettingsFiles, $userSettingsFile)
    {
        $this->settingsChain = [];

        if (!empty($defaultSettingsFiles)) {
            foreach ($defaultSettingsFiles as $file) {
                $this->settingsChain[$file] = null;
            }
        }

        if (!empty($userSettingsFile)) {
            $this->settingsChain[$userSettingsFile] = null;
        }
    }

    protected function mergeFileSettings()
    {
        $mergedSettings = $this->getMergedDefaultSettings();

        $userSettings = end($this->settingsChain) ?: [];
        foreach ($userSettings as $sectionName => $section) {
            if (!isset($mergedSettings[$sectionName])) {
                $mergedSettings[$sectionName] = $section;
            } else {
                // the last user settings file completely overwrites INI sections. the other files in the chain
                // can add to array options
                $mergedSettings[$sectionName] = array_merge($mergedSettings[$sectionName], $section);
            }
        }

        return $mergedSettings;
    }

    protected function getMergedDefaultSettings()
    {
        $userSettingsFile = $this->getUserSettingsFile();

        $mergedSettings = [];
        foreach ($this->settingsChain as $file => $settings) {
            if (
                $file == $userSettingsFile
                || empty($settings)
            ) {
                continue;
            }

            foreach ($settings as $sectionName => $section) {
                if (!isset($mergedSettings[$sectionName])) {
                    $mergedSettings[$sectionName] = $section;
                } else {
                    $mergedSettings[$sectionName] = $this->array_merge_recursive_distinct($mergedSettings[$sectionName], $section);
                }
            }
        }
        return $mergedSettings;
    }

    protected function getUserSettingsFile()
    {
        // the user settings file is the last key in $settingsChain
        end($this->settingsChain);
        return key($this->settingsChain);
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
    public function arrayUnmerge($original, $modified)
    {
        // return key/value pairs for keys in $modified but not in $original
        // return key/value pairs for keys in both $modified and $original, but values differ
        // ignore keys that are in $original but not in $modified

        if (empty($original) || !is_array($original)) {
            $original = [];
        }

        if (empty($modified) || !is_array($modified)) {
            $modified = [];
        }

        return array_udiff_assoc($modified, $original, [__CLASS__, 'compareElements']);
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
    private function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = $this->array_merge_recursive_distinct($merged [$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * public for use in closure.
     */
    public function findIndexOfFirstFileWithSection($sectionName)
    {
        $count = 0;
        foreach ($this->settingsChain as $file => $settings) {
            if (isset($settings[$sectionName])) {
                break;
            }

            ++$count;
        }
        return $count;
    }

    /**
     * public for use in closure.
     */
    public function getIndexOfSectionInFile($fileIndex, $sectionName)
    {
        reset($this->settingsChain);
        for ($i = 0; $i != $fileIndex; ++$i) {
            next($this->settingsChain);
        }

        $settingsData = current($this->settingsChain);
        if (empty($settingsData)) {
            return -1;
        }

        $settingsDataSectionNames = array_keys($settingsData);

        return array_search($sectionName, $settingsDataSectionNames);
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
     * Decode HTML entities
     *
     * @param mixed $values
     * @return mixed
     */
    protected function decodeValues(&$values)
    {
        if (is_array($values)) {
            foreach ($values as &$value) {
                $value = $this->decodeValues($value);
            }
            return $values;
        } elseif (is_string($values)) {
            return html_entity_decode($values, ENT_COMPAT, 'UTF-8');
        }
        return $values;
    }

    private function dumpSettings($values, $header)
    {
        /**
         * Triggered before a config is being written / saved on the local file system.
         *
         * A plugin can listen to it and modify which settings will be saved on the file system. This allows you
         * to prevent saving config values that a plugin sets on demand. Say you configure the database password in the
         * config on demand in your plugin, then you could prevent that the password is saved in the actual config file
         * by listening to this event like this:
         *
         * **Example**
         *     function doNotSaveDbPassword (&$values) {
         *         unset($values['database']['password']);
         *     }
         *
         * @param array &$values Config values that will be saved
         */
        Piwik::postEvent('Config.beforeSave', [&$values]);
        $values = $this->encodeValues($values);

        $writer = new IniWriter();
        return $writer->writeToString($values, $header);
    }

    private function replaceInvalidChars($value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayValue) {
                $key = $this->replaceInvalidChars($key);
                if (is_array($arrayValue)) {
                    $arrayValue = $this->replaceInvalidChars($arrayValue);
                }

                $result[$key] = $arrayValue;
            }
            return $result;
        } else {
            return preg_replace('/[^a-zA-Z0-9_\[\]-]/', '', $value);
        }
    }

    private function replaceSectionInvalidChars($value)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $value);
    }
}
