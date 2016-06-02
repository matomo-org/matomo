<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

use Piwik\Config;
use Piwik\Config\IniFileChain;

/**
 * Provides global settings. Global settings are organized in sections where
 * each section contains a list of name => value pairs. Setting values can
 * be primitive values or arrays of primitive values.
 *
 * Uses the config.ini.php, common.ini.php and global.ini.php files to provide global settings.
 *
 * At the moment a singleton instance of this class is used in order to get tests to pass.
 */
class GlobalSettingsProvider
{
    /**
     * @var IniFileChain
     */
    protected $iniFileChain;

    /**
     * @var string
     */
    protected $pathGlobal = null;

    /**
     * @var string
     */
    protected $pathCommon = null;

    /**
     * @var string
     */
    protected $pathLocal = null;

    /**
     * @param string|null $pathGlobal Path to the global.ini.php file. Or null to use the default.
     * @param string|null $pathLocal Path to the config.ini.php file. Or null to use the default.
     * @param string|null $pathCommon Path to the common.ini.php file. Or null to use the default.
     */
    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $this->pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
        $this->pathCommon = $pathCommon ?: Config::getCommonConfigPath();
        $this->pathLocal = $pathLocal ?: Config::getLocalConfigPath();

        $this->iniFileChain = new IniFileChain();
        $this->reload();
    }

    public function reload($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $this->pathGlobal = $pathGlobal ?: $this->pathGlobal;
        $this->pathCommon = $pathCommon ?: $this->pathCommon;
        $this->pathLocal = $pathLocal ?: $this->pathLocal;

        $this->iniFileChain->reload(array($this->pathGlobal, $this->pathCommon), $this->pathLocal);
    }

    /**
     * Returns a settings section.
     *
     * @param string $name
     * @return array
     */
    public function &getSection($name)
    {
        $section =& $this->iniFileChain->get($name);
        return $section;
    }

    /**
     * Sets a settings section.
     *
     * @param string $name
     * @param array $value
     */
    public function setSection($name, $value)
    {
        $this->iniFileChain->set($name, $value);
    }

    public function getIniFileChain()
    {
        return $this->iniFileChain;
    }

    public function getPathGlobal()
    {
        return $this->pathGlobal;
    }

    public function getPathLocal()
    {
        return $this->pathLocal;
    }

    public function getPathCommon()
    {
        return $this->pathCommon;
    }
}
