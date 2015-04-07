<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

/**
 * Provides global settings. Global settings are organized in sections where
 * each section contains a list of name => value pairs. Setting values can
 * be primitive values or arrays of primitive values.
 *
 * By default, IniSettingsProvider is used which loads all global settings
 * from the config.ini.php, global.ini.php files and the optional
 * common.ini.php file.
 */
interface GlobalSettingsProvider
{
    /**
     * Returns a settings section.
     *
     * @param string $name
     * @return array
     */
    public function &getSection($name);

    /**
     * Sets a settings section.
     *
     * @param string $name
     * @param array $value
     */
    public function setSection($name, $value);
}