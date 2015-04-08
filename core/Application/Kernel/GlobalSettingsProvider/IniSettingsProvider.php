<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel\GlobalSettingsProvider;

use Piwik\Config;
use Piwik\Config\IniFileChain;
use Piwik\Application\Kernel\GlobalSettingsProvider;

/**
 * Default GlobalSettingsProvider implementation. This provider uses the config.ini.php,
 * common.ini.php and global.ini.php files to provide global settings.
 *
 * At the moment a singleton instance of this class is used in order to get tests to pass.
 */
class IniSettingsProvider implements GlobalSettingsProvider
{
    private static $instance = null;

    /**
     * @var IniFileChain
     */
    private $iniFileChain;

    /**
     * @param string|null $pathGlobal Path to the global.ini.php file. Or null to use the default.
     * @param string|null $pathLocal Path to the config.ini.php file. Or null to use the default.
     * @param string|null $pathCommon Path to the common.ini.php file. Or null to use the default.
     */
    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
        $pathCommon = $pathCommon ?: Config::getCommonConfigPath();
        $pathLocal = $pathLocal ?: Config::getLocalConfigPath();

        $this->iniFileChain = new IniFileChain(array($pathGlobal, $pathCommon), $pathLocal);
    }

    public function &getSection($name)
    {
        $section =& $this->iniFileChain->get($name);
        return $section;
    }

    public function setSection($name, $value)
    {
        $this->iniFileChain->set($name, $value);
    }

    public function getIniFileChain()
    {
        return $this->iniFileChain;
    }

    public static function getSingletonInstance($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        if (self::$instance === null) {
            self::$instance = new IniSettingsProvider($pathGlobal, $pathLocal, $pathCommon);
        }

        return self::$instance;
    }

    public static function unsetSingletonInstance()
    {
        self::$instance = null;
    }
}